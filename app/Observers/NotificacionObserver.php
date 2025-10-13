<?php

namespace App\Observers;

use App\Models\Notificacion;
use App\Models\DeviceToken;
use App\Services\Fcm;

class NotificacionObserver
{
    public function created(Notificacion $n): void
    {
        if (!$n->user_id) return;
        $tokens = DeviceToken::where('user_id', $n->user_id)->pluck('token')->all();
        if (!$tokens) return;

        $title = $n->titulo ?? 'Notificación';
        $body  = $n->mensaje ?? '';
        Fcm::sendToTokens($tokens, $title, $body, ['type'=>'notificacion','id'=>$n->id]);
    }
}
