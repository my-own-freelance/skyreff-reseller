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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('code')->unique();
            $table->integer('purchase_price')->default(0);
            $table->integer('selling_price')->default(0);
            $table->integer('commission_regular')->default(0);
            $table->integer('commission_vip')->default(0);
            $table->enum('is_active', ['Y', 'N'])->default('N');
            $table->integer('stock')->default(0);
            $table->integer('total_sale')->default(0);
            $table->string('excerpt')->nullable();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('product_category_id');
            $table->timestamps();
            $table->softDeletes();

            // relation
            $table->foreign('product_category_id')
                ->references('id')
                ->on('product_categories')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
