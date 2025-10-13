<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceToken extends Model
{
    protected $table = 'device_tokens';
    protected $fillable = ['user_id','token','platform','last_seen_at'];
    protected $casts = ['last_seen_at' => 'datetime'];
}
