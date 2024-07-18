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
        Schema::create('process', function (Blueprint $table) {
            $table->id();
            $table->string('processing_item', 255)->comment('加工品目');
            $table->integer('process_number')->comment('加工工程番号');
            $table->string('process', 255)->comment('工程');
            $table->string('store', 255)->comment('ストア・W/C');
            $table->integer('processing_time')->comment('加工時間(秒)');
            $table->unsignedBigInteger('number_id')->comment('品番マスタID');
            $table->integer('lot')->comment('ロット');
            $table->integer('printing_max')->comment('印刷上限');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('process');
    }
};
