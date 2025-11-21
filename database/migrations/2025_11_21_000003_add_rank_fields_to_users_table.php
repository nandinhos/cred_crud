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
            $table->string('full_name')->after('name');
            $table->foreignId('rank_id')
                ->nullable()
                ->after('full_name')
                ->constrained('ranks')
                ->onDelete('set null');
            $table->softDeletes();

            // Índices para otimização
            $table->index('rank_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropForeign(['rank_id']);
            $table->dropIndex(['rank_id']);
            $table->dropColumn(['full_name', 'rank_id', 'deleted_at']);
        });
    }
};
