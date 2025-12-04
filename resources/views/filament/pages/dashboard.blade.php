<x-filament-panels::page>
    @if ($this->activeCredential)

        {{-- Cards de Informação Principal --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            {{-- Card: Dados do Militar --}}
            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-user-circle class="w-5 h-5 text-primary-600"/>
                        <span>Dados do Militar</span>
                    </div>
                </x-slot>
                
                <div class="space-y-4">
                    @if ($user->rank)
                        <div class="flex items-start gap-3 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                            <div class="flex-shrink-0 mt-0.5">
                                <x-heroicon-o-user-circle class="w-5 h-5 text-amber-500"/>
                            </div>
                            <div class="flex-1 min-w-0">
                                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Posto/Graduação</dt>
                                <dd class="mt-1 text-base font-bold text-gray-900 dark:text-white">
                                    {{ $user->rank->abbreviation }} - {{ $user->rank->name }}
                                </dd>
                                <dd class="text-xs text-gray-600 dark:text-gray-400">{{ $user->rank->armed_force }}</dd>
                            </div>
                        </div>
                    @endif

                    <div class="flex items-start gap-3 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                        <div class="flex-shrink-0 mt-0.5">
                            <x-heroicon-o-identification class="w-5 h-5 text-blue-500"/>
                        </div>
                        <div class="flex-1 min-w-0">
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Nome Completo</dt>
                            <dd class="mt-1 text-base font-bold text-gray-900 dark:text-white">{{ $user->full_name }}</dd>
                        </div>
                    </div>

                    @if ($user->office)
                        <div class="flex items-start gap-3 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                            <div class="flex-shrink-0 mt-0.5">
                                <x-heroicon-o-building-office class="w-5 h-5 text-green-500"/>
                            </div>
                            <div class="flex-1 min-w-0">
                                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Unidade</dt>
                                <dd class="mt-1 text-base font-bold text-gray-900 dark:text-white">
                                    {{ $user->office->office }}
                                </dd>
                                <dd class="text-xs text-gray-600 dark:text-gray-400">{{ $user->office->description }}</dd>
                            </div>
                        </div>
                    @endif

                    <div class="flex items-start gap-3 p-3 bg-primary-50 dark:bg-primary-900/20 rounded-lg border border-primary-200 dark:border-primary-800">
                        <div class="flex-shrink-0 mt-0.5">
                            <x-heroicon-o-identification class="w-5 h-5 text-primary-600 dark:text-primary-400"/>
                        </div>
                        <div class="flex-1 min-w-0">
                            <dt class="text-xs font-medium text-primary-700 dark:text-primary-300 uppercase tracking-wider">Número da Credencial</dt>
                            <dd class="mt-1 font-mono text-lg font-black text-primary-900 dark:text-primary-100">
                                {{ $this->activeCredential->credential }}
                            </dd>
                        </div>
                    </div>
                </div>
            </x-filament::section>

            {{-- Card: Informações da Credencial --}}
            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-document-text class="w-5 h-5 text-primary-600"/>
                        <span>Informações da Credencial</span>
                    </div>
                </x-slot>
                
                <div class="space-y-4">
                    <div class="flex items-start gap-3 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                        <div class="flex-shrink-0 mt-0.5">
                            <x-heroicon-o-document class="w-5 h-5 text-indigo-500"/>
                        </div>
                        <div class="flex-1 min-w-0">
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tipo</dt>
                            <dd class="mt-1">
                                <x-filament::badge color="info" size="lg">
                                    {{ $this->activeCredential->type->value ?? $this->activeCredential->type }}
                                </x-filament::badge>
                            </dd>
                        </div>
                    </div>

                    @if ($this->activeCredential->concession)
                        <div class="flex items-start gap-3 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                            <div class="flex-shrink-0 mt-0.5">
                                <x-heroicon-o-calendar class="w-5 h-5 text-green-500"/>
                            </div>
                            <div class="flex-1 min-w-0">
                                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Data de Concessão</dt>
                                <dd class="mt-1 text-base font-bold text-gray-900 dark:text-white">
                                    {{ $this->activeCredential->concession->format('d/m/Y') }}
                                </dd>
                            </div>
                        </div>
                    @endif

                    @if ($this->activeCredential->validity)
                        <div class="flex items-start gap-3 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                            <div class="flex-shrink-0 mt-0.5">
                                <x-heroicon-o-calendar-days class="w-5 h-5 text-orange-500"/>
                            </div>
                            <div class="flex-1 min-w-0">
                                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Data de Validade</dt>
                                <dd class="mt-1 text-base font-bold text-gray-900 dark:text-white">
                                    {{ $this->activeCredential->validity->format('d/m/Y') }}
                                </dd>
                            </div>
                        </div>
                    @endif

                    <div class="flex items-start gap-3 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                        <div class="flex-shrink-0 mt-0.5">
                            <x-heroicon-o-lock-closed class="w-5 h-5 text-red-500"/>
                        </div>
                        <div class="flex-1 min-w-0">
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Grau de Sigilo</dt>
                            <dd class="mt-1">
                                @php
                                    $secrecyValue = $this->activeCredential->secrecy->value ?? $this->activeCredential->secrecy;
                                    $secrecyLabel = is_object($this->activeCredential->secrecy) && method_exists($this->activeCredential->secrecy, 'label') 
                                        ? $this->activeCredential->secrecy->label() 
                                        : match($secrecyValue) {
                                            'R' => 'Reservado',
                                            'S' => 'Secreto',
                                            'AR' => 'Acesso Restrito',
                                            default => $secrecyValue
                                        };
                                    $secrecyColor = match($secrecyValue) {
                                        'S' => 'danger',
                                        'R' => 'success',
                                        'AR' => 'info',
                                        default => 'gray'
                                    };
                                @endphp
                                <x-filament::badge :color="$secrecyColor" size="lg">
                                    {{ $secrecyLabel }}
                                </x-filament::badge>
                            </dd>
                        </div>
                    </div>

                    <div class="flex items-start gap-3 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                        <div class="flex-shrink-0 mt-0.5">
                            <x-heroicon-o-check-circle class="w-5 h-5 text-purple-500"/>
                        </div>
                        <div class="flex-1 min-w-0">
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</dt>
                            <dd class="mt-1">
                                @php
                                    $statusColorMap = [
                                        'Ativa' => 'success',
                                        'Pendente' => 'warning',
                                        'Em Processamento' => 'info',
                                        'Vencida' => 'danger',
                                        'Negada' => 'gray',
                                    ];
                                    $statusColor = $statusColorMap[$this->activeCredential->status] ?? 'gray';
                                @endphp
                                <x-filament::badge :color="$statusColor" size="lg">
                                    {{ $this->activeCredential->status }}
                                </x-filament::badge>
                            </dd>
                        </div>
                    </div>

                    <div class="flex items-start gap-3 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                        <div class="flex-shrink-0 mt-0.5">
                            <x-heroicon-o-hashtag class="w-5 h-5 text-cyan-500"/>
                        </div>
                        <div class="flex-1 min-w-0">
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">FSCS</dt>
                            <dd class="mt-1 font-mono text-base font-bold text-gray-900 dark:text-white">
                                {{ $this->activeCredential->fscs }}
                            </dd>
                        </div>
                    </div>
                </div>
            </x-filament::section>
        </div>
    @else
        {{-- Mensagem quando não há credencial --}}
        <div class="flex items-center justify-center min-h-[400px]">
            <x-filament::section class="max-w-2xl">
                <div class="text-center py-12">
                    <div class="flex justify-center">
                        <div class="rounded-full bg-warning-100 dark:bg-warning-900/20 p-4">
                            <x-heroicon-o-exclamation-triangle class="h-12 w-12 text-warning-600 dark:text-warning-400"/>
                        </div>
                    </div>
                    <h3 class="mt-6 text-lg font-semibold text-gray-900 dark:text-white">
                        Nenhuma Credencial Ativa
                    </h3>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400 max-w-md mx-auto">
                        Não foi encontrada uma credencial de segurança ativa para o seu usuário. 
                        Entre em contato com o administrador do sistema para obter mais informações.
                    </p>
                </div>
            </x-filament::section>
        </div>
    @endif
</x-filament-panels::page>
