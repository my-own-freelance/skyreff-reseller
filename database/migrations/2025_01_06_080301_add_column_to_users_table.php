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
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('province_id')->nullable();
            $table->unsignedBigInteger('district_id')->nullable();
            $table->unsignedBigInteger('sub_district_id')->nullable();
            $table->string('address')->nullable();

            $table->foreign('province_id')
                ->references('id')
                ->on('provinces')
                ->onUpdate('cascade');
            $table->foreign('district_id')
                ->references('id')
                ->on('districts')
                ->onUpdate('cascade');
            $table->foreign('sub_district_id')
                ->references('id')
                ->on('sub_districts')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['province_id']);
            $table->dropForeign(['district_id']);
            $table->dropForeign(['sub_district_id']);
            $table->dropColumn(['province_id', 'district_id', 'sub_district_id', 'address']);
        });
    }
};
