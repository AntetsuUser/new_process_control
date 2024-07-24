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
        Schema::create('shipping_info', function (Blueprint $table) {
            $table->id();
            $table->string('item_code')->nullable()->comment('品目コード');
            $table->string('item_name')->nullable()->comment('品目名称');
            $table->string('delivery_date')->nullable()->comment('要求納期');
            $table->integer('ordering_quantity')->nullable()->comment('注文数量');
            $table->text('note')->comment('備考');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipping_info');
    }
};
