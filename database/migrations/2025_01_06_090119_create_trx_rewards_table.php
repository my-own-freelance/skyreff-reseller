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
        Schema::create('trx_rewards', function (Blueprint $table) {
            $table->id();
            $table->enum('status', ['PENDING', 'PROCESS', 'SUCCESS', 'REJECT', 'CANCEL'])->default('PENDING');
            $table->string('proof_of_acception')->nullable(); // bukti reward diberikan oleh admin
            $table->string('remark')->nullable(); // alasan admin reject
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('reward_id')->nullable(); // bisa null untuk tambah data trx jika reseller zonk dapat reward
            $table->timestamps();
            $table->softDeletes();

            // relation
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onUpdate('cascade');
            $table->foreign('reward_id')
                ->references('id')
                ->on('rewards')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trx_rewards');
    }
};
