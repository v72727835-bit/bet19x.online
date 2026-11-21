<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlockedIp extends Model
{
    protected $connection = 'tenant';

    protected $fillable = ['ip_address', 'reason', 'blocked_by'];

    public function blockedBy() { return $this->belongsTo(Admin::class, 'blocked_by'); }
}
