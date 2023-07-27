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
        Schema::create('credentials', function (Blueprint $table) {
            $table->id();
            $table->string('fscs'); 
            $table->string('name'); 
            $table->string('secrecy'); 
            $table->string('credential'); 
            $table->date('concession')->nullable()->default(NULL); // Permitindo que o campo seja nulo e definindo o valor padrão como NULL
            $table->date('validity')->nullable()->default(NULL);; // Permitindo que o campo seja nulo e definindo o valor padrão como NULL
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credentials');
    }
};