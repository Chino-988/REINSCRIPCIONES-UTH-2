<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    protected $table = 'pagos';
    protected $fillable = [
        'estudiante_id','referencia','comprobante_path',
        'estatus_caja','estatus_admin','observaciones_caja','observaciones_admin'
    ];

    public function estudiante() { return $this->belongsTo(Estudiante::class, 'estudiante_id'); }
}
