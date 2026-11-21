<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('client_phone')->nullable()->after('brand_name');
            $table->string('client_email')->nullable()->after('client_phone');
            $table->enum('sale_type', ['one_time', 'subscription'])->default('one_time')->after('client_email');
            $table->decimal('price', 12, 2)->nullable()->after('sale_type');
            $table->enum('billing_cycle', ['monthly', 'quarterly', 'half_yearly', 'yearly'])->nullable()->after('price');
            $table->date('next_billing_date')->nullable()->after('billing_cycle');
            $table->text('client_notes')->nullable()->after('next_billing_date');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn([
                'client_phone', 'client_email', 'sale_type', 'price',
                'billing_cycle', 'next_billing_date', 'client_notes',
            ]);
        });
    }
};
