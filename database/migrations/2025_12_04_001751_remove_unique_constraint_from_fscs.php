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
            // Remover constraint unique do campo fscs
            // Múltiplas credenciais podem ter fscs "00000" (negadas)
            $table->dropUnique('credentials_fscs_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('credentials', function (Blueprint $table) {
            // Restaurar constraint unique no fscs
            $table->unique('fscs', 'credentials_fscs_unique');
        });
    }
};
