<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('title', 80);                      // what the player sees, e.g. "PhonePe / UPI"
            $table->string('upi_id', 100)->nullable();
            $table->string('payee_name', 100)->nullable();    // name shown in the UPI app
            $table->string('qr_image_path')->nullable();      // storage/app/public/payment-qr/...
            $table->string('bank_name', 100)->nullable();
            $table->string('account_holder', 100)->nullable();
            $table->string('account_number', 40)->nullable();
            $table->string('ifsc', 20)->nullable();
            $table->text('instructions')->nullable();
            $table->decimal('min_amount', 10, 2)->default(100);
            $table->decimal('max_amount', 10, 2)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('deposit_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('payment_method_id')->nullable()->after('user_id');
            $table->string('screenshot_path')->nullable()->after('reference_note');
        });
    }

    public function down(): void
    {
        Schema::table('deposit_requests', function (Blueprint $table) {
            $table->dropColumn(['payment_method_id', 'screenshot_path']);
        });
        Schema::dropIfExists('payment_methods');
    }
};
