<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

/** A way for players to pay the site: UPI ID, QR image and/or bank account, set up by the admin. */
class PaymentMethod extends Model
{
    protected $connection = 'tenant';

    protected $fillable = [
        'title', 'upi_id', 'payee_name', 'qr_image_path', 'bank_name', 'account_holder', 'account_number', 'ifsc',
        'instructions', 'min_amount', 'max_amount', 'sort_order', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    /** True once the table exists (before `php artisan migrate` the pages must not crash). */
    public static function ready(): bool
    {
        try {
            return Schema::connection((new static)->getConnectionName())->hasTable('payment_methods');
        } catch (\Throwable $e) {
            return false;
        }
    }

    /** Active methods for the deposit page, in the admin's order. Empty before the migration. */
    public static function available()
    {
        if (! static::ready()) {
            return collect();
        }

        return static::where('is_active', true)->orderBy('sort_order')->orderBy('id')->get();
    }

    public function qrUrl(): ?string
    {
        return $this->qr_image_path ? asset('storage/' . $this->qr_image_path) : null;
    }

    public function hasBank(): bool
    {
        return (bool) ($this->account_number || $this->bank_name || $this->ifsc);
    }

    /** upi://pay deep link: opens GPay / PhonePe / Paytm with the payee and amount filled in. */
    public function upiLink(?float $amount = null): ?string
    {
        if (! $this->upi_id) {
            return null;
        }
        $q = ['pa' => $this->upi_id, 'pn' => $this->payee_name ?: $this->title, 'cu' => 'INR'];
        if ($amount) {
            $q['am'] = number_format($amount, 2, '.', '');
        }

        return 'upi://pay?' . http_build_query($q, '', '&', PHP_QUERY_RFC3986);
    }

    /** Short line for lists: what this method offers. */
    public function summary(): string
    {
        return collect([
            $this->upi_id ? 'UPI' : null,
            $this->qr_image_path ? 'QR' : null,
            $this->hasBank() ? 'Bank' : null,
        ])->filter()->implode(' + ');
    }
}
