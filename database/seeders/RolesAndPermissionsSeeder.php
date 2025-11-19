<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

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
            'manage_permissions'
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Criar roles e atribuir permissions
        
        // Super Admin - Acesso total ao sistema
        $superAdmin = Role::create(['name' => 'Super Admin']);
        $superAdmin->givePermissionTo(Permission::all());

        // Administrador - Gerencia usuários e credenciais
        $admin = Role::create(['name' => 'Administrador']);
        $admin->givePermissionTo([
            'view_users',
            'create_users', 
            'edit_users',
            'view_credentials',
            'create_credentials',
            'edit_credentials',
            'delete_credentials',
            'view_audit_logs',
            'export_reports'
        ]);

        // Operador - Visualiza e edita credenciais
        $operador = Role::create(['name' => 'Operador']);
        $operador->givePermissionTo([
            'view_credentials',
            'create_credentials',
            'edit_credentials',
            'view_audit_logs'
        ]);

        // Consulta - Apenas visualização
        $consulta = Role::create(['name' => 'Consulta']);
        $consulta->givePermissionTo([
            'view_credentials',
            'view_audit_logs'
        ]);

        $this->command->info('Roles e permissions criados com sucesso!');
        $this->command->info('- Super Admin: ' . $superAdmin->permissions->count() . ' permissions');
        $this->command->info('- Administrador: ' . $admin->permissions->count() . ' permissions'); 
        $this->command->info('- Operador: ' . $operador->permissions->count() . ' permissions');
        $this->command->info('- Consulta: ' . $consulta->permissions->count() . ' permissions');
    }
}
