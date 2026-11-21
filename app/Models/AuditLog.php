<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $connection = 'tenant';

    protected $fillable = ['admin_id', 'module', 'action', 'description', 'ip_address'];

    public function admin() { return $this->belongsTo(Admin::class); }

    public static function record(string $module, string $action, ?string $description = null): void
    {
        static::create([
            'admin_id' => auth('admin')->id(),
            'module' => $module,
            'action' => $action,
            'description' => $description,
            'ip_address' => request()->ip(),
        ]);
    }
}
