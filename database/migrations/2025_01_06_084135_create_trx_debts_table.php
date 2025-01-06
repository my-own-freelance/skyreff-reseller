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
        Schema::create('trx_debts', function (Blueprint $table) {
            $table->id();
            $table->integer('amount')->nullable();
            $table->enum('type', ['D', 'P'])->default('D'); // D = debt . P = pay
            $table->enum('status', ['PENDING', 'SUCCESS', 'REJECT'])->default('PENDING');
            $table->string('proof_of_payment')->nullable(); // upload bukti pembayaran piutang
            $table->string('remark')->nullable(); // keterangan untuk admin ketika reject trx
            $table->unsignedBigInteger('trx_product_id');
            $table->unsignedBigInteger('bank_id'); // diisi ketika bayar hutang via tf
            $table->timestamps();
            $table->softDeletes();

            // relation
            $table->foreign('trx_product_id')
                ->references('id')
                ->on('trx_products')
                ->onUpdate('cascade');
            $table->foreign('bank_id')
                ->references('id')
                ->on('banks')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trx_debts');
    }
};
