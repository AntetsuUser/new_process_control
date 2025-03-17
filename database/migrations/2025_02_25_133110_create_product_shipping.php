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
        Schema::create('product_shipping', function (Blueprint $table) {
            $table->id();
            $table->string('product_code')->comment('品目コード');
            $table->string('product_text')->comment('品目テキスト（商品名など）');
            $table->string('purchase_order_number')->comment('購買発注番号（注文管理用）');
            $table->date('requested_delivery_date')->comment('要求納期（納品希望日）');
            $table->integer('delivered_quantity')->comment('納入数量（納品された個数）');

            // 素材品番（最大5つまで）
            $table->string('material_number_1')->nullable()->comment('素材品番1');
            $table->string('material_number_2')->nullable()->comment('素材品番2');
            $table->string('material_number_3')->nullable()->comment('素材品番3');
            $table->string('material_number_4')->nullable()->comment('素材品番4');
            $table->string('material_number_5')->nullable()->comment('素材品番5');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_shipping');
    }
};
