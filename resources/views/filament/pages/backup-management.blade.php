<x-filament-panels::page>
    {{-- Estatísticas --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        {{-- Total de Backups --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 dark:bg-blue-900">
                    <x-heroicon-o-circle-stack class="w-8 h-8 text-blue-600 dark:text-blue-400"/>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Total de Backups</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $statistics['total'] }}</p>
                </div>
            </div>
        </div>
        
        {{-- Último Backup --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 dark:bg-green-900">
                    <x-heroicon-o-clock class="w-8 h-8 text-green-600 dark:text-green-400"/>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Último Backup</p>
                    <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $statistics['last_backup'] }}</p>
                </div>
            </div>
        </div>
        
        {{-- Tamanho Total --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 dark:bg-purple-900">
                    <x-heroicon-o-server class="w-8 h-8 text-purple-600 dark:text-purple-400"/>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Tamanho Total</p>
                    <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $statistics['total_size_formatted'] }}</p>
                </div>
            </div>
        </div>
        
        {{-- Espaço em Disco --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-orange-100 dark:bg-orange-900">
                    <x-heroicon-o-chart-pie class="w-8 h-8 text-orange-600 dark:text-orange-400"/>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Uso do Disco</p>
                    <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $statistics['disk_usage_percent'] }}%</p>
                </div>
            </div>
        </div>
    </div>
    
    {{-- Tabela de Backups --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                Backups Disponíveis (05 mais recentes)
            </h3>
            
            @if(count($backups) > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Nome do Arquivo
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Data
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Tamanho
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Ações
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($backups as $backup)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $backup['name'] }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        <div>{{ $backup['date_formatted'] }}</div>
                                        <div class="text-xs text-gray-400">{{ $backup['date_human'] }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $backup['size_formatted'] }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="{{ route('backup.download', ['filename' => $backup['name']]) }}" 
                                           class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 mr-3"
                                           title="Download">
                                            <x-heroicon-o-arrow-down-tray class="w-5 h-5 inline"/>
                                        </a>
                                        <button wire:click="deleteBackup('{{ $backup['name'] }}')"
                                                wire:confirm="Tem certeza que deseja deletar este backup?"
                                                class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                                                title="Deletar">
                                            <x-heroicon-o-trash class="w-5 h-5 inline"/>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-12">
                    <x-heroicon-o-circle-stack class="mx-auto h-12 w-12 text-gray-400"/>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Nenhum backup encontrado</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Comece criando seu primeiro backup do banco de dados.
                    </p>
                </div>
            @endif
        </div>
    </div>
</x-filament-panels::page>
