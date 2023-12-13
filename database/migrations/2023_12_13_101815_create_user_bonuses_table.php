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
        Schema::create('user_bonus', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->nullable()->comment('bonus user id');
            $table->integer('bonus_id')->nullable()->comment('bonus_id');
            $table->integer('coins')->nullable()->comment('coins');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_bonuses');
    }
};
