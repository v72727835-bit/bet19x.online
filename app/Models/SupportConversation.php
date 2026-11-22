<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportConversation extends Model
{
    protected $connection = 'tenant';

    protected $fillable = ['user_id', 'status', 'user_unread', 'admin_unread', 'last_message_at'];

    protected $casts = ['last_message_at' => 'datetime'];

    public function user() { return $this->belongsTo(User::class); }
    public function messages() { return $this->hasMany(SupportMessage::class, 'conversation_id'); }
}
