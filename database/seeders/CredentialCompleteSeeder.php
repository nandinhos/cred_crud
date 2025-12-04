<?php

namespace Database\Seeders;

use App\Enums\CredentialSecrecy;
use App\Enums\CredentialType;
use App\Models\Credential;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class CredentialCompleteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Este seeder cria 70 credenciais cobrindo todos os cenários possíveis de status:
     * - Negadas (FSCS = "00000")
     * - Vencidas (validity < hoje)
     * - TCMS Válidas (documento de sigilo sem FSCS)
     * - Em Processamento (TCMS com FSCS)
     * - Pendentes (CRED com FSCS mas sem concessão)
     * - Válidas (CRED com FSCS e concessão)
     * - Pane (casos que não se encaixam nas regras)
     */
    public function run(): void
    {
        $this->command->info('🔐 Criando 70 credenciais com todos os cenários possíveis...');
        $this->command->newLine();

        // Buscar usuários sem credenciais
        $users = User::doesntHave('credentials')->get();

        if ($users->count() < 70) {
            $this->command->error('⚠️  São necessários pelo menos 70 usuários sem credenciais!');
            $this->command->info('   Usuários disponíveis: '.$users->count());

            return;
        }

        $userIndex = 0;
        $credentialNumber = 1000; // Contador para números únicos de credenciais

        // ==========================================
        // GRUPO 1: NEGADAS (10 registros)
        // ==========================================
        $this->command->info('📛 Grupo 1: Credenciais NEGADAS (fscs = "00000")');

        // 5x CRED Reservado Negada
        for ($i = 0; $i < 5; $i++) {
            Credential::create([
                'user_id' => $users[$userIndex++]->id,
                'fscs' => '00000',
                'type' => CredentialType::CRED,
                'secrecy' => CredentialSecrecy::RESERVADO,
                'credential' => 'CRED-'.str_pad($credentialNumber++, 6, '0', STR_PAD_LEFT),
                'observation' => 'Credencial negada pelo Centro de Inteligência',
                'concession' => null,
                'validity' => null,
            ]);
        }

        // 5x CRED Secreto Negada
        for ($i = 0; $i < 5; $i++) {
            Credential::create([
                'user_id' => $users[$userIndex++]->id,
                'fscs' => '00000',
                'type' => CredentialType::CRED,
                'secrecy' => CredentialSecrecy::SECRETO,
                'credential' => 'CRED-'.str_pad($credentialNumber++, 6, '0', STR_PAD_LEFT),
                'observation' => 'Credencial negada pelo Centro de Inteligência',
                'concession' => null,
                'validity' => null,
            ]);
        }

        $this->command->info('   ✓ 10 credenciais negadas criadas');

        // ==========================================
        // GRUPO 2: VENCIDAS (10 registros)
        // ==========================================
        $this->command->info('⏰ Grupo 2: Credenciais VENCIDAS (validity < hoje)');

        // 5x CRED Reservado Vencida (concessão há 3 anos)
        for ($i = 0; $i < 5; $i++) {
            $concession = Carbon::now()->subYears(3)->subMonths($i);
            Credential::create([
                'user_id' => $users[$userIndex++]->id,
                'fscs' => str_pad(rand(10000, 99999), 5, '0', STR_PAD_LEFT),
                'type' => CredentialType::CRED,
                'secrecy' => CredentialSecrecy::RESERVADO,
                'credential' => 'CRED-'.str_pad($credentialNumber++, 6, '0', STR_PAD_LEFT),
                'observation' => 'Credencial vencida há '.($i + 1).' ano(s)',
                'concession' => $concession,
                'validity' => $concession->copy()->addYears(2), // Vencida há 1 ano
            ]);
        }

        // 5x CRED Secreto Vencida
        for ($i = 0; $i < 5; $i++) {
            $concession = Carbon::now()->subYears(3)->subMonths($i + 6);
            Credential::create([
                'user_id' => $users[$userIndex++]->id,
                'fscs' => str_pad(rand(10000, 99999), 5, '0', STR_PAD_LEFT),
                'type' => CredentialType::CRED,
                'secrecy' => CredentialSecrecy::SECRETO,
                'credential' => 'CRED-'.str_pad($credentialNumber++, 6, '0', STR_PAD_LEFT),
                'observation' => 'Credencial vencida',
                'concession' => $concession,
                'validity' => $concession->copy()->addYears(2),
            ]);
        }

        $this->command->info('   ✓ 10 credenciais vencidas criadas');

        // ==========================================
        // GRUPO 3: TCMS VÁLIDAS - Documento de Sigilo (10 registros)
        // ==========================================
        $this->command->info('📄 Grupo 3: TCMS VÁLIDAS (documento de sigilo sem FSCS)');

        for ($i = 0; $i < 10; $i++) {
            Credential::create([
                'user_id' => $users[$userIndex++]->id,
                'fscs' => null, // Sem FSCS = documento de sigilo
                'type' => CredentialType::TCMS,
                'secrecy' => CredentialSecrecy::ACESSO_RESTRITO,
                'credential' => 'TCMS-'.str_pad($credentialNumber++, 6, '0', STR_PAD_LEFT),
                'observation' => 'Documento de sigilo - Acesso Restrito',
                'concession' => Carbon::now()->subMonths($i),
                'validity' => Carbon::createFromDate(Carbon::now()->year, 12, 31),
            ]);
        }

        $this->command->info('   ✓ 10 TCMS válidas (doc. sigilo) criadas');

        // ==========================================
        // GRUPO 4: TCMS EM PROCESSAMENTO (5 registros)
        // ==========================================
        $this->command->info('⏳ Grupo 4: TCMS EM PROCESSAMENTO (com FSCS e COM concessão)');

        // 5x TCMS Em Processamento COM concessão
        for ($i = 0; $i < 5; $i++) {
            Credential::create([
                'user_id' => $users[$userIndex++]->id,
                'fscs' => str_pad(rand(10000, 99999), 5, '0', STR_PAD_LEFT),
                'type' => CredentialType::TCMS,
                'secrecy' => CredentialSecrecy::ACESSO_RESTRITO,
                'credential' => 'TCMS-'.str_pad($credentialNumber++, 6, '0', STR_PAD_LEFT),
                'observation' => 'TCMS em processamento - termo já concedido',
                'concession' => Carbon::now()->subDays(rand(1, 30)),
                'validity' => Carbon::createFromDate(Carbon::now()->year, 12, 31),
            ]);
        }

        $this->command->info('   ✓ 5 TCMS em processamento criadas');

        // ==========================================
        // GRUPO 5: CRED PENDENTES (10 registros)
        // ==========================================
        $this->command->info('⏸️  Grupo 5: CRED PENDENTES (com FSCS mas sem concessão)');

        // 5x Reservado Pendente
        for ($i = 0; $i < 5; $i++) {
            Credential::create([
                'user_id' => $users[$userIndex++]->id,
                'fscs' => str_pad(rand(10000, 99999), 5, '0', STR_PAD_LEFT),
                'type' => CredentialType::CRED,
                'secrecy' => CredentialSecrecy::RESERVADO,
                'credential' => 'CRED-'.str_pad($credentialNumber++, 6, '0', STR_PAD_LEFT),
                'observation' => 'Aguardando data de concessão',
                'concession' => null,
                'validity' => null,
            ]);
        }

        // 5x Secreto Pendente
        for ($i = 0; $i < 5; $i++) {
            Credential::create([
                'user_id' => $users[$userIndex++]->id,
                'fscs' => str_pad(rand(10000, 99999), 5, '0', STR_PAD_LEFT),
                'type' => CredentialType::CRED,
                'secrecy' => CredentialSecrecy::SECRETO,
                'credential' => 'CRED-'.str_pad($credentialNumber++, 6, '0', STR_PAD_LEFT),
                'observation' => 'Aguardando data de concessão',
                'concession' => null,
                'validity' => null,
            ]);
        }

        $this->command->info('   ✓ 10 CRED pendentes criadas');

        // ==========================================
        // GRUPO 6: CRED VÁLIDAS com gradiente de vencimento (15 registros)
        // ==========================================
        $this->command->info('✅ Grupo 6: CRED VÁLIDAS (com diferentes proximidades de vencimento)');

        // 3x Vence em 1-15 dias (Crítica - laranja/vermelho forte)
        for ($i = 0; $i < 3; $i++) {
            $concession = Carbon::now()->subYears(2)->addDays(rand(1, 15));
            Credential::create([
                'user_id' => $users[$userIndex++]->id,
                'fscs' => str_pad(rand(10000, 99999), 5, '0', STR_PAD_LEFT),
                'type' => CredentialType::CRED,
                'secrecy' => CredentialSecrecy::RESERVADO,
                'credential' => 'CRED-'.str_pad($credentialNumber++, 6, '0', STR_PAD_LEFT),
                'observation' => 'Credencial crítica - vence em poucos dias',
                'concession' => $concession,
                'validity' => $concession->copy()->addYears(2),
            ]);
        }

        // 3x Vence em 16-30 dias (Atenção - laranja médio)
        for ($i = 0; $i < 3; $i++) {
            $concession = Carbon::now()->subYears(2)->addDays(rand(16, 30));
            Credential::create([
                'user_id' => $users[$userIndex++]->id,
                'fscs' => str_pad(rand(10000, 99999), 5, '0', STR_PAD_LEFT),
                'type' => CredentialType::CRED,
                'secrecy' => CredentialSecrecy::SECRETO,
                'credential' => 'CRED-'.str_pad($credentialNumber++, 6, '0', STR_PAD_LEFT),
                'observation' => 'Atenção - vence em menos de 1 mês',
                'concession' => $concession,
                'validity' => $concession->copy()->addYears(2),
            ]);
        }

        // 3x Vence em 31-45 dias (Alerta - amarelo forte)
        for ($i = 0; $i < 3; $i++) {
            $concession = Carbon::now()->subYears(2)->addDays(rand(31, 45));
            Credential::create([
                'user_id' => $users[$userIndex++]->id,
                'fscs' => str_pad(rand(10000, 99999), 5, '0', STR_PAD_LEFT),
                'type' => CredentialType::CRED,
                'secrecy' => CredentialSecrecy::RESERVADO,
                'credential' => 'CRED-'.str_pad($credentialNumber++, 6, '0', STR_PAD_LEFT),
                'observation' => 'Alerta - vence em 1-2 meses',
                'concession' => $concession,
                'validity' => $concession->copy()->addYears(2),
            ]);
        }

        // 3x Vence em 46-60 dias (Início gradiente - amarelo médio)
        for ($i = 0; $i < 3; $i++) {
            $concession = Carbon::now()->subYears(2)->addDays(rand(46, 60));
            Credential::create([
                'user_id' => $users[$userIndex++]->id,
                'fscs' => str_pad(rand(10000, 99999), 5, '0', STR_PAD_LEFT),
                'type' => CredentialType::CRED,
                'secrecy' => CredentialSecrecy::SECRETO,
                'credential' => 'CRED-'.str_pad($credentialNumber++, 6, '0', STR_PAD_LEFT),
                'observation' => 'Vence em aproximadamente 2 meses',
                'concession' => $concession,
                'validity' => $concession->copy()->addYears(2),
            ]);
        }

        // 3x Vence em mais de 60 dias (Normal - sem cor especial)
        for ($i = 0; $i < 3; $i++) {
            $concession = Carbon::now()->subMonths(rand(1, 18));
            Credential::create([
                'user_id' => $users[$userIndex++]->id,
                'fscs' => str_pad(rand(10000, 99999), 5, '0', STR_PAD_LEFT),
                'type' => CredentialType::CRED,
                'secrecy' => CredentialSecrecy::RESERVADO,
                'credential' => 'CRED-'.str_pad($credentialNumber++, 6, '0', STR_PAD_LEFT),
                'observation' => 'Credencial válida - longe do vencimento',
                'concession' => $concession,
                'validity' => $concession->copy()->addYears(2),
            ]);
        }

        $this->command->info('   ✓ 15 CRED válidas com diferentes vencimentos criadas');

        // ==========================================
        // GRUPO 7: CASOS EDGE / PANE (10 registros)
        // ==========================================
        $this->command->info('🚨 Grupo 7: CASOS EDGE (Pane - Verificar)');

        // 2x TCMS sem FSCS e sem "TCMS" no credential
        for ($i = 0; $i < 2; $i++) {
            Credential::create([
                'user_id' => $users[$userIndex++]->id,
                'fscs' => null,
                'type' => CredentialType::TCMS,
                'secrecy' => CredentialSecrecy::ACESSO_RESTRITO,
                'credential' => 'DOC-'.str_pad($credentialNumber++, 6, '0', STR_PAD_LEFT), // Sem "TCMS"
                'observation' => 'PANE: TCMS sem FSCS e sem identificador TCMS no número',
                'concession' => Carbon::now()->subMonths(1),
                'validity' => null,
            ]);
        }

        // 3x CRED sem FSCS
        for ($i = 0; $i < 3; $i++) {
            Credential::create([
                'user_id' => $users[$userIndex++]->id,
                'fscs' => null,
                'type' => CredentialType::CRED,
                'secrecy' => CredentialSecrecy::RESERVADO,
                'credential' => 'CRED-'.str_pad($credentialNumber++, 6, '0', STR_PAD_LEFT),
                'observation' => 'PANE: CRED sem FSCS',
                'concession' => null,
                'validity' => null,
            ]);
        }

        // 5x TCMS com FSCS mas SEM concessão (PANE)
        for ($i = 0; $i < 5; $i++) {
            Credential::create([
                'user_id' => $users[$userIndex++]->id,
                'fscs' => str_pad(rand(10000, 99999), 5, '0', STR_PAD_LEFT),
                'type' => CredentialType::TCMS,
                'secrecy' => CredentialSecrecy::ACESSO_RESTRITO,
                'credential' => 'TCMS-'.str_pad($credentialNumber++, 6, '0', STR_PAD_LEFT),
                'observation' => 'PANE: TCMS com FSCS mas sem data de concessão do termo',
                'concession' => null,
                'validity' => null,
            ]);
        }

        $this->command->info('   ✓ 10 casos edge (PANE) criados');

        // ==========================================
        // RESUMO FINAL
        // ==========================================
        $this->command->newLine();
        $this->command->info('═══════════════════════════════════════════');
        $this->command->info('✅ RESUMO: 70 credenciais criadas com sucesso!');
        $this->command->newLine();
        $this->command->info('📊 Distribuição por Status:');
        $this->command->info('   • Negadas: 10');
        $this->command->info('   • Vencidas: 10');
        $this->command->info('   • TCMS Válidas (doc. sigilo): 10');
        $this->command->info('   • Em Processamento: 5 (TCMS com FSCS e concessão)');
        $this->command->info('   • Pendentes: 10 (CRED com FSCS mas sem concessão)');
        $this->command->info('   • Válidas (com gradiente): 15');
        $this->command->info('   • PANE - Verificar: 10');
        $this->command->info('     - 2 TCMS sem FSCS e sem "TCMS" no número');
        $this->command->info('     - 3 CRED sem FSCS');
        $this->command->info('     - 5 TCMS com FSCS mas sem concessão');
        $this->command->newLine();
        $this->command->info('📊 Distribuição por Tipo:');
        $this->command->info('   • CRED: '.Credential::where('type', CredentialType::CRED)->count());
        $this->command->info('   • TCMS: '.Credential::where('type', CredentialType::TCMS)->count());
        $this->command->newLine();
        $this->command->info('📊 Distribuição por Sigilo:');
        $this->command->info('   • Reservado (R): '.Credential::where('secrecy', CredentialSecrecy::RESERVADO)->count());
        $this->command->info('   • Secreto (S): '.Credential::where('secrecy', CredentialSecrecy::SECRETO)->count());
        $this->command->info('   • Acesso Restrito (AR): '.Credential::where('secrecy', CredentialSecrecy::ACESSO_RESTRITO)->count());
        $this->command->info('═══════════════════════════════════════════');
    }
}
