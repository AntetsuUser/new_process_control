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
        Schema::create('print_history', function (Blueprint $table) 
        {
            $table->id();
            $table->unsignedBigInteger('characteristic_id')->comment('固有ID');
            $table->string('item_name',255)->comment('品目名称');
            $table->string('processing_item',255)->comment('加工品目');
            $table->string('parent_name',255)->comment('親品番');
            $table->string('child_part_number1',255)->comment('子品番1');
            $table->string('child_part_number2',255)->comment('子品番2');
            $table->string('child_part_number2',255)->comment('納期');
            $table->integer('processing_quantity')->comment('加工数');
            $table->date('start_date')->comment('着手日');
            $table->unsignedBigInteger('woker_id')->comment('作業者_id');
            $table->string('process',255)->comment('工程');
            $table->string('workcenter',255)->comment('W/C');
            $table->date('capture_date')->comment('長期作成日');
            $table->integer('processing_all')->comment('加工全数');
            $table->integer('long_term_all')->comment('長期全数');
            $table->string('input_complete_flag',255)->comment('作業フラグ');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('print_history');
    }
};
