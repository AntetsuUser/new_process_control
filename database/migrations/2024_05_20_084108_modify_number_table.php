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
        Schema::table('number', function (Blueprint $table) {
            // collect_nameカラムをmaterial_itemカラムの後ろに追加
            // collect_name: 品目集約
            $table->string('collect_name')->nullable()->after('material_item')->comment('品目集約');
        });

        Schema::table('number', function (Blueprint $table) {
            // material_item, child_part_number1, child_part_number2, collect_nameカラムのnullを許可
            // material_item: 材料品目
            // child_part_number1, child_part_number2: 子品番号1, 子品番号2
            $table->string('material_item')->nullable()->change()->comment('材料品目');
            $table->string('child_part_number1')->nullable()->change()->comment('子品番号1');
            $table->string('child_part_number2')->nullable()->change()->comment('子品番号2');
            $table->string('collect_name')->nullable()->change()->comment('品目集約');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('number', function (Blueprint $table) {
            $table->dropColumn('collect_name');
        });
    }
};
