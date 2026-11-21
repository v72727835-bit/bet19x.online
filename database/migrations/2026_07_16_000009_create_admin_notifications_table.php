<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('message');
            $table->enum('channel', ['sms', 'email', 'push'])->default('push');
            $table->enum('audience', ['all', 'active', 'blocked', 'kyc_pending'])->default('all');
            $table->unsignedInteger('recipient_count')->default(0);
            $table->foreignId('sent_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_notifications');
    }
};
