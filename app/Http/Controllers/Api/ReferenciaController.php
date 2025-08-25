<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\{Pago, Notificacion, ReferenciaUpdate, Evidencia};

class ReferenciaController extends Controller
{
    /**
     * GET /api/v1/referencias/pendientes?limit=50
     * Devuelve pagos con estatus en revisión (PENDIENTE o NULL).
     */
    public function pendientes(Request $request)
    {
        try {
            $limit = (int) $request->query('limit', 50);
            $limit = max(1, min(500, $limit));

            $pagos = Pago::select('id','estudiante_id','referencia','estatus_caja','updated_at','created_at')
                ->where(function($q){
                    $q->whereNull('estatus_caja')
                      ->orWhere('estatus_caja', 'PENDIENTE');
                })
                ->orderBy('updated_at')
                ->limit($limit)
                ->get();

            $items = $pagos->map(fn($p)=>[
                'pago_id'     => $p->id,
                'referencia'  => $p->referencia,
                'estatus'     => $p->estatus_caja ?? 'PENDIENTE',
                'created_at'  => optional($p->created_at)->toIso8601String(),
                'updated_at'  => optional($p->updated_at)->toIso8601String(),
            ]);

            return response()->json(['ok'=>true,'count'=>$items->count(),'items'=>$items]);
        } catch (\Throwable $e) {
            Log::error('API pendientes error: '.$e->getMessage(), ['trace'=>$e->getTraceAsString()]);
            return response()->json([
                'ok'=>false,
                'error'=>'INTERNAL_ERROR',
                'message'=>$e->getMessage(),
            ], 500);
        }
    }

    /**
     * POST /api/v1/referencias/consulta
     * Body: { "referencias": ["123...", "..."] }
     */
    public function consulta(Request $request)
    {
        $data = $request->validate([
            'referencias'   => 'required|array|min:1',
            'referencias.*' => 'string|min:8|max:60',
        ]);

        try {
            $refs  = $data['referencias'];
            $pagos = Pago::with('estudiante')->whereIn('referencia', $refs)->get();

            $map = [];
            foreach ($refs as $r) {
                $p = $pagos->firstWhere('referencia', $r);
                if ($p) {
                    $map[$r] = [
                        'status'        => $p->estatus_caja ?? 'PENDIENTE',
                        'estatus_admin' => $p->estatus_admin ?? 'PENDIENTE',
                        'estudiante'    => [
                            'matricula' => optional($p->estudiante)->matricula,
                            'nombre'    => trim((optional($p->estudiante)->nombre.' '.optional($p->estudiante)->apellido_paterno.' '.optional($p->estudiante)->apellido_materno)),
                        ],
                        'received_at'   => optional($p->created_at)->toIso8601String(),
                    ];
                } else {
                    $map[$r] = ['status' => 'DESCONOCIDA'];
                }
            }

            return response()->json(['ok'=>true,'result'=>$map]);
        } catch (\Throwable $e) {
            Log::error('API consulta error: '.$e->getMessage());
            return response()->json(['ok'=>false,'error'=>$e->getMessage()], 500);
        }
    }

