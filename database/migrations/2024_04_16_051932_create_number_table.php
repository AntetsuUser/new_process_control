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
        Schema::create('number', function (Blueprint $table) {
            $table->id();
            $table->string('print_number',255)->comment('図面番号');
            $table->string('processing_item',255)->comment('加工品目');
            $table->string('item_name',255)->comment('品目名称');
            $table->string('material_item',255)->comment('材料品目');
            $table->string('child_part_number1',255)->comment('子品番1');
            $table->string('child_part_number2',255)->comment('子品番2');
            $table->unsignedBigInteger('factory_id')->comment('工場ID');
            $table->unsignedBigInteger('department_id')->comment('部署ID');
            $table->string('line',255)->comment('ライン');
            $table->string('join_flag',255)->comment('結合フラグ');
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('number');
    }
};
