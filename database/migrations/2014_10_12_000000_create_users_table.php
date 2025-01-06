<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('username')->unique();
            $table->string('password');
            $table->string('phone_number')->nullable();
            $table->enum('is_active', ['Y', 'N'])->default('N');
            $table->enum('role', ['ADMIN', 'RESELLER'])->default('RESELLER');
            $table->string('bank_type')->nullable();
            $table->string('bank_account')->nullable();
            $table->enum('level', ['REGULAR', 'VIP'])->nullable();
            $table->integer('debt_limit')->default(0);
            $table->integer('total_debt')->default(0);
            $table->integer('comission')->default(0);
            $table->string('image')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
