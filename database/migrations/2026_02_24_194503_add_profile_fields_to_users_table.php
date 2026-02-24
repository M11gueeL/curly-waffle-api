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
        Schema::table('users', function (Blueprint $table) {
            $table->string('last_name')->nullable()->after('name'); 
            // Agregamos nullable() a username y birth_date
            $table->string('username')->nullable()->unique()->after('last_name');
            $table->date('birth_date')->nullable()->after('password');
            
            $table->string('google_id')->nullable()->unique()->after('email'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['last_name', 'username', 'birth_date', 'google_id']);
        });
    }
};
