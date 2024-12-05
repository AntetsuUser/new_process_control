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
        Schema::table('material_arrival', function (Blueprint $table) {
            // 追加するカラムの設定
            //history_id
            $table->string('is_matched')->nullable()->comment('一致したか')->after('note'); 
            $table->string('status')->nullable()->comment('反映したか')->after('is_matched'); 
            $table->string('history_id')->nullable()->comment('履歴ID')->after('status'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('material_arrival', function (Blueprint $table) {
            //
        });
    }
};
