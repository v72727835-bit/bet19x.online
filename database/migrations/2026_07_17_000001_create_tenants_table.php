<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('domain')->unique(); // e.g. clientbrand.com (no scheme, no trailing slash)
            $table->string('brand_name');
            $table->string('db_host')->default('127.0.0.1');
            $table->string('db_name');
            $table->string('db_username');
            $table->text('db_password')->nullable();
            $table->string('db_port')->default('3306');
            $table->enum('status', ['provisioning', 'active', 'suspended', 'failed'])->default('provisioning');
            $table->text('provision_log')->nullable();
            $table->date('license_expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
