<?php

namespace Database\Seeders;

use App\Models\Rank;
use Illuminate\Database\Seeder;

class RankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🎖️ Populando tabela de Postos e Graduações...');

        $ranks = [
            // Marinha - Hierarquia de 1 (mais baixo) a 17 (mais alto)
            ['abbreviation' => 'MN', 'name' => 'Marinheiro', 'armed_force' => 'Marinha', 'hierarchy_order' => 1],
            ['abbreviation' => 'CB', 'name' => 'Cabo', 'armed_force' => 'Marinha', 'hierarchy_order' => 2],
            ['abbreviation' => 'SD-FN', 'name' => 'Soldado Fuzileiro Naval', 'armed_force' => 'Marinha', 'hierarchy_order' => 3],
            ['abbreviation' => '3SG', 'name' => 'Terceiro-Sargento', 'armed_force' => 'Marinha', 'hierarchy_order' => 4],
            ['abbreviation' => '2SG', 'name' => 'Segundo-Sargento', 'armed_force' => 'Marinha', 'hierarchy_order' => 5],
            ['abbreviation' => '1SG', 'name' => 'Primeiro-Sargento', 'armed_force' => 'Marinha', 'hierarchy_order' => 6],
            ['abbreviation' => 'SO', 'name' => 'Suboficial', 'armed_force' => 'Marinha', 'hierarchy_order' => 7],
            ['abbreviation' => 'GM', 'name' => 'Guarda-Marinha', 'armed_force' => 'Marinha', 'hierarchy_order' => 8],
            ['abbreviation' => '2T', 'name' => 'Segundo-Tenente', 'armed_force' => 'Marinha', 'hierarchy_order' => 9],
            ['abbreviation' => '1T', 'name' => 'Primeiro-Tenente', 'armed_force' => 'Marinha', 'hierarchy_order' => 10],
            ['abbreviation' => 'CT', 'name' => 'Capitão-Tenente', 'armed_force' => 'Marinha', 'hierarchy_order' => 11],
            ['abbreviation' => 'CC', 'name' => 'Capitão de Corveta', 'armed_force' => 'Marinha', 'hierarchy_order' => 12],
            ['abbreviation' => 'CF', 'name' => 'Capitão de Fragata', 'armed_force' => 'Marinha', 'hierarchy_order' => 13],
            ['abbreviation' => 'CMG', 'name' => 'Capitão de Mar e Guerra', 'armed_force' => 'Marinha', 'hierarchy_order' => 14],
            ['abbreviation' => 'CA', 'name' => 'Contra-Almirante', 'armed_force' => 'Marinha', 'hierarchy_order' => 15],
            ['abbreviation' => 'VA', 'name' => 'Vice-Almirante', 'armed_force' => 'Marinha', 'hierarchy_order' => 16],
            ['abbreviation' => 'Alm Esq', 'name' => 'Almirante de Esquadra', 'armed_force' => 'Marinha', 'hierarchy_order' => 17],
            ['abbreviation' => 'Alm', 'name' => 'Almirante (Em Guerra)', 'armed_force' => 'Marinha', 'hierarchy_order' => 18],

            // Exército - Hierarquia de 1 (mais baixo) a 17 (mais alto)
            ['abbreviation' => 'Sd', 'name' => 'Soldado', 'armed_force' => 'Exército', 'hierarchy_order' => 1],
            ['abbreviation' => 'Cb', 'name' => 'Cabo', 'armed_force' => 'Exército', 'hierarchy_order' => 2],
            ['abbreviation' => '3º Sgt', 'name' => 'Terceiro-Sargento', 'armed_force' => 'Exército', 'hierarchy_order' => 3],
            ['abbreviation' => '2º Sgt', 'name' => 'Segundo-Sargento', 'armed_force' => 'Exército', 'hierarchy_order' => 4],
            ['abbreviation' => '1º Sgt', 'name' => 'Primeiro-Sargento', 'armed_force' => 'Exército', 'hierarchy_order' => 5],
            ['abbreviation' => 'S Ten', 'name' => 'Subtenente', 'armed_force' => 'Exército', 'hierarchy_order' => 6],
            ['abbreviation' => 'Asp', 'name' => 'Aspirante-a-Oficial', 'armed_force' => 'Exército', 'hierarchy_order' => 7],
            ['abbreviation' => '2º Ten', 'name' => 'Segundo-Tenente', 'armed_force' => 'Exército', 'hierarchy_order' => 8],
            ['abbreviation' => '1º Ten', 'name' => 'Primeiro-Tenente', 'armed_force' => 'Exército', 'hierarchy_order' => 9],
            ['abbreviation' => 'Cap', 'name' => 'Capitão', 'armed_force' => 'Exército', 'hierarchy_order' => 10],
            ['abbreviation' => 'Maj', 'name' => 'Major', 'armed_force' => 'Exército', 'hierarchy_order' => 11],
            ['abbreviation' => 'TC', 'name' => 'Tenente-Coronel', 'armed_force' => 'Exército', 'hierarchy_order' => 12],
            ['abbreviation' => 'Cel', 'name' => 'Coronel', 'armed_force' => 'Exército', 'hierarchy_order' => 13],
            ['abbreviation' => 'Gen Bda', 'name' => 'General de Brigada', 'armed_force' => 'Exército', 'hierarchy_order' => 14],
            ['abbreviation' => 'Gen Div', 'name' => 'General de Divisão', 'armed_force' => 'Exército', 'hierarchy_order' => 15],
            ['abbreviation' => 'Gen Ex', 'name' => 'General de Exército', 'armed_force' => 'Exército', 'hierarchy_order' => 16],
            ['abbreviation' => 'Mal', 'name' => 'Marechal (Em Guerra)', 'armed_force' => 'Exército', 'hierarchy_order' => 17],

            // Aeronáutica - Hierarquia de 1 (mais baixo) a 18 (mais alto)
            ['abbreviation' => 'S2', 'name' => 'Soldado 2ª Classe', 'armed_force' => 'Aeronáutica', 'hierarchy_order' => 1],
            ['abbreviation' => 'S1', 'name' => 'Soldado 1ª Classe', 'armed_force' => 'Aeronáutica', 'hierarchy_order' => 2],
            ['abbreviation' => 'Cb', 'name' => 'Cabo', 'armed_force' => 'Aeronáutica', 'hierarchy_order' => 3],
            ['abbreviation' => '3S', 'name' => 'Terceiro-Sargento', 'armed_force' => 'Aeronáutica', 'hierarchy_order' => 4],
            ['abbreviation' => '2S', 'name' => 'Segundo-Sargento', 'armed_force' => 'Aeronáutica', 'hierarchy_order' => 5],
            ['abbreviation' => '1S', 'name' => 'Primeiro-Sargento', 'armed_force' => 'Aeronáutica', 'hierarchy_order' => 6],
            ['abbreviation' => 'SO', 'name' => 'Suboficial', 'armed_force' => 'Aeronáutica', 'hierarchy_order' => 7],
            ['abbreviation' => 'Asp', 'name' => 'Aspirante-a-Oficial', 'armed_force' => 'Aeronáutica', 'hierarchy_order' => 8],
            ['abbreviation' => '2º Ten', 'name' => 'Segundo-Tenente', 'armed_force' => 'Aeronáutica', 'hierarchy_order' => 9],
            ['abbreviation' => '1º Ten', 'name' => 'Primeiro-Tenente', 'armed_force' => 'Aeronáutica', 'hierarchy_order' => 10],
            ['abbreviation' => 'Cap', 'name' => 'Capitão', 'armed_force' => 'Aeronáutica', 'hierarchy_order' => 11],
            ['abbreviation' => 'Maj', 'name' => 'Major', 'armed_force' => 'Aeronáutica', 'hierarchy_order' => 12],
            ['abbreviation' => 'Ten Cel', 'name' => 'Tenente-Coronel', 'armed_force' => 'Aeronáutica', 'hierarchy_order' => 13],
            ['abbreviation' => 'Cel', 'name' => 'Coronel', 'armed_force' => 'Aeronáutica', 'hierarchy_order' => 14],
            ['abbreviation' => 'Brig', 'name' => 'Brigadeiro', 'armed_force' => 'Aeronáutica', 'hierarchy_order' => 15],
            ['abbreviation' => 'Maj Brig', 'name' => 'Major-Brigadeiro', 'armed_force' => 'Aeronáutica', 'hierarchy_order' => 16],
            ['abbreviation' => 'Ten Brig', 'name' => 'Tenente-Brigadeiro do Ar', 'armed_force' => 'Aeronáutica', 'hierarchy_order' => 17],
            ['abbreviation' => 'Mal Ar', 'name' => 'Marechal do Ar (Em Guerra)', 'armed_force' => 'Aeronáutica', 'hierarchy_order' => 18],
        ];

        foreach ($ranks as $rank) {
            Rank::create($rank);
        }

        $this->command->info('');
        $this->command->info('✅ Postos e Graduações criados com sucesso!');
        $this->command->info('📊 Total de ranks: '.Rank::count());
        $this->command->info('⚓ Marinha: '.Rank::where('armed_force', 'Marinha')->count());
        $this->command->info('🪖 Exército: '.Rank::where('armed_force', 'Exército')->count());
        $this->command->info('✈️  Aeronáutica: '.Rank::where('armed_force', 'Aeronáutica')->count());
    }
}
