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
        Schema::table('credentials', function (Blueprint $table) {
            // Tornar FSCS e Credential opcionais para permitir TCMS sem credencial
            $table->string('fscs')->nullable()->change();
            $table->string('credential')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('credentials', function (Blueprint $table) {
            // Reverter para NOT NULL
            $table->string('fscs')->nullable(false)->change();
            $table->string('credential')->nullable(false)->change();
        });
    }
};
