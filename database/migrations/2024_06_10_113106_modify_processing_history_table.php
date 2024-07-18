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
        Schema::table('processing_history', function (Blueprint $table) {
            //
            // カラムの削除
            $table->dropColumn('delivery');
            $table->dropColumn('request_date');

            // 新しいカラムの追加
            $table->date('start_date')->nullable()->comment('開始日');
            $table->date('end_date')->nullable()->comment('終了日');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('processing_history', function (Blueprint $table) {
            //
        });
    }
};
