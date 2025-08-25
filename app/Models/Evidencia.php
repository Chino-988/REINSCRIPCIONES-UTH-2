<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Evidencia extends Model
{
    protected $table = 'evidencias';

    protected $fillable = [
        'pago_id','path','mime','meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function pago() { return $this->belongsTo(Pago::class); }
}
