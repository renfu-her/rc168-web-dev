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
        Schema::create('case_clients', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->nullable()->comment('會員 ID');
            $table->string('title')->nullable()->comment('標題');
            $table->text('content')->nullable()->comment('內容');
            $table->date('start_date')->nullable()->comment('開始日期');
            $table->date('end_date')->nullable()->comment('結束日期');
            $table->string('mobile')->nullable()->comment('手機');
            $table->string('pay')->nullable()->comment('金額');
            $table->string('status')->nullable()->comment('狀態: 1: 上架 2: 已經完成 3: 下架');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('case_clients');
    }
};
