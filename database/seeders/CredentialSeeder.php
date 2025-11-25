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
        $this->command->info('🔐 Criando 50 credenciais (uma por usuário)...');

        $users = User::all();

        if ($users->count() < 50) {
            $this->command->warn('⚠️  Menos de 50 usuários encontrados. Execute UserSeeder primeiro.');
            return;
        }

        // Distribuição de credenciais para testes completos
        $distributions = [
            // Vencidas (10 credenciais)
            ['status' => 'vencida', 'days' => -30, 'count' => 2],
            ['status' => 'vencida', 'days' => -15, 'count' => 2],
            ['status' => 'vencida', 'days' => -7, 'count' => 2],
            ['status' => 'vencida', 'days' => -3, 'count' => 2],
            ['status' => 'vencida', 'days' => -1, 'count' => 2],
            
            // Críticas - 1 a 15 dias (10 credenciais)
            ['status' => 'critica', 'days' => 1, 'count' => 2],
            ['status' => 'critica', 'days' => 5, 'count' => 2],
            ['status' => 'critica', 'days' => 10, 'count' => 3],
            ['status' => 'critica', 'days' => 15, 'count' => 3],
            
            // Atenção - 16 a 30 dias (8 credenciais)
            ['status' => 'atencao', 'days' => 16, 'count' => 2],
            ['status' => 'atencao', 'days' => 20, 'count' => 2],
            ['status' => 'atencao', 'days' => 25, 'count' => 2],
            ['status' => 'atencao', 'days' => 30, 'count' => 2],
            
            // Alerta - 31 a 45 dias (7 credenciais)
            ['status' => 'alerta', 'days' => 31, 'count' => 2],
            ['status' => 'alerta', 'days' => 38, 'count' => 2],
            ['status' => 'alerta', 'days' => 45, 'count' => 3],
            
            // Início - 46 a 60 dias (7 credenciais)
            ['status' => 'inicio', 'days' => 46, 'count' => 2],
            ['status' => 'inicio', 'days' => 53, 'count' => 2],
            ['status' => 'inicio', 'days' => 60, 'count' => 3],
            
            // Normal - > 60 dias (5 credenciais)
            ['status' => 'normal', 'days' => 90, 'count' => 2],
            ['status' => 'normal', 'days' => 180, 'count' => 2],
            ['status' => 'normal', 'days' => 365, 'count' => 1],
            
            // Pendentes - sem concessão (2 credenciais)
            ['status' => 'pendente', 'days' => null, 'count' => 2],
            
            // Negada (1 credencial)
            ['status' => 'negada', 'days' => null, 'count' => 1],
        ];

        $userIndex = 0;
        $credentialsCreated = 0;

        foreach ($distributions as $dist) {
            for ($i = 0; $i < $dist['count']; $i++) {
                if ($userIndex >= $users->count()) {
                    break;
                }

                $user = $users[$userIndex];
                $userIndex++;

                $fscs = str_pad($credentialsCreated + 1, 5, '0', STR_PAD_LEFT);
                
                // Credencial Negada
                if ($dist['status'] === 'negada') {
                    Credential::create([
                        'user_id' => $user->id,
                        'fscs' => '00000',
                        'type' => 'CRED',
                        'secrecy' => 'R',
                        'credential' => 'NEGADA-' . $fscs,
                        'concession' => null,
                        'validity' => null,
                    ]);
                    $this->command->info("  ⚫ Negada: {$user->name}");
                    $credentialsCreated++;
                    continue;
                }

                // Credencial Pendente (sem concessão)
                if ($dist['status'] === 'pendente') {
                    Credential::create([
                        'user_id' => $user->id,
                        'fscs' => $fscs,
                        'type' => 'CRED',
                        'secrecy' => ['R', 'S'][rand(0, 1)],
                        'credential' => 'CRED-' . $fscs,
                        'concession' => null,
                        'validity' => null, // Será calculada quando houver concessão
                    ]);
                    $this->command->info("  🟡 Pendente: {$user->name}");
                    $credentialsCreated++;
                    continue;
                }

                // Credenciais com validade
                // Calcular data de concessão baseado na validade desejada
                // CRED: validade = concessão + 2 anos
                // TCMS: validade = concessão + 1 ano
                
                $desiredValidityDate = now()->addDays($dist['days']);
                
                // Determinar tipo (maioria CRED, alguns TCMS para variedade)
                $isTCMS = $credentialsCreated % 10 === 0; // 10% TCMS
                $type = $isTCMS ? 'TCMS' : 'CRED';
                
                // Calcular concessão baseado no tipo
                $concessionDate = $isTCMS 
                    ? $desiredValidityDate->copy()->subYear() 
                    : $desiredValidityDate->copy()->subYears(2);
                
                // Sigilo baseado no tipo
                // CRED: apenas R ou S
                // TCMS: apenas AR
                $secrecy = $isTCMS ? 'AR' : ['R', 'S'][rand(0, 1)];

                Credential::create([
                    'user_id' => $user->id,
                    'fscs' => $fscs,
                    'type' => $type,
                    'secrecy' => $secrecy,
                    'credential' => $type . '-' . $fscs,
                    'concession' => $concessionDate,
                    'validity' => $desiredValidityDate,
                ]);

                $emoji = match($dist['status']) {
                    'vencida' => '🔴',
                    'critica' => '🟠',
                    'atencao' => '🟡',
                    'alerta' => '🟡',
                    'inicio' => '🟢',
                    'normal' => '✅',
                    default => '⚪',
                };

                $this->command->info("  {$emoji} {$dist['status']}: {$user->name} (validade: {$desiredValidityDate->format('d/m/Y')})");
                $credentialsCreated++;
            }
        }

        // Estatísticas finais
        $this->command->info('');
        $this->command->info('✅ Credenciais criadas com sucesso!');
        $this->command->info('📊 Total de credenciais: ' . Credential::count());
        $this->command->info('👥 Usuários com credenciais: ' . $credentialsCreated);
        $this->command->info('');
        
        $all = Credential::all();
        $this->command->info('Por Status:');
        $this->command->info('🔴 Vencidas: ' . $all->where('status', 'Vencida')->count());
        $this->command->info('🟠 Críticas (1-15d): ' . $all->filter(function($c) {
            return $c->validity && !$c->validity->isPast() && now()->diffInDays($c->validity, false) <= 15;
        })->count());
        $this->command->info('🟡 Atenção (16-30d): ' . $all->filter(function($c) {
            return $c->validity && !$c->validity->isPast() && now()->diffInDays($c->validity, false) > 15 && now()->diffInDays($c->validity, false) <= 30;
        })->count());
        $this->command->info('🟡 Alerta (31-45d): ' . $all->filter(function($c) {
            return $c->validity && !$c->validity->isPast() && now()->diffInDays($c->validity, false) > 30 && now()->diffInDays($c->validity, false) <= 45;
        })->count());
        $this->command->info('🟢 Início (46-60d): ' . $all->filter(function($c) {
            return $c->validity && !$c->validity->isPast() && now()->diffInDays($c->validity, false) > 45 && now()->diffInDays($c->validity, false) <= 60;
        })->count());
        $this->command->info('✅ Normal (>60d): ' . $all->filter(function($c) {
            return $c->validity && !$c->validity->isPast() && now()->diffInDays($c->validity, false) > 60;
        })->count());
        $this->command->info('🟡 Pendentes: ' . $all->where('status', 'Pendente')->count());
        $this->command->info('⚫ Negadas: ' . $all->where('status', 'Negada')->count());
    }
}
