<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->boolean('is_blocked')->default(false)->after('phone');
            $table->string('blocked_reason')->nullable()->after('is_blocked');
            $table->timestamp('blocked_at')->nullable()->after('blocked_reason');
            $table->enum('kyc_status', ['not_submitted', 'pending', 'approved', 'rejected'])->default('not_submitted')->after('blocked_at');
            $table->string('kyc_id_proof_path')->nullable()->after('kyc_status');
            $table->string('kyc_address_proof_path')->nullable()->after('kyc_id_proof_path');
            $table->string('kyc_remarks')->nullable()->after('kyc_address_proof_path');
            $table->decimal('daily_deposit_limit', 10, 2)->default(50000)->after('kyc_remarks');
            $table->decimal('max_contest_entry_limit', 10, 2)->default(20000)->after('daily_deposit_limit');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone', 'is_blocked', 'blocked_reason', 'blocked_at',
                'kyc_status', 'kyc_id_proof_path', 'kyc_address_proof_path', 'kyc_remarks',
                'daily_deposit_limit', 'max_contest_entry_limit',
            ]);
        });
    }
};
