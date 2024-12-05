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
        Schema::create('all_departments', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('部署名');  // 部署名
            $table->text('description')->nullable()->comment('説明');  // 部署名の説明（オプション）後から入力
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('all_departments');
    }
};
