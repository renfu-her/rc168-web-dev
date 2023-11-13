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
        Schema::create('case_joins', function (Blueprint $table) {
            $table->id();
            $table->integer('case_id')->nullable()->comment('案件 ID');
            $table->integer('user_id')->nullable()->comment('會員 ID');
            $table->string('payment')->nullable()->comment('金額');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('case_joins');
    }
};
