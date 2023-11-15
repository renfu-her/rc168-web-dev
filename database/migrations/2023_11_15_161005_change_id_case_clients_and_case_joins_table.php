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
        Schema::table('case_clients', function (Blueprint $table) {
            DB::statement('alter table `case_clients` modify `id` bigint unsigned not null auto_increment');
        });

        Schema::table('case_joins', function(Blueprint $table){
            DB::statement('alter table `case_joins` modify `id` bigint unsigned not null auto_increment');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('case_clients', function (Blueprint $table) {
            //
        });
    }
};
