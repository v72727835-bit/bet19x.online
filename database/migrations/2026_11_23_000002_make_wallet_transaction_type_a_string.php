<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * wallet_transactions.type was an ENUM of six values, so strict MySQL/MariaDB
 * rejected 'transfer_in', 'transfer_out', 'credit' and 'debit' (transfers and
 * admin manual adjustments failed with a 500). Make it a plain string.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->string('type', 30)->change();
        });
    }

    public function down(): void
    {
        // Leave as string: shrinking back to the old ENUM would fail on rows
        // that already use the newer types.
    }
};
