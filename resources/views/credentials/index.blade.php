<x-app-layout>
    <div class="py-2">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="px-4 sm:px-6 lg:px-8">

                <x-heading 
                title="Credenciais" 
                description="Listagem de Credenciais de Segurança do efetivo do GAC-PAC"
                btn-label="Cadastrar" 
                :route="route('credentials.create')" />

                <div class="w-full overflow-hidden md:rounded-lg">
                    <livewire:table resource="Credential" :columns="[
                        ['label' => 'ID', 'column' => 'id'],
                        ['label' => 'FSCS', 'column' => 'fscs'],
                        ['label' => 'NOME', 'column' => 'name'],
                        ['label' => 'SIGILO', 'column' => 'secrecy'],
                        ['label' => 'CONCESSÃO', 'column' => 'concession'],
                        ['label' => 'VALIDADE', 'column' => 'validity'],
                    ]" edit="credentials.edit"
                        delete="credentials.destroy"></livewire:table>
                </div>

            </div>
        </div>

        <script>
    Livewire.on('openEditModal', function (id) {
        Swal.fire({
            title: 'Edição',
            html: `<form wire:submit.prevent="update(${id})">
                        <label for="name">Nome:</label>
                        <input type="text" id="name" wire:model="name">
                        <input type="submit" value="Salvar">
                    </form>`,
            showCancelButton: true,
            cancelButtonText: 'Cancelar',
            showConfirmButton: false,
            allowOutsideClick: false,
        })
    });
</script>
        
</x-app-layout>
