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

        // Credenciais ATIVAS (validade futura ou sem validade)
        $this->command->info('🟢 Criando credenciais ATIVAS...');

        // 10 credenciais ativas com validade futura
        Credential::factory()
            ->count(10)
            ->create([
                'user_id' => $users->random()->id,
                'validity' => now()->addMonths(rand(6, 24)),
                'concession' => now()->subMonths(rand(1, 12)),
            ]);

        // 5 credenciais ativas SEM data de validade (permanentes)
        Credential::factory()
            ->count(5)
            ->create([
                'user_id' => $users->random()->id,
                'validity' => null,
                'concession' => now()->subMonths(rand(1, 12)),
            ]);

        // CREDENCIAIS EXPIRANDO EM 30 DIAS (críticas)
        $this->command->info('🟡 Criando credenciais EXPIRANDO em 30 dias...');

        // 8 credenciais expirando nos próximos 30 dias
        Credential::factory()
            ->count(8)
            ->create([
                'user_id' => $users->random()->id,
                'validity' => now()->addDays(rand(1, 30)),
                'concession' => now()->subMonths(rand(6, 18)),
            ]);

        // CREDENCIAIS EXPIRADAS
        $this->command->info('🔴 Criando credenciais EXPIRADAS...');

        // 12 credenciais já expiradas
        Credential::factory()
            ->count(12)
            ->create([
                'user_id' => $users->random()->id,
                'validity' => now()->subDays(rand(1, 365)),
                'concession' => now()->subMonths(rand(12, 36)),
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

        // 7 credenciais RESERVADAS (R)
        Credential::factory()
            ->reserved()
            ->count(7)
            ->create([
                'user_id' => $admin->id,
                'validity' => now()->addMonths(rand(3, 18)),
            ]);

        // 5 credenciais OSTENSIVAS (O)
        Credential::factory()
            ->count(5)
            ->create([
                'user_id' => $consulta->id,
                'secrecy' => 'O',
                'validity' => now()->addMonths(rand(12, 24)),
            ]);

        // Credenciais SEM DATAS (para testar campos opcionais)
        $this->command->info('📝 Criando credenciais SEM datas...');

        Credential::factory()
            ->count(3)
            ->create([
                'user_id' => $users->random()->id,
                'concession' => null,
                'validity' => null,
            ]);

        // Estatísticas finais
        $this->command->info('');
        $this->command->info('✅ Credenciais criadas com sucesso!');
        $this->command->info('📊 Total de credenciais: '.Credential::count());
        $this->command->info('🟢 Ativas: '.Credential::where('validity', '>', now())->orWhereNull('validity')->count());
        $this->command->info('🟡 Expirando em 30 dias: '.Credential::whereBetween('validity', [now(), now()->addDays(30)])->count());
        $this->command->info('🔴 Expiradas: '.Credential::where('validity', '<', now())->count());
        $this->command->info('🔐 Secretas (S): '.Credential::where('secrecy', 'S')->count());
        $this->command->info('🛡️  Reservadas (R): '.Credential::where('secrecy', 'R')->count());
        $this->command->info('📢 Ostensivas (O): '.Credential::where('secrecy', 'O')->count());
    }
}
