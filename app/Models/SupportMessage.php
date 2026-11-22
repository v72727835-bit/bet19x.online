<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportMessage extends Model
{
    protected $connection = 'tenant';

    protected $fillable = ['conversation_id', 'sender', 'admin_id', 'body'];

    public function conversation() { return $this->belongsTo(SupportConversation::class, 'conversation_id'); }
    public function admin() { return $this->belongsTo(Admin::class); }

    public function toChat(): array
    {
        return [
            'id' => $this->id,
            'from' => $this->sender,
            'body' => $this->body,
            'time' => $this->created_at?->format('d M, h:i A'),
        ];
    }
}
