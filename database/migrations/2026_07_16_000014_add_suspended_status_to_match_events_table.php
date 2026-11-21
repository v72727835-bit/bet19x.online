<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE match_events MODIFY status ENUM('upcoming','live','completed','suspended','cancelled') NOT NULL DEFAULT 'upcoming'");
        }
        // SQLite/others store enum as string/check constraint via Laravel already permissive in testing;
        // no action needed there since Schema::create used enum() which SQLite maps loosely.
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE match_events MODIFY status ENUM('upcoming','live','completed','cancelled') NOT NULL DEFAULT 'upcoming'");
        }
    }
};
