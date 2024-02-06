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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_id')->comment('訂單 ID');
            $table->integer('amount')->comment('金額');
            $table->string('transaction_id')->comment('line pay 的 transaction id');
            $table->text('info')->comment('line pay information');
            $table->integer('status')->default(0)->comment('0：無效，1: 有效');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
