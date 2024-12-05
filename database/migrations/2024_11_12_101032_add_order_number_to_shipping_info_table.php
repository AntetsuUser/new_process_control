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
             if (!Schema::hasColumn('shipping_info', 'order_number')) {
                $table->string('order_number')->nullable()->comment('注文番号')->after('history_id');
            }
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
