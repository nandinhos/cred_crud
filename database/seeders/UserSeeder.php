<?php

namespace Database\Seeders;

use App\Models\Office;
use App\Models\Rank;
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
        // Pegar alguns ranks e offices para os usuários fixos
        $generalEx = Rank::where('abbreviation', 'Gen Ex')->first();
        $coronel = Rank::where('abbreviation', 'Cel')->where('armed_force', 'Exército')->first();
        $capitao = Rank::where('abbreviation', 'Cap')->where('armed_force', 'Exército')->first();
        $tenente = Rank::where('abbreviation', '1º Ten')->where('armed_force', 'Exército')->first();
        $sargento = Rank::where('abbreviation', '1º Sgt')->where('armed_force', 'Exército')->first();

        $gacPac = Office::where('office', 'GAC-PAC')->first();
        $scpEmb = Office::where('office', 'SCP-EMB')->first();
        $ecpGpx = Office::where('office', 'ECP-GPX')->first();
        $ecpIja = Office::where('office', 'ECP-IJA')->first();
        $ecpPoa = Office::where('office', 'ECP-POA')->first();

        // Super Admin - Acesso total ao sistema
        $superAdmin = User::create([
            'name' => 'Admin',
            'full_name' => 'Super Administrador do Sistema',
            'rank_id' => $generalEx?->id,
            'office_id' => $gacPac?->id,
            'email' => 'superadmin@credcrud.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $superAdmin->assignRole('super_admin');

        // Admin 1 - Administrador principal
        $admin1 = User::create([
            'name' => 'João',
            'full_name' => 'João Silva Santos',
            'rank_id' => $coronel?->id,
            'office_id' => $scpEmb?->id,
            'email' => 'admin@credcrud.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $admin1->assignRole('admin');

        // Admin 2 - Administrador secundário
        $admin2 = User::create([
            'name' => 'Maria',
            'full_name' => 'Maria Santos Oliveira',
            'rank_id' => $capitao?->id,
            'office_id' => $ecpGpx?->id,
            'email' => 'admin2@credcrud.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $admin2->assignRole('admin');

        // Usuário de Consulta 1
        $consulta1 = User::create([
            'name' => 'Pedro',
            'full_name' => 'Pedro Oliveira Costa',
            'rank_id' => $tenente?->id,
            'office_id' => $ecpIja?->id,
            'email' => 'consulta@credcrud.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $consulta1->assignRole('consulta');

        // Usuário de Consulta 2
        $consulta2 = User::create([
            'name' => 'Ana',
            'full_name' => 'Ana Costa Pereira',
            'rank_id' => $sargento?->id,
            'office_id' => $ecpPoa?->id,
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
