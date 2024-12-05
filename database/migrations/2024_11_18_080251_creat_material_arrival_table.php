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
        Schema::create('material_arrival', function (Blueprint $table) {
            $table->id();
            $table->string('arrival_date')->nullable()->comment('入荷日');
            $table->string('item_code')->nullable()->comment('品目コード');
            $table->string('quantity')->nullable()->comment('数量');
            $table->string('note')->nullable()->comment('備考');
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
