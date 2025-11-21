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

        // Criar permissions
        $permissions = [
            'view_users',
            'create_users',
            'edit_users',
            'delete_users',
            'view_credentials',
            'create_credentials',
            'edit_credentials',
            'delete_credentials',
            'view_audit_logs',
            'export_reports',
            'manage_permissions',
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
            'view_users',
            'create_users',
            'edit_users',
            'view_credentials',
            'create_credentials',
            'edit_credentials',
            'delete_credentials',
            'view_audit_logs',
            'export_reports',
        ]);

        // Operador - Visualiza e edita credenciais
        $operador = Role::create(['name' => 'operador']);
        $operador->givePermissionTo([
            'view_credentials',
            'create_credentials',
            'edit_credentials',
            'view_audit_logs',
        ]);

        // Consulta - Apenas visualização
        $consulta = Role::create(['name' => 'consulta']);
        $consulta->givePermissionTo([
            'view_credentials',
            'view_audit_logs',
        ]);

        $this->command->info('Roles e permissions criados com sucesso!');
        $this->command->info('- Super Admin: '.$superAdmin->permissions->count().' permissions');
        $this->command->info('- Administrador: '.$admin->permissions->count().' permissions');
        $this->command->info('- Operador: '.$operador->permissions->count().' permissions');
        $this->command->info('- Consulta: '.$consulta->permissions->count().' permissions');
    }
}
