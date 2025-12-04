<?php

namespace App\Filament\Pages;

use App\Models\User;
use Filament\Pages\Page;

class Dashboard extends Page
{
    protected string $view = 'filament.pages.dashboard';

    public User $user;

    public function mount(): void
    {
        // Carrega o usuário autenticado com suas relações
        $this->user = auth()->user()->load(['rank', 'office', 'credentials']);
    }

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-home';
    }

    public static function getNavigationSort(): ?int
    {
        return -2;
    }

    public static function getNavigationLabel(): string
    {
        return 'Dashboard';
    }

    public function getTitle(): string
    {
        return 'Dashboard';
    }

    public function getHeading(): string
    {
        return 'Minha Credencial';
    }

    /**
     * Retorna a credencial mais recente/ativa do usuário
     */
    public function getActiveCredentialProperty()
    {
        return $this->user->credentials()
            ->whereNotNull('concession')
            ->orderBy('concession', 'desc')
            ->first();
    }
}
