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
        Schema::table('material_stock', function (Blueprint $table) {
            $table->string('material_for_mark')->nullable()->comment('マーク用材料在庫')->after('material_stock'); // 追加するカラムの設定
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('material_stock', function (Blueprint $table) {
            //
        });
    }
};
