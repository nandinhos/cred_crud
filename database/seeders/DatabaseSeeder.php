<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🚀 Iniciando seed do banco de dados...');
        $this->command->info('');

        // Executar seeders em ordem correta
        $this->call([
            RolesAndPermissionsSeeder::class, // 1. Criar roles e permissions primeiro
            RankSeeder::class,                 // 2. Criar postos e graduações
            UserSeeder::class,                 // 3. Criar usuários com roles e ranks
            CredentialSeeder::class,           // 4. Criar credenciais para os usuários
        ]);

        $this->command->info('');
        $this->command->info('✅ Seed concluído com sucesso!');
    }
}
