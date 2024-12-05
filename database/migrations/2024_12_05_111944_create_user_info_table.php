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
        Schema::create('user_info', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('ユーザー名');
            $table->string('password')->comment('パスワード（ハッシュ値）'); // ハッシュ化したパスワードを保存
            $table->string('all_departments_id')->comment('部署ID'); // ハッシュ化したパスワードを保存
            $table->string('positions_id')->comment('役職ID'); // ハッシュ化したパスワードを保存
            $table->string('ipaddress')->nullable()->comment('端末IPアドレス');//新規登録後記入

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_info');
    }
};
