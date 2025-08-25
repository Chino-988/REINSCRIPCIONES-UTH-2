<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReferenciaUpdate extends Model
{
    protected $table = 'referencia_updates';

    protected $fillable = [
        'pago_id','referencia','status_before','status_after',
        'source','actor','ip','meta'
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function pago()
    {
        return $this->belongsTo(Pago::class);
    }
}
