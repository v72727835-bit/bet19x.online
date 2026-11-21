<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class MasterAdmin extends Authenticatable
{
    // Intentionally NOT tenant-scoped — this account manages the Tenants list itself.

    protected $fillable = ['name', 'email', 'password'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['password' => 'hashed'];
    }
}
