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
        Schema::create('material_stock', function (Blueprint $table) {
            $table->id();
            $table->string('material_name')->nullable()->comment('材料品番');
            $table->string('material_stock')->nullable()->comment('材料在庫');
            $table->string('parent_name')->nullable()->comment('親品番');
            $table->string('using_name')->nullable()->comment('使用品番');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_stock');
    }
};
