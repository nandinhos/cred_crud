<!-- resources/views/components/sidebar-items.blade.php -->
@php
$currentRoute = request()->route()->getName();
@endphp

<nav class="flex-1 px-2 py-4 space-y-1">
    <a href="{{ route('credentials.index') }}" class="flex items-center px-2 py-2 text-sm font-medium rounded-md group hover:bg-gray-700 hover:text-white group {{ $currentRoute === 'credentials.index' ? 'bg-gray-900 text-white' : 'text-gray-300' }}">
        <svg class="flex-shrink-0 w-6 h-6 mr-3 text-gray-400 group-hover:text-gray-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
            <!-- ... Ícone do link ... -->
        </svg>
        Controle de Credenciais
    </a>

    <a href="{{ route('dashboard') }}" class="flex items-center px-2 py-2 text-sm font-medium rounded-md group  hover:bg-gray-700 hover:text-white group {{ $currentRoute === 'dashboard' ? 'bg-gray-900 text-white' : 'text-gray-300' }}">
        <svg class="flex-shrink-0 w-6 h-6 mr-3 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
            <!-- ... Ícone do link ... -->
        </svg>
        Painel de Controle
    </a>

    <!-- Adicione outros links e verificação de rota ativa, conforme necessário ... -->

    <a href="#" class="flex items-center px-2 py-2 text-sm font-medium rounded-md group hover:bg-gray-700 hover:text-white group {{ $currentRoute === 'outra.rota' ? 'bg-gray-900 text-white' : 'text-gray-300' }}">
        <svg class="flex-shrink-0 w-6 h-6 mr-3 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
            <!-- ... Ícone do link ... -->
        </svg>
        Outra Rota
    </a>
</nav>
