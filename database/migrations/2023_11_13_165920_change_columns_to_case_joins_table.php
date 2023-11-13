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
            $table->integer('case_client_id')->nullable()->comment('委託案件 ID');
            $table->dropColumn('case_join_id');
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
