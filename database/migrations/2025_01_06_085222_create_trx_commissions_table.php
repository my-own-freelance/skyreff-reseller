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
        Schema::create('trx_commissions', function (Blueprint $table) {
            $table->id();
            $table->integer('amount')->default(0);
            $table->string('bank_name');
            $table->string('bank_account');
            $table->enum('status', ['PENDING', 'PROCESS', 'SUCCESS', 'REJECT'])->default('PENDING');
            $table->string('proof_of_payment')->nullable(); // bukti bayar oleh admin
            $table->string('remark')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->timestamps();
            $table->softDeletes();

            // relation
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
        Schema::dropIfExists('trx_commissions');
    }
};
