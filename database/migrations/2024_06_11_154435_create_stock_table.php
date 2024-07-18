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
        Schema::create('stock', function (Blueprint $table) {
            $table->id();
            $table->string('processing_item')->nullable()->comment('加工品番');
            $table->integer('material_stock_1')->nullable()->comment('材料1在庫');
            $table->integer('material_stock_2')->nullable()->comment('材料2在庫');
            $table->integer('process1_stock')->nullable()->comment('工程在庫1');
            $table->integer('process2_stock')->nullable()->comment('工程在庫2');
            $table->integer('process3_stock')->nullable()->comment('工程在庫3');
            $table->integer('process4_stock')->nullable()->comment('工程在庫4');
            $table->integer('process5_stock')->nullable()->comment('工程在庫5');
            $table->integer('process6_stock')->nullable()->comment('工程在庫6');
            $table->integer('process7_stock')->nullable()->comment('工程在庫7');
            $table->integer('process8_stock')->nullable()->comment('工程在庫8');
            $table->integer('process9_stock')->nullable()->comment('工程在庫9');
            $table->integer('process10_stock')->nullable()->comment('工程在庫10');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock');
    }
};
