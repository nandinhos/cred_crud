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
        Schema::create('ranks', function (Blueprint $table): void {
            $table->id();
            $table->string('abbreviation'); // Ex: 'Sd', 'Cb', 'Alm'
            $table->string('name'); // Ex: 'Soldado', 'Cabo', 'Almirante'
            $table->string('armed_force'); // Ex: 'Exército', 'Marinha', 'Aeronáutica'
            $table->integer('hierarchy_order')->index(); // Para ordenação hierárquica (quanto maior, mais alto)
            $table->timestamps();

            // Índice único composto: abbreviation + armed_force (pois 'Cb' existe nas 3 forças)
            $table->unique(['abbreviation', 'armed_force'], 'ranks_abbreviation_force_unique');

            // Índices para otimização de queries
            $table->index('armed_force');
            $table->index(['armed_force', 'hierarchy_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ranks');
    }
};
