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
        $this->command->info('🔐 Criando credenciais (uma por usuário)...');

        $users = User::all();

        if ($users->isEmpty()) {
            $this->command->warn('⚠️  Nenhum usuário encontrado. Execute UserSeeder primeiro.');

            return;
        }

        // Distribuir credenciais entre os usuários
        // Cada usuário recebe APENAS UMA credencial

        $totalUsers = $users->count();
        $credentialsCreated = 0;

        // Definir distribuição de credenciais
        $distributions = [
            ['type' => 'cred', 'status' => 'active', 'count' => (int) ($totalUsers * 0.4)], // 40% ativas
            ['type' => 'cred', 'status' => 'pending', 'count' => (int) ($totalUsers * 0.2)], // 20% pendentes
            ['type' => 'tcms', 'status' => 'processing', 'count' => (int) ($totalUsers * 0.2)], // 20% em processamento
            ['type' => 'cred', 'status' => 'expired', 'count' => (int) ($totalUsers * 0.15)], // 15% vencidas
            ['type' => 'cred', 'status' => 'denied', 'count' => (int) ($totalUsers * 0.05)], // 5% negadas
        ];

        $userIndex = 0;

        foreach ($distributions as $dist) {
            $count = min($dist['count'], $totalUsers - $credentialsCreated);

            for ($i = 0; $i < $count; $i++) {
                if ($userIndex >= $totalUsers) {
                    break;
                }

                $user = $users[$userIndex];
                $userIndex++;

                // Criar credencial baseada no tipo e status
                $credentialData = [
                    'user_id' => $user->id,
                ];

                switch ($dist['status']) {
                    case 'active':
                        Credential::factory()
                            ->cred()
                            ->active()
                            ->create($credentialData);
                        $this->command->info("  🟢 Credencial ATIVA criada para {$user->name}");
                        break;

                    case 'pending':
                        Credential::factory()
                            ->cred()
                            ->pending()
                            ->create($credentialData);
                        $this->command->info("  🟡 Credencial PENDENTE criada para {$user->name}");
                        break;

                    case 'processing':
                        Credential::factory()
                            ->tcms()
                            ->create(array_merge($credentialData, [
                                'concession' => now()->subMonths(rand(1, 6)),
                            ]));
                        $this->command->info("  🔵 Credencial EM PROCESSAMENTO criada para {$user->name}");
                        break;

                    case 'expired':
                        Credential::factory()
                            ->cred()
                            ->expired()
                            ->create($credentialData);
                        $this->command->info("  🔴 Credencial VENCIDA criada para {$user->name}");
                        break;

                    case 'denied':
                        Credential::factory()
                            ->create(array_merge($credentialData, [
                                'fscs' => str_pad($i, 5, '0', STR_PAD_LEFT),
                            ]));
                        $this->command->info("  ⚫ Credencial NEGADA criada para {$user->name}");
                        break;
                }

                $credentialsCreated++;
            }
        }

        // Estatísticas finais
        $this->command->info('');
        $this->command->info('✅ Credenciais criadas com sucesso!');
        $this->command->info('📊 Total de credenciais: '.Credential::count());
        $this->command->info('👥 Usuários com credenciais: '.$credentialsCreated);
        $this->command->info('👤 Usuários sem credenciais: '.($totalUsers - $credentialsCreated));
        $this->command->info('');
        $this->command->info('📄 CRED: '.Credential::where('type', 'CRED')->count());
        $this->command->info('📋 TCMS: '.Credential::where('type', 'TCMS')->count());
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
