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
        Schema::table('case_joins', function (Blueprint $table) {
            $table->integer('status')->default(0)->comment('狀態 0: 未執行 1: 已完成');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('case_joins', function (Blueprint $table) {
            //
        });
    }
};
