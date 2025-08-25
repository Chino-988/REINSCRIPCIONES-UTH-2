<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Pago;

class ReferenciasPushDemo extends Command
{
    protected $signature = 'referencias:push-demo
                            {--n=3 : Cuántas referencias enviar}
                            {--status=VALIDADO : VALIDADO|RECHAZADO|EN_PROCESO|PENDIENTE}
                            {--obs=Confirmado por demo : Observación a guardar}';

    protected $description = 'Simula un sistema externo que empuja estatus a /api/v1/referencias/sincronizar';

    public function handle()
    {
        $n      = (int) $this->option('n');
        $status = strtoupper((string)$this->option('status'));
        $obs    = (string) $this->option('obs');

        if (!in_array($status, ['VALIDADO','RECHAZADO','EN_PROCESO','PENDIENTE'], true)) {
            $this->error('Status inválido. Usa: VALIDADO | RECHAZADO | EN_PROCESO | PENDIENTE');
            return self::FAILURE;
        }

        // Tomamos pagos elegibles (pendientes / en proceso / nulos)
        $pagos = Pago::whereIn('estatus_caja', [null, 'PENDIENTE', 'EN_PROCESO'])
            ->orderBy('id', 'desc')
            ->limit($n)
            ->get();

        if ($pagos->isEmpty()) {
            $this->warn('No hay pagos elegibles para enviar.');
            return self::SUCCESS;
        }

        $items = $pagos->map(fn($p) => [
            'referencia'  => $p->referencia,
            'status'      => $status,
            'observacion' => $obs,
        ])->values()->all();

        // Base URL (usa APP_URL del .env; fallback a 127.0.0.1:8000)
        $base = rtrim(config('app.url') ?: env('APP_URL', 'http://127.0.0.1:8000'), '/');
        $url  = $base . '/api/v1/referencias/sincronizar';

        $apiKey = env('API_KEY_CAJA', '');
        if ($apiKey === '') {
            $this->error('API_KEY_CAJA no está definido en .env');
            return self::FAILURE;
        }

        $this->info("POST {$url}");
        $this->line('Payload: ' . json_encode(['items' => $items], JSON_UNESCAPED_UNICODE));

        $res = Http::withHeaders([
            'X-API-KEY'    => $apiKey,
            'Accept'       => 'application/json',
            'Content-Type' => 'application/json',
        ])->post($url, ['items' => $items]);

        if ($res->successful()) {
            $this->info('OK ' . $res->status() . ': ' . $res->body());
            return self::SUCCESS;
        }

        $this->error('ERROR ' . $res->status() . ': ' . $res->body());
        return self::FAILURE;
    }
}
