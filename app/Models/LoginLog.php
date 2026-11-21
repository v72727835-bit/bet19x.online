<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoginLog extends Model
{
    protected $connection = 'tenant';

    protected $fillable = ['user_id', 'admin_id', 'ip_address', 'device', 'user_agent', 'login_at'];

    protected $casts = ['login_at' => 'datetime'];

    public function user() { return $this->belongsTo(User::class); }
    public function admin() { return $this->belongsTo(Admin::class); }
}
