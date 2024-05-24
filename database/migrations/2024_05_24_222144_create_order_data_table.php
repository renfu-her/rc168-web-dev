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
        Schema::create('order_data', function (Blueprint $table) {
            $table->id();
            $table->string('order_no')->unique()->comment('訂單編號');
            $table->json('data')->nullable()->comment('訂單資料');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_data');
    }
};
