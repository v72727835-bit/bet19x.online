<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DepositRequest extends Model
{
    protected $connection = 'tenant';

    protected $fillable = [
        'user_id', 'amount', 'method', 'reference_note',
        'status', 'admin_remarks', 'processed_by', 'processed_at',
    ];

    protected $casts = ['processed_at' => 'datetime'];

    public function user() { return $this->belongsTo(User::class); }
    public function processedBy() { return $this->belongsTo(Admin::class, 'processed_by'); }
}
