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
        Schema::table('picked_tickets', function (Blueprint $table) {
            $table->dropColumn('deposit_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('picked_tickets', function (Blueprint $table) {
            $table->integer('deposit_id')->default(0);
        });
    }
};
