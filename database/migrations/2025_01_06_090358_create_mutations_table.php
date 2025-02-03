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
        Schema::create('mutations', function (Blueprint $table) {
            $table->id();
            $table->integer('amount')->default(0);
            $table->enum('type', ['C', 'W']); // C = commission . W = Widhraw
            $table->integer('first_commission')->default(0);
            $table->integer('last_commission')->default(0);
            $table->unsignedBigInteger('trx_product_id')->nullable(); // diisi ketika add commission
            $table->unsignedBigInteger('trx_commission_id')->nullable(); // diisi ketika widhraw
            $table->unsignedBigInteger('user_id');
            $table->timestamps();
            $table->softDeletes();

            // relation
            $table->foreign('trx_product_id')
                ->references('id')
                ->on('trx_products')
                ->onUpdate('cascade');
            $table->foreign('trx_commission_id')
                ->references('id')
                ->on('trx_commissions')
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
        Schema::dropIfExists('mutations');
    }
};
