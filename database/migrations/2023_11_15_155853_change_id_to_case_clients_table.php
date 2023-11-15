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
            DB::statement('ALTER TABLE case_clients MODIFY id INTEGER AUTO_INCREMENT');
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
