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
        Schema::create('equipment', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('factory_id')->comment('工場ID');
            $table->unsignedBigInteger('department_id')->comment('部署ID');
            $table->string('line',255)->comment('ライン');
            $table->integer('equipment_id')->comment('設備番号');
            $table->string('category',255)->comment('種類');
            $table->string('model',255)->comment('型式');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment');
    }
};
