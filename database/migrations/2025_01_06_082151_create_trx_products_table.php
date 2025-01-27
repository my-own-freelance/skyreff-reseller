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
        Schema::create('trx_products', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->integer('amount')->default(0); // harga per produk
            $table->integer('commission')->default(0);
            $table->integer('qty')->default(1);
            $table->integer('total_amount')->default(0);
            $table->integer('profit')->default(0);
            $table->enum('status', ['PENDING', 'PROCESS', 'SUCCESS', 'REJECT', 'CANCEL'])->default('PENDING');
            $table->enum('payment_type', ['TRANSFER', 'DEBT'])->default('TRANSFER');
            $table->string('proof_of_payment')->nullable(); // upload bukti pembayaran jika type nya tf
            $table->string('proof_of_return')->nullable(); // upload bukti pengembalian uang kita sudah terlanjut di tf dan admin menolak transaksi
            $table->string('remark')->nullable(); // keterangan untuk admin ketika reject trx
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('bank_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // relation
            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->onUpdate('cascade');
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
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
        Schema::dropIfExists('trx_products');
    }
};
