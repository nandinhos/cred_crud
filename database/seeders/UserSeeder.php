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
        $this->command->info('👥 Criando 50 usuários...');

        // Pegar ranks e offices
        $primeiroSargento = Rank::where('abbreviation', '1º Sgt')->where('armed_force', 'Exército')->first();
        $tenente = Rank::where('abbreviation', 'Ten')->where('armed_force', 'Exército')->first();
        $capitao = Rank::where('abbreviation', 'Cap')->where('armed_force', 'Exército')->first();
        $major = Rank::where('abbreviation', 'Maj')->where('armed_force', 'Exército')->first();
        $coronel = Rank::where('abbreviation', 'Cel')->where('armed_force', 'Exército')->first();

        $offices = Office::all();
        $allRanks = Rank::all();

        // 1 Super Admin: 1S FERNANDO
        $superAdmin = User::create([
            'name' => 'Fernando',
            'full_name' => '1S Fernando Silva',
            'rank_id' => $primeiroSargento?->id,
            'office_id' => $offices->random()->id,
            'email' => 'fernando@credcrud.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $superAdmin->assignRole('super_admin');
        $this->command->info('  👑 Super Admin: 1S Fernando');

        // 2 Admins: TEN FRANCO, 1S MOISES
        $admin1 = User::create([
            'name' => 'Franco',
            'full_name' => 'Ten Franco Oliveira',
            'rank_id' => $tenente?->id,
            'office_id' => $offices->random()->id,
            'email' => 'franco@credcrud.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $admin1->assignRole('admin');

        $admin2 = User::create([
            'name' => 'Moises',
            'full_name' => '1S Moises Santos',
            'rank_id' => $primeiroSargento?->id,
            'office_id' => $offices->random()->id,
            'email' => 'moises@credcrud.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $admin2->assignRole('admin');
        $this->command->info('  🛡️  Admins: Ten Franco, 1S Moises');

        // 47 Usuários de Consulta
        $nomes = [
            'Pedro', 'Ana', 'Carlos', 'Beatriz', 'Rafael', 'Juliana', 'Lucas', 'Mariana',
            'Gabriel', 'Fernanda', 'Thiago', 'Camila', 'Bruno', 'Larissa', 'Diego', 'Patricia',
            'Rodrigo', 'Amanda', 'Felipe', 'Bruna', 'Gustavo', 'Renata', 'Leonardo', 'Carla',
            'Marcelo', 'Daniela', 'Anderson', 'Tatiana', 'Ricardo', 'Vanessa', 'Paulo', 'Simone',
            'Fabio', 'Cristina', 'Vinicius', 'Adriana', 'Alexandre', 'Monica', 'Roberto', 'Sandra',
            'Sergio', 'Claudia', 'Marcos', 'Luciana', 'Antonio', 'Silvia', 'Jose'
        ];

        $sobrenomes = [
            'Silva', 'Santos', 'Oliveira', 'Souza', 'Lima', 'Pereira', 'Costa', 'Rodrigues',
            'Almeida', 'Nascimento', 'Araujo', 'Ribeiro', 'Carvalho', 'Gomes', 'Martins', 'Rocha',
            'Fernandes', 'Barbosa', 'Dias', 'Monteiro', 'Cardoso', 'Teixeira', 'Cavalcanti', 'Ramos'
        ];

        for ($i = 0; $i < 47; $i++) {
            $nome = $nomes[$i % count($nomes)];
            $sobrenome = $sobrenomes[$i % count($sobrenomes)];
            $rank = $allRanks->random();
            
            $user = User::create([
                'name' => $nome,
                'full_name' => $rank->abbreviation . ' ' . $nome . ' ' . $sobrenome,
                'rank_id' => $rank->id,
                'office_id' => $offices->random()->id,
                'email' => strtolower($nome . $i) . '@credcrud.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]);
            $user->assignRole('consulta');
        }

        $this->command->info('  👀 Consulta: 47 usuários');
        $this->command->info('');
        $this->command->info('✅ Total de usuários criados: ' . User::count());
        $this->command->info('👑 Super Admins: ' . User::role('super_admin')->count());
        $this->command->info('🛡️  Admins: ' . User::role('admin')->count());
        $this->command->info('👀 Consulta: ' . User::role('consulta')->count());
    }
}
