<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DeviceToken;

class DeviceTokenController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'token'    => 'required|string|max:255',
            'platform' => 'nullable|string|max:20', // android|ios|web
        ]);

        $userId = $request->user()->id;

        $dt = DeviceToken::firstOrCreate(
            ['user_id' => $userId, 'token' => $data['token']],
            ['platform' => $data['platform'] ?? null]
        );
        $dt->touch();

        return response()->json(['ok' => true]);
    }
}
