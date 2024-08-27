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
        Schema::create('production_print_history', function (Blueprint $table) {
            $table->id();
            $table->integer('departments_id')->nullable()->comment('製造課ID');
            $table->integer('print_count')->nullable()->comment('印刷カウント');
            $table->timestamps();
        });
    }
4
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_print_history');
    }
};
