<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Super Admin - Acesso total ao sistema
        $superAdmin = User::create([
            'name' => 'Super Administrador',
            'email' => 'superadmin@credcrud.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $superAdmin->assignRole('super_admin');

        // Admin 1 - Administrador principal
        $admin1 = User::create([
            'name' => 'João Silva',
            'email' => 'admin@credcrud.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $admin1->assignRole('admin');

        // Admin 2 - Administrador secundário
        $admin2 = User::create([
            'name' => 'Maria Santos',
            'email' => 'admin2@credcrud.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $admin2->assignRole('admin');

        // Usuário de Consulta 1
        $consulta1 = User::create([
            'name' => 'Pedro Oliveira',
            'email' => 'consulta@credcrud.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $consulta1->assignRole('consulta');

        // Usuário de Consulta 2
        $consulta2 = User::create([
            'name' => 'Ana Costa',
            'email' => 'consulta2@credcrud.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $consulta2->assignRole('consulta');

        // Usuários comuns (5 usuários sem roles específicas)
        User::factory()->count(5)->create();

        $this->command->info('✅ Usuários criados com sucesso!');
        $this->command->info('📊 Total de usuários: '.User::count());
        $this->command->info('👑 Super Admins: '.User::role('super_admin')->count());
        $this->command->info('🛡️  Admins: '.User::role('admin')->count());
        $this->command->info('👀 Consulta: '.User::role('consulta')->count());
    }
}
