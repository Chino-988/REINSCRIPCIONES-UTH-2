<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class Fcm
{
    public static function sendToTokens(array $tokens, string $title, string $body, array $data = []): array
    {
        $key = config('services.fcm.server_key');
        if (!$key) return ['ok'=>false,'error'=>'FCM_SERVER_KEY_MISSING'];

        $payload = [
            'registration_ids' => $tokens,
            'notification' => ['title'=>$title, 'body'=>$body],
            'data' => $data,
            'android' => ['priority' => 'high'],
        ];

        $res = Http::withHeaders([
            'Authorization' => 'key '.$key,
            'Content-Type'  => 'application/json',
        ])->post('https://fcm.googleapis.com/fcm/send', $payload);

        return ['ok'=>$res->successful(), 'status'=>$res->status(), 'body'=>$res->json()];
    }
}
