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
        // Primeiro: renomear coluna e adicionar nova coluna
        Schema::table('credentials', function (Blueprint $table): void {
            $table->renameColumn('name', 'type');
        });

        Schema::table('credentials', function (Blueprint $table): void {
            $table->text('observation')->nullable()->after('credential');
        });

        // Segundo: alterar constraints para NOT NULL
        Schema::table('credentials', function (Blueprint $table): void {
            $table->foreignId('user_id')->nullable(false)->change();
            $table->string('type')->nullable(false)->change();
            $table->string('secrecy')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverter constraints
        Schema::table('credentials', function (Blueprint $table): void {
            $table->foreignId('user_id')->nullable()->change();
            $table->string('type')->nullable()->change();
            $table->string('secrecy')->nullable()->change();
        });

        // Remover coluna observation
        Schema::table('credentials', function (Blueprint $table): void {
            $table->dropColumn('observation');
        });

        // Renomear coluna de volta
        Schema::table('credentials', function (Blueprint $table): void {
            $table->renameColumn('type', 'name');
        });
    }
};
