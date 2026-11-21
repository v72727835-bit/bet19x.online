<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminNotification extends Model
{
    protected $connection = 'tenant';

    protected $fillable = [
        'title', 'message', 'channel', 'audience', 'recipient_count', 'sent_by',
    ];

    public function sentBy() { return $this->belongsTo(Admin::class, 'sent_by'); }
}
