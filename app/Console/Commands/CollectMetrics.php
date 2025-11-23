<?php

namespace App\Console\Commands;

use App\Models\Credential;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CollectMetrics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'metrics:collect';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Coleta métricas do sistema e salva em arquivo JSON';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Coletando métricas do sistema...');

        // Coletar métricas
        $metrics = [
            'timestamp' => now()->toDateTimeString(),
            'date' => now()->toDateString(),
            'users' => $this->getUserMetrics(),
            'credentials' => $this->getCredentialMetrics(),
            'database' => $this->getDatabaseMetrics(),
        ];

        // Criar diretório se não existir
        $metricsDir = storage_path('metrics');
        if (! is_dir($metricsDir)) {
            mkdir($metricsDir, 0755, true);
        }

        // Salvar em arquivo JSON
        $filename = 'metrics_'.now()->format('Y-m-d').'.json';
        $filepath = $metricsDir.'/'.$filename;

        file_put_contents($filepath, json_encode($metrics, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $this->info('✓ Métricas coletadas com sucesso!');
        $this->info("✓ Arquivo: {$filepath}");
        $this->newLine();
        $this->line('Resumo:');
        $this->line("  • Usuários: {$metrics['users']['total']}");
        $this->line("  • Credenciais: {$metrics['credentials']['total']}");
        $this->line("  • Credenciais Expiradas: {$metrics['credentials']['expired']}");
        $this->line("  • Credenciais Expirando em Breve: {$metrics['credentials']['expiring_soon']}");
        $this->line("  • Tamanho do Banco: {$metrics['database']['size_mb']} MB");

        return Command::SUCCESS;
    }

    /**
     * Coleta métricas de usuários
     */
    private function getUserMetrics(): array
    {
        return [
            'total' => User::count(),
            'active' => User::whereNull('deleted_at')->count(),
            'deleted' => User::onlyTrashed()->count(),
            'with_credentials' => User::has('credentials')->count(),
        ];
    }

    /**
     * Coleta métricas de credenciais
     */
    private function getCredentialMetrics(): array
    {
        $now = now();
        $thirtyDaysFromNow = now()->addDays(30);

        return [
            'total' => Credential::count(),
            'active' => Credential::whereNull('deleted_at')->count(),
            'deleted' => Credential::onlyTrashed()->count(),
            'expired' => Credential::whereNotNull('validity')
                ->where('validity', '<', $now)
                ->count(),
            'expiring_soon' => Credential::whereNotNull('validity')
                ->where('validity', '>=', $now)
                ->where('validity', '<=', $thirtyDaysFromNow)
                ->count(),
            'by_type' => [
                'CRED' => Credential::where('type', 'CRED')->count(),
                'TCMS' => Credential::where('type', 'TCMS')->count(),
            ],
            'by_secrecy' => [
                'R' => Credential::where('secrecy', 'R')->count(),
                'S' => Credential::where('secrecy', 'S')->count(),
                'AR' => Credential::where('secrecy', 'AR')->count(),
            ],
        ];
    }

    /**
     * Coleta métricas do banco de dados
     */
    private function getDatabaseMetrics(): array
    {
        $databaseName = config('database.connections.mysql.database');

        $size = DB::select('
            SELECT 
                ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) as size_mb
            FROM information_schema.TABLES
            WHERE table_schema = ?
        ', [$databaseName]);

        $tableCount = DB::select('
            SELECT COUNT(*) as count
            FROM information_schema.TABLES
            WHERE table_schema = ?
        ', [$databaseName]);

        return [
            'name' => $databaseName,
            'size_mb' => $size[0]->size_mb ?? 0,
            'table_count' => $tableCount[0]->count ?? 0,
        ];
    }
}
