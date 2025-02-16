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
        Schema::create('trx_topups', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->integer('amount')->default(0);
            $table->enum('status', ['PENDING', 'PROCESS', 'SUCCESS', 'REJECT', 'CANCEL'])->default('PENDING');
            $table->string('proof_of_payment')->nullable();
            $table->string('proof_of_return')->nullable();
            $table->string('remark')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('bank_id')->nullable();
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
        Schema::dropIfExists('trx_topups');
    }
};
