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
        Schema::create('processing_history', function (Blueprint $table) {
            $table->id();
            $table->string('category',255)->comment('種類');
            $table->string('detail',255)->comment('詳細');
            $table->string('file_name',255)->comment('ファイル名');
            $table->datetime('upload_day')->comment('アップロード日');
            $table->date('request_date')->nullable()->comment('要求納期');
            $table->string('delivery',255)->nullable()->comment('便名');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('processing_history');
    }
};
