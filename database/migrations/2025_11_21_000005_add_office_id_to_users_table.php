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
        Schema::table('users', function (Blueprint $table): void {
            $table->foreignId('office_id')
                ->nullable()
                ->after('rank_id')
                ->constrained('offices')
                ->onDelete('set null');

            // Índice para otimização
            $table->index('office_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropForeign(['office_id']);
            $table->dropIndex(['office_id']);
            $table->dropColumn('office_id');
        });
    }
};
