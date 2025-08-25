<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reinscripcion extends Model
{
    protected $table = 'reinscripciones';

    protected $fillable = [
        'estudiante_id','pago_id','estatus_final',
        'constancia_pdf_path','constancia_qr_path',
        'token_verificacion'
    ];

    public function estudiante(): BelongsTo { return $this->belongsTo(Estudiante::class); }
    public function pago(): BelongsTo { return $this->belongsTo(Pago::class); }
}
