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
        Schema::create('rewards', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->integer('stock')->default(0);
            $table->string('image')->nullable();
            $table->enum('is_active', ['Y', 'N'])->default('N');
            $table->enum('type', ['G', 'V'])->default('G'); // G = global . V = vip
            $table->string('reseller_list')->nullable(); // (jika type == vip . maka field ini diisi json array id reseller yg bisa melakukan claim reward)
            $table->date('start_date');
            $table->date('end_date');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rewards');
    }
};
