<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Criar permissions (em português e resumidas)
        $permissions = [
            'Visualizar Usuários',
            'Criar Usuários',
            'Editar Usuários',
            'Excluir Usuários',
            'Visualizar Credenciais',
            'Criar Credenciais',
            'Editar Credenciais',
            'Excluir Credenciais',
            'Visualizar Logs',
            'Exportar Relatórios',
            'Gerenciar Permissões',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Criar roles e atribuir permissions

        // Super Admin - Acesso total ao sistema
        $superAdmin = Role::create(['name' => 'super_admin']);
        $superAdmin->givePermissionTo(Permission::all());

        // Administrador - Gerencia usuários e credenciais
        $admin = Role::create(['name' => 'admin']);
        $admin->givePermissionTo([
            'Visualizar Usuários',
            'Criar Usuários',
            'Editar Usuários',
            'Visualizar Credenciais',
            'Criar Credenciais',
            'Editar Credenciais',
            'Excluir Credenciais',
            'Visualizar Logs',
            'Exportar Relatórios',
        ]);

        // Operador - Visualiza e edita credenciais
        $operador = Role::create(['name' => 'operador']);
        $operador->givePermissionTo([
            'Visualizar Credenciais',
            'Criar Credenciais',
            'Editar Credenciais',
            'Visualizar Logs',
        ]);

        // Consulta - Apenas visualização
        $consulta = Role::create(['name' => 'consulta']);
        $consulta->givePermissionTo([
            'Visualizar Credenciais',
            'Visualizar Logs',
        ]);

        $this->command->info('Roles e permissions criados com sucesso!');
        $this->command->info('- Super Admin: '.$superAdmin->permissions->count().' permissions');
        $this->command->info('- Administrador: '.$admin->permissions->count().' permissions');
        $this->command->info('- Operador: '.$operador->permissions->count().' permissions');
        $this->command->info('- Consulta: '.$consulta->permissions->count().' permissions');
    }
}
