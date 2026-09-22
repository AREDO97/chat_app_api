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
        Schema::table('conversation_users', function (Blueprint $table) {
            // added role owner
             $table->enum('role',['admin','owner','member'])->default('member');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('conversation_users', function (Blueprint $table) {
            //
            $table->dropColumn('role');
        });
    }
};
