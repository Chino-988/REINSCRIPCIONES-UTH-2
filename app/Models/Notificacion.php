<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notificacion extends Model
{
    protected $table = 'notificaciones';

    protected $fillable = [
        'user_id', 'titulo', 'mensaje', 'leida',
    ];

    protected $casts = [
        'leida' => 'boolean',
    ];

    public function user() { return $this->belongsTo(User::class); }

    // === Helpers ===
    public static function toUser(int $userId, string $titulo, string $mensaje): self
    {
        return static::create([
            'user_id' => $userId,
            'titulo'  => $titulo,
            'mensaje' => $mensaje,
            'leida'   => false,
        ]);
    }

    public static function toRole(string $role, string $titulo, string $mensaje): int
    {
        $users = User::where('role', $role)->pluck('id');
        $count = 0;
        foreach ($users as $uid) {
            static::create([
                'user_id' => $uid,
                'titulo'  => $titulo,
                'mensaje' => $mensaje,
                'leida'   => false,
            ]);
            $count++;
        }
        return $count;
    }
}
