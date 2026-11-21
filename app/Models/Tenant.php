<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    // Intentionally NOT tenant-scoped — this table lives in the landlord/master database.

    protected $fillable = [
        'domain', 'brand_name', 'client_phone', 'client_email', 'sale_type', 'price',
        'billing_cycle', 'next_billing_date', 'client_notes',
        'db_host', 'db_name', 'db_username', 'db_password',
        'db_port', 'status', 'provision_log', 'license_expires_at',
    ];

    protected $hidden = ['db_password'];

    protected $casts = [
        'license_expires_at' => 'date',
        'next_billing_date' => 'date',
        'price' => 'decimal:2',
    ];

    /**
     * Store the tenant DB password encrypted. Reads fall back to the raw value so
     * rows saved before encryption was introduced keep working.
     */
    protected function dbPassword(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: function ($value) {
                if ($value === null || $value === '') {
                    return $value;
                }
                try {
                    return \Illuminate\Support\Facades\Crypt::decryptString($value);
                } catch (\Throwable $e) {
                    return $value;
                }
            },
            set: fn ($value) => ($value === null || $value === '') ? $value : \Illuminate\Support\Facades\Crypt::encryptString($value),
        );
    }

    public function isLicenseValid(): bool
    {
        return ! $this->license_expires_at || ! $this->license_expires_at->isPast();
    }

    public function isBillingOverdue(): bool
    {
        return $this->sale_type === 'subscription' && $this->next_billing_date && $this->next_billing_date->isPast();
    }
}
