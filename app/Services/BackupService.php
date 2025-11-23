<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;

class BackupService
{
    private string $backupPath = 'backups';

    /**
     * Lista todos os backups disponíveis
     */
    public function listBackups(): array
    {
        $disk = Storage::disk('local');

        if (! $disk->exists($this->backupPath)) {
            return [];
        }

        $files = $disk->files($this->backupPath);

        $backups = collect($files)->map(function ($file) use ($disk) {
            return [
                'name' => basename($file),
                'path' => $file,
                'size' => $disk->size($file),
                'size_formatted' => $this->formatBytes($disk->size($file)),
                'date' => $disk->lastModified($file),
                'date_formatted' => Carbon::createFromTimestamp($disk->lastModified($file))->format('d/m/Y H:i'),
                'date_human' => Carbon::createFromTimestamp($disk->lastModified($file))->diffForHumans(),
            ];
        })->sortByDesc('date')->take(5)->values()->all();

        return $backups;
    }

    /**
     * Cria um novo backup
     */
    public function createBackup(): array
    {
        $artisanPath = base_path('artisan');
        $result = Process::run("php {$artisanPath} db:backup");

        return [
            'success' => $result->successful(),
            'output' => $result->output(),
            'error' => $result->errorOutput(),
        ];
    }

    /**
     * Deleta um backup
     */
    public function deleteBackup(string $filename): bool
    {
        $path = $this->backupPath.'/'.$filename;

        if (Storage::disk('local')->exists($path)) {
            return Storage::disk('local')->delete($path);
        }

        return false;
    }

    /**
     * Retorna estatísticas dos backups
     */
    public function getStatistics(): array
    {
        $backups = $this->listBackups();

        return [
            'total' => count($backups),
            'total_size' => array_sum(array_column($backups, 'size')),
            'total_size_formatted' => $this->formatBytes(array_sum(array_column($backups, 'size'))),
            'last_backup' => $backups[0]['date_human'] ?? 'Nenhum',
            'disk_usage_percent' => $this->getDiskUsagePercent(),
        ];
    }

    /**
     * Retorna caminho para download
     */
    public function getDownloadPath(string $filename): string
    {
        return storage_path('app/'.$this->backupPath.'/'.$filename);
    }

    /**
     * Limpa backups antigos mantendo apenas os N mais recentes
     */
    public function cleanOldBackups(int $keep = 5): int
    {
        $disk = Storage::disk('local');
        $files = collect($disk->files($this->backupPath))
            ->map(fn ($file) => [
                'path' => $file,
                'time' => $disk->lastModified($file),
            ])
            ->sortByDesc('time')
            ->skip($keep)
            ->pluck('path');

        foreach ($files as $file) {
            $disk->delete($file);
        }

        return $files->count();
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

    private function getDiskUsagePercent(): float
    {
        $total = disk_total_space(storage_path());
        $used = array_sum(array_column($this->listBackups(), 'size'));

        return round(($used / $total) * 100, 4);
    }
}
