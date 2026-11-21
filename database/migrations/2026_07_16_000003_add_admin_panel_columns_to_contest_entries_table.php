<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contest_entries', function (Blueprint $table) {
            $table->enum('status', ['pending', 'won', 'lost', 'void', 'cancelled'])->default('pending')->after('user_id');
            $table->decimal('entry_fee_paid', 10, 2)->default(0)->after('status');
            $table->text('admin_remarks')->nullable()->after('prize_won');
        });
    }

    public function down(): void
    {
        Schema::table('contest_entries', function (Blueprint $table) {
            $table->dropColumn(['status', 'entry_fee_paid', 'admin_remarks']);
        });
    }
};
