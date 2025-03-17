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
        Schema::create('shipment_count', function (Blueprint $table) {
            $table->id();
            $table->string('item_name')->comment('品目コード');
            $table->string('before_update_count')->comment('更新前個数');
            $table->string('shipment_count')->comment('出荷個数');
            //made_count
            $table->string('made_count')->comment('作った個数');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
