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
            // Adicionar índice único no campo credential (número da credencial)
            // Garante que não existam números de credenciais repetidos (CRED ou TCMS)
            $table->unique('credential', 'credentials_credential_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('credentials', function (Blueprint $table) {
            // Remover o índice único
            $table->dropUnique('credentials_credential_unique');
        });
    }
};
