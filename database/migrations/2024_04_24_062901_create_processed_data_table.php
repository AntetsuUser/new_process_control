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
        Schema::create('processed_data', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('characteristic_id')->comment('固有ID');
            $table->string('item_name',255)->comment('品目名称');
            $table->string('processing_item',255)->comment('加工品目');
            $table->string('parent_name',255)->comment('親品番');
            $table->string('process',255)->comment('工程');
            $table->string('workcenter',255)->comment('W/C');
            $table->integer('processing_plan_quantity')->comment('加工予定数');
            $table->integer('good_item')->comment('良品');
            $table->integer('processing_defect_item')->comment('加工不良');
            $table->integer('material_defect_item')->comment('材料不良');
            $table->date('delivery_date')->comment('納期');
            $table->date('capture_date')->comment('長期作成日');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('processed_data');
    }
};
