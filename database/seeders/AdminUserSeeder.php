<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Criar permissões para credenciais
        $permissions = [
            'view_credential',
            'view_any_credential',
            'create_credential',
            'update_credential',
            'restore_credential',
            'restore_any_credential',
            'replicate_credential',
            'reorder_credential',
            'delete_credential',
            'delete_any_credential',
            'force_delete_credential',
            'force_delete_any_credential',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Criar role super_admin
        $role = Role::firstOrCreate(['name' => 'super_admin']);

        // Atribuir todas as permissões ao super_admin
        $role->givePermissionTo(Permission::all());

        // Criar usuário admin
        $user = User::firstOrCreate(
            ['email' => config('auth.super_admin_email')],
            [
                'name' => 'Administrator',
                'full_name' => 'Administrator',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        // Atribuir role super_admin ao usuário
        $user->assignRole('super_admin');

        $this->command->info('Usuário admin criado com sucesso!');
        $this->command->info('Email: '.config('auth.super_admin_email'));
        $this->command->info('Senha: password');
    }
}
