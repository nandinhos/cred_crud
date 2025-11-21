<?php

namespace Database\Seeders;

use App\Models\Office;
use Illuminate\Database\Seeder;

class OfficeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🏢 Populando tabela de Unidades Militares...');

        $offices = [
            [
                'office' => 'GAC-PAC',
                'description' => 'Grupo de Acompanhamento e Controle do Programa Aeronave de Combate',
            ],
            [
                'office' => 'SCP-EMB',
                'description' => 'Subseção de Coordenação de Projetos Embraer',
            ],
            [
                'office' => 'ECP-GPX',
                'description' => 'Escritório de Coordenação de Projetos de Gavião Peixoto - SP',
            ],
            [
                'office' => 'ECP-IJA',
                'description' => 'Escritório de Coordenação de Projetos de Itajubá - MG',
            ],
            [
                'office' => 'ECP-POA',
                'description' => 'Escritório de Coordenação de Projetos de Porto Alegre - RS',
            ],
        ];

        foreach ($offices as $office) {
            Office::create($office);
        }

        $this->command->info('');
        $this->command->info('✅ Unidades Militares criadas com sucesso!');
        $this->command->info('📊 Total de offices: '.Office::count());
        $this->command->info('');
    }
}
