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
        Schema::create('tablets', function (Blueprint $table) {
            $table->id();
            $table->string('tablet_number')->nullable()->comment('タブレット番号');
            $table->string('tablet_ip')->nullable()->comment('タブレットIP');
            $table->string('supported_item')->nullable()->comment('対応品目');
            $table->integer('factory_id')->comment('工場ID');
            $table->integer('department_id')->comment('部署ID');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tablets');
    }
};
