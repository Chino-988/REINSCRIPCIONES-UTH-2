<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class MobileAuthController extends Controller
{
    public function login(Request $request)
    {
        $data = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string|min:3',
        ]);

        $user = User::where('email', $data['email'])->first();
        if (!$user || !Hash::check($data['password'], $user->password)) {
            return response()->json(['ok'=>false,'error'=>'INVALID_CREDENTIALS'], 401);
        }
        if (!in_array($user->role, ['ESTUDIANTE'])) {
            return response()->json(['ok'=>false,'error'=>'UNAUTHORIZED_ROLE'], 403);
        }

        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'ok'    => true,
            'token' => $token,
            'user'  => ['id'=>$user->id,'email'=>$user->email,'role'=>$user->role],
        ]);
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        if ($user && $user->currentAccessToken()) {
            $user->currentAccessToken()->delete();
        }
        return response()->json(['ok'=>true]);
    }
}