    /**
     * POST /api/v1/referencias/sincronizar
     * Body: { items: [{referencia,status,observacion?,monto?,fecha_pago?}, ...] }
     * Nota: EN_PROCESO se normaliza a PENDIENTE para no romper CHECKs de BD.
     */
    public function sincronizar(Request $request)
    {
        $payload = $request->validate([
            'items'               => 'required|array|min:1',
            'items.*.referencia'  => 'required|string|min:8|max:60',
            'items.*.status'      => 'required|string|in:VALIDADO,PENDIENTE,RECHAZADO,EN_PROCESO',
            'items.*.monto'       => 'nullable|numeric',
            'items.*.fecha_pago'  => 'nullable|date',
            'items.*.observacion' => 'nullable|string|max:300',
        ]);

        $source = strtoupper($request->header('X-SOURCE', 'API'));

        $resumen = [
            'procesados'=>0,'validados'=>0,'rechazados'=>0,'pendientes'=>0,
            'no_encontrados'=>0,'sin_cambio'=>0
        ];
        $det = [];

        try {
            DB::beginTransaction();

            foreach ($payload['items'] as $it) {
                $ref       = $it['referencia'];
                $statusIn  = strtoupper($it['status']);
                $status    = $this->normalizeStatus($statusIn); // EN_PROCESO -> PENDIENTE

                $pago = Pago::with('estudiante')->where('referencia',$ref)->latest()->first();
                if (!$pago) {
                    $resumen['no_encontrados']++;
                    $det[] = ['referencia'=>$ref,'resultado'=>'NO_ENCONTRADO'];
                    continue;
                }

                $antes = $pago->estatus_caja ?? 'PENDIENTE';

                if ($antes === $status && empty($it['observacion'])) {
                    $resumen['sin_cambio']++;
                    $det[] = ['referencia'=>$ref,'resultado'=>'SIN_CAMBIO','status'=>$status];
                    continue;
                }

                // Actualización
                $pago->estatus_caja = $status;
                if (!empty($it['observacion'])) {
                    $pago->observaciones_caja = $it['observacion'];
                }
                $pago->save();

                // Auditoría
                ReferenciaUpdate::create([
                    'pago_id'       => $pago->id,
                    'referencia'    => $ref,
                    'status_before' => $antes,
                    'status_after'  => $status,
                    'source'        => $source,
                    'actor'         => null,
                    'ip'            => $request->ip(),
                    'meta'          => [
                        'observacion' => $it['observacion'] ?? null,
                        'monto'       => $it['monto'] ?? null,
                        'fecha_pago'  => $it['fecha_pago'] ?? null,
                        'user_agent'  => $request->userAgent(),
                    ],
                ]);

                // Notificaciones (no interrumpen el flujo si fallan)
                try {
                    if ($pago->estudiante && $pago->estudiante->user_id) {
                        if ($status === 'VALIDADO') {
                            Notificacion::toUser(
                                $pago->estudiante->user_id,
                                'Pago validado por Caja',
                                'Tu pago fue validado automáticamente.'
                            );
                        } elseif ($status === 'RECHAZADO') {
                            Notificacion::toUser(
                                $pago->estudiante->user_id,
                                'Pago rechazado por Caja',
                                $pago->observaciones_caja ?? 'Rechazado.'
                            );
                        }
                    }
                } catch (\Throwable $eNoti) {
                    Log::warning('No se pudo enviar notificación: '.$eNoti->getMessage());
                }

                // Conteos
                $resumen['procesados']++;
                if     ($status === 'VALIDADO')  $resumen['validados']++;
                elseif ($status === 'RECHAZADO') $resumen['rechazados']++;
                else                              $resumen['pendientes']++;

                $det[] = ['referencia'=>$ref,'resultado'=>$status,'antes'=>$antes];
            }

            DB::commit();

            return response()->json(['ok'=>true,'resumen'=>$resumen,'detalles'=>$det]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('API sincronizar error: '.$e->getMessage(), ['trace'=>$e->getTraceAsString()]);
            return response()->json([
                'ok'=>false,
                'error'=>'INTERNAL_ERROR',
                'message'=>$e->getMessage(),
            ], 500);
        }
    }

    /**
     * POST /api/v1/referencias/evidencia
     * archivo: jpg/png/pdf (<=4MB)
     */
    public function evidencia(Request $request)
    {
        $data = $request->validate([
            'referencia' => 'nullable|string|min:8|max:60',
            'pago_id'    => 'nullable|integer|exists:pagos,id',
            'archivo'    => 'required|file|mimes:jpg,jpeg,png,pdf|max:4096',
            'note'       => 'nullable|string|max:200',
        ]);

        try {
            // Encontrar pago
            $pago = null;
            if (!empty($data['pago_id'])) {
                $pago = Pago::find($data['pago_id']);
            } elseif (!empty($data['referencia'])) {
                $pago = Pago::where('referencia', $data['referencia'])->latest()->first();
            }
            if (!$pago) {
                return response()->json(['ok'=>false,'error'=>'Pago no encontrado'], 404);
            }

            Storage::disk('public')->makeDirectory('evidencias');
            $path = $request->file('archivo')->store('evidencias', 'public');

            Evidencia::create([
                'pago_id' => $pago->id,
                'path'    => $path,
                'mime'    => $request->file('archivo')->getMimeType(),
                'meta'    => [
                    'referencia' => $pago->referencia,
                    'note'       => $data['note'] ?? null,
                    'source'     => $request->header('X-SOURCE', 'API'),
                    'ip'         => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ],
            ]);

            return response()->json(['ok'=>true,'message'=>'Evidencia almacenada','path'=>$path]);
        } catch (\Throwable $e) {
            Log::error('API evidencia error: '.$e->getMessage());
            return response()->json(['ok'=>false,'error'=>$e->getMessage()], 500);
        }
    }

    /**
     * POST /api/v1/referencias/cargar-csv
     * CSV con columnas: referencia,status,fecha,monto,detalle
     */
    public function cargarCsv(Request $request)
    {
        $request->validate([
            'archivo' => 'required|file|mimes:csv,txt|max:10240',
        ]);

        try {
            Storage::disk('local')->makeDirectory('analisis/api');
            $path = $request->file('archivo')->store('analisis/api', 'local');
            $abs  = Storage::disk('local')->path($path);

            $h = fopen($abs, 'r');
            if (!$h) return response()->json(['ok'=>false,'error'=>'No se pudo leer el CSV'], 422);

            $first = fgets($h); rewind($h);
            $delim = (substr_count($first,';')>substr_count($first,',')) ? ';' : ',';

            $headers=null; $line=0; $items=[];
            while(($row = fgetcsv($h, 0, $delim)) !== false){
                if ($line === 0) { $headers = array_map('strtolower', $row); $line++; continue; }
                $data = @array_combine($headers, $row);
                if (!$data) continue;
                $items[] = [
                    'referencia'  => $data['referencia'] ?? $data['ref'] ?? null,
                    'status'      => strtoupper($data['status'] ?? $data['estatus'] ?? 'PENDIENTE'),
                    'monto'       => isset($data['monto']) ? (float)$data['monto'] : null,
                    'fecha_pago'  => $data['fecha'] ?? null,
                    'observacion' => $data['detalle'] ?? null,
                ];
            }
            fclose($h);

            $items = array_values(array_filter($items, fn($i)=>!empty($i['referencia'])));
            // normaliza EN_PROCESO -> PENDIENTE
            foreach ($items as &$i) { $i['status'] = $this->normalizeStatus($i['status']); }

            // reutiliza sincronizar()
            $request->merge(['items'=>$items]);
            $request->headers->set('X-SOURCE', 'CSV');
            return $this->sincronizar($request);
        } catch (\Throwable $e) {
            Log::error('API cargarCsv error: '.$e->getMessage());
            return response()->json(['ok'=>false,'error'=>$e->getMessage()], 500);
        }
    }

    /** EN_PROCESO -> PENDIENTE, el resto se respeta */
    private function normalizeStatus(string $status): string
    {
        $s = strtoupper($status);
        return $s === 'EN_PROCESO' ? 'PENDIENTE' : $s;
    }
}
