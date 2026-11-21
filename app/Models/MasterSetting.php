<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterSetting extends Model
{
    // Intentionally NOT tenant-scoped — this is the reseller's OWN panel branding,
    // lives in the landlord/master database, shared across all tenants they manage.

    protected $fillable = ['company_name', 'logo_path', 'primary_color'];
}
