<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\User;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Criar roles padrão
        $adminRole = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web',
        ]);

        $superAdminRole = Role::firstOrCreate([
            'name' => 'super_admin',
            'guard_name' => 'web',
        ]);

        $consultaRole = Role::firstOrCreate([
            'name' => 'consulta',
            'guard_name' => 'web',
        ]);

        // Verificar se existe o usuário admin e atribuir role super_admin
        $adminUser = User::where('email', config('auth.super_admin_email'))->first();
        if ($adminUser && !$adminUser->hasRole('super_admin')) {
            $adminUser->assignRole('super_admin');
        }

        $this->command->info('Roles criados com sucesso:');
        $this->command->info('- admin: Administrador com permissões de CRUD');
        $this->command->info('- super_admin: Super administrador com todas as permissões');
        $this->command->info('- consulta: Usuário com permissões apenas de visualização');
        
        if ($adminUser) {
            $this->command->info("Usuário " . config('auth.super_admin_email') . " foi definido como super_admin");
        }
    }
}
