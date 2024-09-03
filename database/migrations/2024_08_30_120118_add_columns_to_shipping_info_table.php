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
        Schema::table('shipping_info', function (Blueprint $table) {
            $table->string('application_flag')->comment('反映フラグ');
            $table->integer('history_id')->comment('履歴ID');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shipping_info', function (Blueprint $table) {
            //
        });
    }
};
