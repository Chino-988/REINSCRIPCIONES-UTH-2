<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use App\Models\Reinscripcion;

class ConstanciaController extends Controller
{
    public function descargar()
    {
        $estu = auth()->user()->estudiante ?? null;
        abort_unless($estu, 403);
        $rein = Reinscripcion::where('estudiante_id', $estu->id)->latest()->firstOrFail();
        abort_unless($rein->estatus_final === 'APROBADA' && $rein->constancia_pdf_path, 403);

        return Storage::disk('public')->download($rein->constancia_pdf_path, 'Constancia_Reinscripcion.pdf');
    }
}
