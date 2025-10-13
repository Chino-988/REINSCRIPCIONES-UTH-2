<?php

namespace App\Services;

use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Support\Facades\Http;
use App\Models\DeviceToken;

class FcmService
{
    protected string $projectId;
    protected ?string $credentialsFile;
    protected ?string $credentialsJson;

    public function __construct()
    {
        $this->projectId       = (string) config('services.firebase.project_id', '');
        $this->credentialsFile = config('services.firebase.credentials_file');
        $this->credentialsJson = config('services.firebase.credentials_json');

        if (!$this->projectId) {
            throw new \RuntimeException('FIREBASE_PROJECT_ID no configurado.');
        }
    }

    protected function accessToken(): string
    {
        $scopes = ['https://www.googleapis.com/auth/firebase.messaging'];

        if ($this->credentialsJson) {
            $creds = new ServiceAccountCredentials($scopes, json_decode($this->credentialsJson, true));
        } elseif ($this->credentialsFile && file_exists($this->credentialsFile)) {
            $creds = new ServiceAccountCredentials($scopes, json_decode(file_get_contents($this->credentialsFile), true));
        } else {
            throw new \RuntimeException('Credenciales de Firebase no configuradas. Define FIREBASE_CREDENTIALS o FIREBASE_CREDENTIALS_JSON.');
        }

        $token = $creds->fetchAuthToken();
        return $token['access_token'] ?? '';
    }

    public function sendToToken(string $token, string $title, string $body, array $data = []): bool
    {
        $endpoint = "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send";
        $payload = [
            'message' => [
                'token' => $token,
                'notification' => ['title'=>$title, 'body'=>$body],
                'data' => $data,
                'android' => ['priority'=>'high'],
            ],
        ];

        $res = Http::withToken($this->accessToken())
            ->acceptJson()
            ->post($endpoint, $payload);

        return $res->successful();
    }

    public function sendToUser(int $userId, string $title, string $body, array $data = []): bool
    {
        $tokens = DeviceToken::where('user_id',$userId)->pluck('token')->all();
        $ok = true;
        foreach ($tokens as $t) {
            $ok = $this->sendToToken($t, $title, $body, $data) && $ok;
        }
        return $ok;
    }
}
