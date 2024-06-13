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
        Schema::create('order_logistics', function (Blueprint $table) {
            $table->id();
            $table->string('member_id')->comment('會員ID');
            $table->string('logistics_sub_type')->nullable();
            $table->string('cvs_store_iD')->nullable();
            $table->string('cvs_store_name')->nullable();
            $table->string('cvs_address')->nullable();
            $table->string('cvs_telephone')->nullable();
            $table->string('cvs_out_side')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_logistics');
    }
};
