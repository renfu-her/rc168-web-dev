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
            $table->integer('user_join_id')->nullable()->comment('被委託的使用者 ID');
            $table->integer('case_join_id')->nullable()->comment('案件的 ID');
            $table->dropColumn('case_id');
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
