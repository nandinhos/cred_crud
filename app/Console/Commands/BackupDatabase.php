<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class BackupDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:backup {--keep=5 : Número de backups a manter}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cria um backup do banco de dados MySQL';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔄 Iniciando backup do banco de dados...');
        $this->newLine();

        // Informações do banco
        $database = config('database.connections.mysql.database');
        $host = config('database.connections.mysql.host');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');

        $this->line("Database: {$database}");
        $this->line("Host: {$host}");
        $this->line("User: {$username}");
        $this->newLine();

        // Criar diretório se não existir
        $backupDir = storage_path('app/backups');
        if (! is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        // Nome do arquivo
        $filename = 'database_'.now()->format('Y-m-d_His').'.sql';
        $filepath = $backupDir.'/'.$filename;

        // Comando mysqldump (sem senha no comando por segurança)
        $command = sprintf(
            'MYSQL_PWD=%s mysqldump -h %s -u %s --single-transaction --quick %s > %s 2>&1',
            escapeshellarg($password),
            escapeshellarg($host),
            escapeshellarg($username),
            escapeshellarg($database),
            escapeshellarg($filepath)
        );

        // Executar backup
        exec($command, $output, $returnCode);

        // Verificar se foi bem sucedido
        if ($returnCode !== 0 || ! file_exists($filepath) || filesize($filepath) === 0) {
            $this->error('✗ Erro ao criar backup!');
            if (file_exists($filepath)) {
                unlink($filepath);
            }

            return Command::FAILURE;
        }

        // Sucesso
        $size = $this->formatBytes(filesize($filepath));
        $this->info('✓ Backup criado com sucesso!');
        $this->line("✓ Arquivo: storage/app/backups/{$filename}");
        $this->line("✓ Tamanho: {$size}");
        $this->newLine();

        // Limpar backups antigos
        $keep = (int) $this->option('keep');
        $this->info("🗑️  Limpando backups antigos (mantendo {$keep})...");

        $files = glob($backupDir.'/database_*.sql');
        usort($files, fn ($a, $b) => filemtime($b) - filemtime($a));

        $removed = 0;
        foreach (array_slice($files, $keep) as $oldFile) {
            unlink($oldFile);
            $removed++;
        }

        $kept = min(count($files), $keep);
        $this->line("✓ Mantidos: {$kept} backups");
        $this->line("✓ Removidos: {$removed} backups antigos");
        $this->newLine();

        // Listar backups disponíveis
        $this->info('Backups disponíveis:');
        $remainingFiles = array_slice($files, 0, $keep);

        foreach ($remainingFiles as $index => $file) {
            if (! file_exists($file)) {
                continue;
            }

            $name = basename($file);
            $size = $this->formatBytes(filesize($file));
            $time = now()->diffForHumans(now()->setTimestamp(filemtime($file)));

            $this->line('  '.($index + 1).". {$name} ({$size}) - {$time}");
        }

        return Command::SUCCESS;
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2).' '.$units[$pow];
    }
}
