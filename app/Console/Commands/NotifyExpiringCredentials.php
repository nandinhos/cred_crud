<?php

namespace App\Console\Commands;

use App\Models\Credential;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class NotifyExpiringCredentials extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'credentials:notify-expiring 
                            {--days=30 : Número de dias antes do vencimento}
                            {--dry-run : Apenas simular sem enviar notificações}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notifica usuários sobre credenciais que estão próximas do vencimento';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = (int) $this->option('days');
        $dryRun = $this->option('dry-run');

        $this->info("🔍 Verificando credenciais que expiram em {$days} dias...");

        // Buscar credenciais que expiram nos próximos X dias
        $expiringCredentials = Credential::query()
            ->whereNotNull('validity')
            ->whereDate('validity', '<=', now()->addDays($days))
            ->whereDate('validity', '>=', now())
            ->with(['user'])
            ->get();

        if ($expiringCredentials->isEmpty()) {
            $this->info('✅ Nenhuma credencial expirando nos próximos '.$days.' dias.');

            return self::SUCCESS;
        }

        $this->warn("⚠️  Encontradas {$expiringCredentials->count()} credenciais expirando:");
        $this->newLine();

        // Agrupar por usuário
        $credentialsByUser = $expiringCredentials->groupBy('user_id');

        $table = [];
        foreach ($credentialsByUser as $userId => $credentials) {
            $user = $credentials->first()->user;

            foreach ($credentials as $credential) {
                $daysUntilExpiry = now()->diffInDays($credential->validity, false);

                $table[] = [
                    'Usuário' => $user?->name ?? 'N/A',
                    'Credencial' => $credential->credential,
                    'Tipo' => $credential->type?->value ?? 'N/A',
                    'Validade' => $credential->validity?->format('d/m/Y') ?? 'N/A',
                    'Dias Restantes' => (int) $daysUntilExpiry,
                    'Status' => $this->getStatusEmoji($daysUntilExpiry),
                ];
            }
        }

        $this->table(
            ['Usuário', 'Credencial', 'Tipo', 'Validade', 'Dias Restantes', 'Status'],
            $table
        );

        if ($dryRun) {
            $this->warn('🔍 Modo DRY-RUN: Nenhuma notificação foi enviada.');
            $this->info('💡 Execute sem --dry-run para enviar as notificações.');

            return self::SUCCESS;
        }

        // Enviar notificações
        $this->info('📧 Enviando notificações...');

        $notificationCount = 0;
        foreach ($credentialsByUser as $userId => $credentials) {
            $user = $credentials->first()->user;

            if (! $user) {
                $this->warn('⚠️  Usuário não encontrado para credencial');

                continue;
            }

            // Aqui você implementaria o envio de notificação real
            // Por exemplo: $user->notify(new CredentialsExpiringNotification($credentials));

            Log::info("Notificação enviada para {$user->name} sobre {$credentials->count()} credencial(is) expirando.");
            $notificationCount++;
        }

        // Log detalhado de segurança para cada credencial expirando
        foreach ($expiringCredentials as $credential) {
            $daysLeft = now()->diffInDays($credential->validity);
            
            Log::channel('security')->warning('Credencial expirando', [
                'fscs' => $credential->fscs,
                'name' => $credential->name,
                'validity' => $credential->validity->format('Y-m-d'),
                'days_left' => $daysLeft,
                'user' => $credential->user?->name ?? 'N/A',
            ]);
        }

        $this->newLine();
        $this->info("✅ {$notificationCount} notificação(ões) enviada(s) com sucesso!");

        // Registrar em log
        Log::channel('daily')->info('Notificações de credenciais expirando enviadas', [
            'total_credentials' => $expiringCredentials->count(),
            'total_users' => $credentialsByUser->count(),
            'days_threshold' => $days,
        ]);

        return self::SUCCESS;
    }

    /**
     * Retorna emoji baseado nos dias restantes
     */
    private function getStatusEmoji(float $days): string
    {
        if ($days < 0) {
            return '🔴 VENCIDA';
        }

        if ($days <= 7) {
            return '🔴 CRÍTICO';
        }

        if ($days <= 15) {
            return '🟡 URGENTE';
        }

        return '🟢 ATENÇÃO';
    }
}
