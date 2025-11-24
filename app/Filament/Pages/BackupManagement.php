<?php

namespace App\Filament\Pages;

use App\Services\BackupService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class BackupManagement extends Page
{
    protected string $view = 'filament.pages.backup-management';

    public array $backups = [];

    public array $statistics = [];

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-circle-stack';
    }

    public static function getNavigationLabel(): string
    {
        return 'Backups';
    }

    public function getTitle(): string
    {
        return 'Gerenciamento de Backups';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Sistema';
    }

    public static function getNavigationSort(): ?int
    {
        return 99;
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user && ($user->hasRole('super_admin') || $user->hasRole('admin'));
    }

    public function mount(): void
    {
        $this->loadData();
    }

    public function loadData(): void
    {
        $service = new BackupService;
        $this->backups = $service->listBackups();
        $this->statistics = $service->getStatistics();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('createBackup')
                ->label('Criar Novo Backup')
                ->icon('heroicon-o-plus-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Criar Novo Backup')
                ->modalDescription('Tem certeza que deseja criar um novo backup do banco de dados? Isso pode levar alguns segundos.')
                ->action(function () {
                    $service = new BackupService;
                    $result = $service->createBackup();

                    if ($result['success']) {
                        Notification::make()
                            ->title('Backup criado com sucesso!')
                            ->success()
                            ->send();

                        $this->loadData();
                    } else {
                        Notification::make()
                            ->title('Erro ao criar backup')
                            ->body($result['error'])
                            ->danger()
                            ->send();
                    }
                }),

            Action::make('cleanOld')
                ->label('Limpar Antigos')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Limpar Backups Antigos')
                ->modalDescription('Serão mantidos apenas os 5 backups mais recentes. Esta ação não pode ser desfeita.')
                ->action(function () {
                    $service = new BackupService;
                    $removed = $service->cleanOldBackups(5);

                    Notification::make()
                        ->title('Backups antigos removidos')
                        ->body("{$removed} backup(s) foram removidos com sucesso.")
                        ->success()
                        ->send();

                    $this->loadData();
                }),
        ];
    }

    public string $backupToDelete = '';

    public function deleteBackupAction(): Action
    {
        return Action::make('deleteBackup')
            ->label('Deletar Backup')
            ->icon('heroicon-o-trash')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Deletar Backup')
            ->modalDescription(fn () => "Tem certeza que deseja deletar o backup '{$this->backupToDelete}'? Esta ação não pode ser desfeita.")
            ->modalSubmitActionLabel('Sim, deletar')
            ->modalCancelActionLabel('Cancelar')
            ->action(function () {
                $service = new BackupService;

                if ($service->deleteBackup($this->backupToDelete)) {
                    Notification::make()
                        ->title('Backup deletado com sucesso!')
                        ->success()
                        ->send();

                    $this->loadData();
                } else {
                    Notification::make()
                        ->title('Erro ao deletar backup')
                        ->danger()
                        ->send();
                }
            });
    }

    public function setBackupToDelete(string $filename): void
    {
        $this->backupToDelete = $filename;
    }
}
