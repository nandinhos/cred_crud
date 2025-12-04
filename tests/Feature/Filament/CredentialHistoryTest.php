<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\Credentials\CredentialResource;
use App\Filament\Resources\Credentials\Pages\ListCredentials;
use App\Filament\Resources\UserResource;
use App\Filament\Resources\UserResource\RelationManagers\CredentialsRelationManager;
use App\Models\Credential;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CredentialHistoryTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $testUser;

    protected Credential $credential;

    protected function setUp(): void
    {
        parent::setUp();

        // Executar seeders necessários
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        // Criar usuário admin
        $this->admin = User::factory()->create([
            'email' => 'admin@test.com',
        ]);
        $this->admin->assignRole('super_admin');

        // Criar usuário de teste
        $this->testUser = User::factory()->create();

        // Criar credencial de teste
        $this->credential = Credential::factory()->create([
            'user_id' => $this->testUser->id,
        ]);
    }

    /** @test */
    public function pode_visualizar_credenciais_deletadas_na_tabela(): void
    {
        // Deletar a credencial
        $this->credential->delete();

        // Verificar que a credencial foi soft deleted
        $this->assertSoftDeleted('credentials', ['id' => $this->credential->id]);

        // Autenticar como admin
        $this->actingAs($this->admin);

        // Acessar a lista de credenciais com filtro de deletadas
        Livewire::test(ListCredentials::class)
            ->assertSuccessful()
            ->assertCanSeeTableRecords([$this->credential]);
    }

    /** @test */
    public function pode_restaurar_credencial_deletada(): void
    {
        // Deletar a credencial
        $this->credential->delete();
        $this->assertSoftDeleted('credentials', ['id' => $this->credential->id]);

        // Autenticar como admin
        $this->actingAs($this->admin);

        // Restaurar a credencial
        Livewire::test(ListCredentials::class)
            ->callTableAction('restore', $this->credential);

        // Verificar que a credencial foi restaurada
        $this->assertDatabaseHas('credentials', [
            'id' => $this->credential->id,
            'deleted_at' => null,
        ]);
    }

    /** @test */
    public function pode_deletar_permanentemente_credencial(): void
    {
        // Deletar a credencial (soft delete)
        $this->credential->delete();

        // Autenticar como super admin
        $this->actingAs($this->admin);

        // Force delete a credencial
        Livewire::test(ListCredentials::class)
            ->callTableAction('forceDelete', $this->credential);

        // Verificar que a credencial foi removida permanentemente
        $this->assertDatabaseMissing('credentials', [
            'id' => $this->credential->id,
        ]);
    }

    /** @test */
    public function pode_restaurar_multiplas_credenciais(): void
    {
        // Criar mais credenciais
        $credential2 = Credential::factory()->create(['user_id' => $this->testUser->id]);
        $credential3 = Credential::factory()->create(['user_id' => $this->testUser->id]);

        // Deletar as credenciais
        $this->credential->delete();
        $credential2->delete();
        $credential3->delete();

        // Autenticar como admin
        $this->actingAs($this->admin);

        // Restaurar múltiplas credenciais
        Livewire::test(ListCredentials::class)
            ->callTableBulkAction('restore', [$this->credential, $credential2, $credential3]);

        // Verificar que todas foram restauradas
        $this->assertDatabaseHas('credentials', ['id' => $this->credential->id, 'deleted_at' => null]);
        $this->assertDatabaseHas('credentials', ['id' => $credential2->id, 'deleted_at' => null]);
        $this->assertDatabaseHas('credentials', ['id' => $credential3->id, 'deleted_at' => null]);
    }

    /** @test */
    public function usuario_pode_ver_historico_completo_no_relation_manager(): void
    {
        // Criar credencial ativa
        $activeCredential = Credential::factory()->create(['user_id' => $this->testUser->id]);

        // Criar e deletar credencial
        $deletedCredential = Credential::factory()->create(['user_id' => $this->testUser->id]);
        $deletedCredential->delete();

        // Autenticar como admin
        $this->actingAs($this->admin);

        // Verificar que ambas aparecem no relation manager
        Livewire::test(CredentialsRelationManager::class, [
            'ownerRecord' => $this->testUser,
            'pageClass' => UserResource\Pages\EditUser::class,
        ])
            ->assertSuccessful()
            ->assertCanSeeTableRecords([$activeCredential, $deletedCredential]);
    }

    /** @test */
    public function relation_manager_mostra_status_correto_de_credenciais(): void
    {
        // Credencial ativa
        $activeCredential = Credential::factory()->create(['user_id' => $this->testUser->id]);

        // Credencial deletada
        $deletedCredential = Credential::factory()->create(['user_id' => $this->testUser->id]);
        $deletedCredential->delete();

        // Autenticar como admin
        $this->actingAs($this->admin);

        // Testar o relation manager
        $component = Livewire::test(CredentialsRelationManager::class, [
            'ownerRecord' => $this->testUser,
            'pageClass' => UserResource\Pages\EditUser::class,
        ]);

        // Verificar que mostra ambas as credenciais
        $component
            ->assertSuccessful()
            ->assertCanSeeTableRecords([$activeCredential, $deletedCredential]);
    }

    /** @test */
    public function pode_criar_nova_credencial_pelo_relation_manager(): void
    {
        // Autenticar como admin
        $this->actingAs($this->admin);

        // Criar credencial pelo relation manager
        Livewire::test(CredentialsRelationManager::class, [
            'ownerRecord' => $this->testUser,
            'pageClass' => UserResource\Pages\EditUser::class,
        ])
            ->callTableAction('create', data: [
                'fscs' => '12345',
                'type' => 'CRED',
                'secrecy' => 'R',
                'credential' => 'CRED-2024-001',
                'concession' => now()->format('Y-m-d'),
                'validity' => now()->addYear()->format('Y-m-d'),
            ]);

        // Verificar que a credencial foi criada
        $this->assertDatabaseHas('credentials', [
            'user_id' => $this->testUser->id,
            'fscs' => '12345',
            'type' => 'CRED',
        ]);
    }

    /** @test */
    public function filtro_trashed_funciona_corretamente(): void
    {
        // Criar credencial ativa
        $activeCredential = Credential::factory()->create(['user_id' => $this->testUser->id]);

        // Criar e deletar credencial
        $deletedCredential = Credential::factory()->create(['user_id' => $this->testUser->id]);
        $deletedCredential->delete();

        // Autenticar como admin
        $this->actingAs($this->admin);

        // Testar filtro "Sem deletadas" (apenas ativas)
        Livewire::test(ListCredentials::class)
            ->filterTable('trashed', '')
            ->assertCanSeeTableRecords([$activeCredential])
            ->assertCanNotSeeTableRecords([$deletedCredential]);

        // Testar filtro "Apenas deletadas"
        Livewire::test(ListCredentials::class)
            ->filterTable('trashed', 'only')
            ->assertCanSeeTableRecords([$deletedCredential])
            ->assertCanNotSeeTableRecords([$activeCredential]);

        // Testar filtro "Com deletadas" (todas)
        Livewire::test(ListCredentials::class)
            ->filterTable('trashed', 'with')
            ->assertCanSeeTableRecords([$activeCredential, $deletedCredential]);
    }

    /** @test */
    public function coluna_deletada_mostra_icone_correto(): void
    {
        // Criar credencial ativa
        $activeCredential = Credential::factory()->create(['user_id' => $this->testUser->id]);

        // Criar e deletar credencial
        $deletedCredential = Credential::factory()->create(['user_id' => $this->testUser->id]);
        $deletedCredential->delete();

        // Autenticar como admin
        $this->actingAs($this->admin);

        // Verificar que a coluna "is_deleted" mostra o estado correto
        $component = Livewire::test(ListCredentials::class)
            ->filterTable('trashed', 'with');

        $component->assertSuccessful();
    }

    /** @test */
    public function notificacao_exibida_ao_restaurar_credencial(): void
    {
        // Deletar a credencial
        $this->credential->delete();

        // Autenticar como admin
        $this->actingAs($this->admin);

        // Restaurar e verificar notificação
        Livewire::test(ListCredentials::class)
            ->callTableAction('restore', $this->credential)
            ->assertNotified();
    }

    /** @test */
    public function notificacao_exibida_ao_deletar_permanentemente(): void
    {
        // Deletar a credencial (soft delete)
        $this->credential->delete();

        // Autenticar como admin
        $this->actingAs($this->admin);

        // Force delete e verificar notificação
        Livewire::test(ListCredentials::class)
            ->callTableAction('forceDelete', $this->credential)
            ->assertNotified();
    }
}
