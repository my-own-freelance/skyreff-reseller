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
        Schema::create('trx_compensation', function (Blueprint $table) {
            $table->id();
            $table->enum('status', ['PENDING', 'PROCESS', 'SUCCESS', 'REJECT', 'CANCEL'])->default('PENDING');
            $table->string('description'); // keterangan kendala / keluhan
            $table->string('proof_of_constrain')->nullable(); // upload bukti kendala / keluhan
            $table->string('proof_of_solution')->nullable(); // upload bukti solusi dari admin
            $table->string('remark'); // catatan dari admin ketika reject
            $table->unsignedBigInteger('trx_product_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();
            $table->softDeletes();

            // relation
            $table->foreign('trx_product_id')
                ->references('id')
                ->on('trx_products')
                ->onUpdate('cascade');
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trx_compensation');
    }
};
