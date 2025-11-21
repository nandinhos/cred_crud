<?php

namespace Database\Seeders;

use App\Models\Credential;
use App\Models\User;
use Illuminate\Database\Seeder;

class CredentialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superAdmin = User::role('super_admin')->first();
        $admin = User::role('admin')->first();
        $consulta = User::role('consulta')->first();
        $users = User::all();

        // Credenciais ATIVAS tipo CRED (com concessão)
        $this->command->info('🟢 Criando credenciais ATIVAS (CRED)...');

        Credential::factory()
            ->cred()
            ->active()
            ->count(15)
            ->create([
                'user_id' => $users->random()->id,
            ]);

        // CREDENCIAIS PENDENTES tipo CRED (sem concessão)
        $this->command->info('🟡 Criando credenciais PENDENTES (CRED)...');

        Credential::factory()
            ->cred()
            ->pending()
            ->count(8)
            ->create([
                'user_id' => $users->random()->id,
            ]);

        // CREDENCIAIS EM PROCESSAMENTO tipo TCMS
        $this->command->info('🔵 Criando credenciais EM PROCESSAMENTO (TCMS)...');

        Credential::factory()
            ->tcms()
            ->count(10)
            ->create([
                'user_id' => $users->random()->id,
                'concession' => now()->subMonths(rand(1, 6)),
            ]);

        // CREDENCIAIS VENCIDAS
        $this->command->info('🔴 Criando credenciais VENCIDAS...');

        Credential::factory()
            ->cred()
            ->expired()
            ->count(12)
            ->create([
                'user_id' => $users->random()->id,
            ]);

        // Credenciais específicas por nível de sigilo
        $this->command->info('🔐 Criando credenciais por nível de SIGILO...');

        // 5 credenciais SECRETAS (S)
        Credential::factory()
            ->secret()
            ->count(5)
            ->create([
                'user_id' => $superAdmin->id,
                'validity' => now()->addMonths(rand(6, 12)),
            ]);

        // 10 credenciais RESERVADAS (R)
        Credential::factory()
            ->reserved()
            ->count(10)
            ->create([
                'user_id' => $admin->id,
            ]);

        // CREDENCIAIS NEGADAS
        $this->command->info('⚫ Criando credenciais NEGADAS...');

        Credential::factory()
            ->count(3)
            ->sequence(
                ['fscs' => '00000'],
                ['fscs' => '00001'],
                ['fscs' => '00002'],
            )
            ->create([
                'user_id' => $users->random()->id,
            ]);

        // Estatísticas finais
        $this->command->info('');
        $this->command->info('✅ Credenciais criadas com sucesso!');
        $this->command->info('📊 Total de credenciais: '.Credential::count());
        $this->command->info('📄 CRED: '.Credential::where('type', 'CRED')->count());
        $this->command->info('📋 TCMS: '.Credential::where('type', 'TCMS')->count());
        $this->command->info('🔐 Secretas (S): '.Credential::where('secrecy', 'S')->count());
        $this->command->info('🛡️  Reservadas (R): '.Credential::where('secrecy', 'R')->count());
        $this->command->info('');
        $this->command->info('Por Status:');
        // Contar por status usando o accessor
        $all = Credential::all();
        $this->command->info('🟢 Ativas: '.$all->where('status', 'Ativa')->count());
        $this->command->info('🟡 Pendentes: '.$all->where('status', 'Pendente')->count());
        $this->command->info('🔵 Em Processamento: '.$all->where('status', 'Em Processamento')->count());
        $this->command->info('🔴 Vencidas: '.$all->where('status', 'Vencida')->count());
        $this->command->info('⚫ Negadas: '.$all->where('status', 'Negada')->count());
    }
}
