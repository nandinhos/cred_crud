<x-filament-panels::page>
    @if ($this->activeCredential)
        {{-- Informações do Militar --}}
        <x-filament::section>
            <x-slot name="heading">
                Dados do Militar
            </x-slot>
            
            <dl class="grid gap-6 sm:grid-cols-2">
                @if ($user->rank)
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Posto/Graduação</dt>
                        <dd class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">
                            {{ $user->rank->abbreviation }} - {{ $user->rank->name }}
                            <div class="text-xs text-gray-500">{{ $user->rank->armed_force }}</div>
                        </dd>
                    </div>
                @endif

                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Nome Completo</dt>
                    <dd class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ $user->full_name }}</dd>
                </div>

                @if ($user->office)
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Unidade</dt>
                        <dd class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">
                            {{ $user->office->office }}
                            <div class="text-xs text-gray-500">{{ $user->office->description }}</div>
                        </dd>
                    </div>
                @endif

                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Número da Credencial</dt>
                    <dd class="mt-1 font-mono text-sm font-bold text-gray-900 dark:text-white">
                        {{ $this->activeCredential->credential }}
                    </dd>
                </div>
            </dl>
        </x-filament::section>

        {{-- Datas e Sigilo --}}
        <x-filament::section>
            <x-slot name="heading">
                Informações da Credencial
            </x-slot>
            
            <dl class="grid gap-6 sm:grid-cols-3">
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Tipo</dt>
                    <dd class="mt-1">
                        <x-filament::badge color="info">
                            {{ $this->activeCredential->type->value ?? $this->activeCredential->type }}
                        </x-filament::badge>
                    </dd>
                </div>

                @if ($this->activeCredential->concession)
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Data de Concessão</dt>
                        <dd class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">
                            {{ $this->activeCredential->concession->format('d/m/Y') }}
                        </dd>
                    </div>
                @endif

                @if ($this->activeCredential->validity)
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Data de Validade</dt>
                        <dd class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">
                            {{ $this->activeCredential->validity->format('d/m/Y') }}
                        </dd>
                    </div>
                @endif

                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Grau de Sigilo</dt>
                    <dd class="mt-1">
                        @php
                            $secrecyValue = $this->activeCredential->secrecy->value ?? $this->activeCredential->secrecy;
                            $secrecyLabel = is_object($this->activeCredential->secrecy) && method_exists($this->activeCredential->secrecy, 'label') 
                                ? $this->activeCredential->secrecy->label() 
                                : ($secrecyValue === 'R' ? 'Reservado' : 'Secreto');
                            $secrecyColor = $secrecyValue === 'R' ? 'success' : 'danger';
                        @endphp
                        <x-filament::badge :color="$secrecyColor">
                            {{ $secrecyLabel }}
                        </x-filament::badge>
                    </dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</dt>
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
                        <x-filament::badge :color="$statusColor">
                            {{ $this->activeCredential->status }}
                        </x-filament::badge>
                    </dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">FSCS</dt>
                    <dd class="mt-1 font-mono text-sm font-bold text-gray-900 dark:text-white">
                        {{ $this->activeCredential->fscs }}
                    </dd>
                </div>
            </dl>
        </x-filament::section>
    @else
        {{-- Mensagem quando não há credencial --}}
        <x-filament::section>
            <x-slot name="heading">
                Nenhuma Credencial Ativa
            </x-slot>
            
            <div class="text-sm text-gray-500 dark:text-gray-400">
                Não foi encontrada uma credencial de segurança ativa para o seu usuário. 
                Entre em contato com o administrador do sistema para obter mais informações.
            </div>
        </x-filament::section>
    @endif
</x-filament-panels::page>
