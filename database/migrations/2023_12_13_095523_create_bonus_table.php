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
        Schema::create('bonus', function (Blueprint $table) {
            $table->id();
            $table->string('icon')->nullable()->comment('icon');
            $table->string('title')->nullable()->comment('標題');
            $table->string('sub_title')->nullable()->comment('子標題');
            $table->string('expiry_date')->nullable()->comment('權限日期');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bonus_controllers');
    }
};
