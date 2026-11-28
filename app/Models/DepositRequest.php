<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DepositRequest extends Model
{
    protected $connection = 'tenant';

    protected $fillable = [
        'user_id', 'payment_method_id', 'amount', 'method', 'reference_note', 'screenshot_path',
        'status', 'admin_remarks', 'processed_by', 'processed_at',
    ];

    protected $casts = ['processed_at' => 'datetime'];

    public function user() { return $this->belongsTo(User::class); }
    public function paymentMethod() { return $this->belongsTo(PaymentMethod::class); }

    public function screenshotUrl(): ?string
    {
        return $this->screenshot_path ? asset('storage/' . $this->screenshot_path) : null;
    }
    public function processedBy() { return $this->belongsTo(Admin::class, 'processed_by'); }
}
