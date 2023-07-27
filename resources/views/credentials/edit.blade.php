<x-app-layout>
    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="px-4 sm:px-6 lg:px-8">

                <x-heading title="Editar Credencial" description="Faça alterações na Credencial de Segurança" />

                <div class="mt-6">
                    <form method="POST" action="{{ route('credentials.update', $credential->id) }}">
                        @csrf
                        @method('PUT')

                        <!-- FSCS -->
                        <div class="mt-4">
                            <x-input-label for="fscs" :value="__('FSCS')" />
                            <x-text-input id="fscs" class="block w-full mt-1" type="text" name="fscs"
                                :value="$credential->fscs" required />
                            <x-input-error :messages="$errors->get('fscs')" class="mt-2" />
                        </div>

                        <!-- Nome -->
                        <div class="mt-4">
                            <x-input-label for="name" :value="__('Nome')" />
                            <x-text-input id="name" class="block w-full mt-1" type="text" name="name"
                                :value="$credential->name" required />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <!-- Sigilo -->
                        <div class="mt-4">
                            <x-input-label for="secrecy" :value="__('Sigilo')" />

                            <select id="secrecy" name="secrecy"
                                class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                required>
                                <option value="">Selecione o Grau de Sigilo</option>
                                <option value="R" @if ($credential->secrecy === 'R') selected @endif>Reservado
                                </option>
                                <option value="S" @if ($credential->secrecy === 'S') selected @endif>Secreto
                                </option>
                            </select>

                            <x-input-error :messages="$errors->get('secrecy')" class="mt-2" />
                        </div>


                        <!-- Concessão -->
                        <div class="mt-4">
                            <x-input-label for="concession" :value="__('Concessão')" />
                            <x-text-input id="concession" class="block w-full mt-1" type="text" name="concession"
                                :value="$credential->concession" required />
                            <x-input-error :messages="$errors->get('concession')" class="mt-2" />
                        </div>

                        <!-- Validade -->
                        <div class="mt-4">
                            <x-input-label for="validity" :value="__('Validade')" />
                            <x-text-input id="validity" class="block w-full mt-1" type="text" name="validity"
                                :value="$credential->validity" required />
                            <x-input-error :messages="$errors->get('validity')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <x-primary-button class="ml-4">
                                {{ __('Save') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
