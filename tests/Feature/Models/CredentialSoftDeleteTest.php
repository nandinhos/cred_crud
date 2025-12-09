<?php

namespace Tests\Feature\Models;

use App\Models\Credential;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CredentialSoftDeleteTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    /** @test */
    public function credencial_pode_ser_soft_deleted(): void
    {
        $credential = Credential::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $credential->delete();

        $this->assertSoftDeleted('credentials', [
            'id' => $credential->id,
        ]);

        $this->assertNotNull($credential->fresh()->deleted_at);
    }

    /** @test */
    public function credencial_soft_deleted_pode_ser_restaurada(): void
    {
        $credential = Credential::factory()->create([
            'user_id' => $this->user->id,
        ]);

        // Soft delete
        $credential->delete();
        $this->assertSoftDeleted('credentials', ['id' => $credential->id]);

        // Restaurar
        $credential->restore();

        // Verificar que foi restaurada
        $this->assertDatabaseHas('credentials', [
            'id' => $credential->id,
            'deleted_at' => null,
        ]);

        $this->assertNull($credential->fresh()->deleted_at);
    }

    /** @test */
    public function credencial_pode_ser_deletada_permanentemente(): void
    {
        $credential = Credential::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $credentialId = $credential->id;

        // Force delete
        $credential->forceDelete();

        // Verificar que foi removida permanentemente
        $this->assertDatabaseMissing('credentials', [
            'id' => $credentialId,
        ]);

        $this->assertNull(Credential::withTrashed()->find($credentialId));
    }

    /** @test */
    public function relacionamento_credentials_nao_retorna_deletadas_por_padrao(): void
    {
        // Criar credencial vencida primeiro
        $expiredCredential = Credential::factory()->expired()->create([
            'user_id' => $this->user->id,
        ]);

        // Criar credencial ativa (automaticamente deleta a vencida)
        $activeCredential = Credential::factory()->active()->create([
            'user_id' => $this->user->id,
        ]);

        // Verificar que apenas a ativa é retornada
        $this->user->refresh();
        $credentials = $this->user->credentials;

        $this->assertCount(1, $credentials);
        $this->assertTrue($credentials->contains($activeCredential));
        $this->assertFalse($credentials->contains($expiredCredential));
    }

    /** @test */
    public function relacionamento_credential_history_retorna_todas_incluindo_deletadas(): void
    {
        // Criar credencial vencida primeiro
        $expiredCredential = Credential::factory()->expired()->create([
            'user_id' => $this->user->id,
        ]);

        // Criar credencial ativa (automaticamente deleta a vencida)
        $activeCredential = Credential::factory()->active()->create([
            'user_id' => $this->user->id,
        ]);

        // Verificar que ambas são retornadas (incluindo a deletada)
        $this->user->refresh();
        $history = $this->user->credentialHistory;

        $this->assertCount(2, $history);
        $this->assertTrue($history->contains($activeCredential));
        $this->assertTrue($history->contains($expiredCredential));
    }

    /** @test */
    public function relacionamento_active_credential_retorna_apenas_nao_deletadas(): void
    {
        // Criar credencial vencida primeiro (permite criar nova)
        $expiredCredential = Credential::factory()->expired()->create([
            'user_id' => $this->user->id,
        ]);

        // Criar credencial ativa (irá deletar automaticamente a vencida)
        $activeCredential = Credential::factory()->active()->create([
            'user_id' => $this->user->id,
        ]);

        // Verificar que a ativa existe e a vencida foi deletada
        $this->user->refresh();
        $activeCredentials = $this->user->activeCredential;

        $this->assertCount(1, $activeCredentials);
        $this->assertTrue($activeCredentials->contains($activeCredential));

        // Verificar que a credencial vencida foi soft deleted
        $this->assertSoftDeleted('credentials', ['id' => $expiredCredential->id]);
    }

    /** @test */
    public function query_with_trashed_retorna_todas_credenciais(): void
    {
        // Criar credencial vencida primeiro
        $expiredCredential = Credential::factory()->expired()->create([
            'user_id' => $this->user->id,
        ]);

        // Criar credencial ativa (automaticamente deleta a vencida)
        $activeCredential = Credential::factory()->active()->create([
            'user_id' => $this->user->id,
        ]);

        // Buscar todas (incluindo deletadas)
        $allCredentials = Credential::withTrashed()->get();

        $this->assertCount(2, $allCredentials);
        $this->assertTrue($allCredentials->contains($activeCredential));
        $this->assertTrue($allCredentials->contains($expiredCredential));
    }

    /** @test */
    public function query_only_trashed_retorna_apenas_deletadas(): void
    {
        // Criar credencial vencida primeiro
        $expiredCredential = Credential::factory()->expired()->create([
            'user_id' => $this->user->id,
        ]);

        // Criar credencial ativa (automaticamente deleta a vencida)
        $activeCredential = Credential::factory()->active()->create([
            'user_id' => $this->user->id,
        ]);

        // Buscar apenas deletadas
        $trashedCredentials = Credential::onlyTrashed()->get();

        $this->assertCount(1, $trashedCredentials);
        $this->assertFalse($trashedCredentials->contains($activeCredential));
        $this->assertTrue($trashedCredentials->contains($expiredCredential));
    }

    /** @test */
    public function metodo_trashed_retorna_true_para_credencial_deletada(): void
    {
        $credential = Credential::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $this->assertFalse($credential->trashed());

        $credential->delete();

        $this->assertTrue($credential->fresh()->trashed());
    }

    /** @test */
    public function deleted_at_timestamp_e_registrado_ao_deletar(): void
    {
        // Usar credencial negada que pode ser deletada sem conflito
        $credential = Credential::factory()->denied()->create([
            'user_id' => $this->user->id,
        ]);

        $this->assertNull($credential->deleted_at);

        $beforeDelete = now()->subSecond(); // Dar 1 segundo de tolerância
        $credential->delete();
        $afterDelete = now()->addSecond(); // Dar 1 segundo de tolerância

        $deletedAt = $credential->fresh()->deleted_at;

        $this->assertNotNull($deletedAt);
        $this->assertTrue(
            $deletedAt->between($beforeDelete, $afterDelete),
            "deleted_at ({$deletedAt}) should be between {$beforeDelete} and {$afterDelete}"
        );
    }

    /** @test */
    public function deleted_at_e_resetado_ao_restaurar(): void
    {
        $credential = Credential::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $credential->delete();
        $this->assertNotNull($credential->fresh()->deleted_at);

        $credential->restore();
        $this->assertNull($credential->fresh()->deleted_at);
    }

    /** @test */
    public function multiplas_credenciais_podem_ser_deletadas_e_restauradas(): void
    {
        // Criar 5 credenciais negadas (podem coexistir) para o mesmo usuário
        $credentials = Credential::factory()->denied()->count(5)->create([
            'user_id' => $this->user->id,
        ]);

        // Deletar todas
        foreach ($credentials as $credential) {
            $credential->delete();
        }

        $this->assertEquals(5, Credential::onlyTrashed()->count());
        $this->assertEquals(0, Credential::count());

        // Restaurar todas (credenciais negadas podem coexistir)
        Credential::onlyTrashed()->restore();

        $this->assertEquals(0, Credential::onlyTrashed()->count());
        $this->assertEquals(5, Credential::count());
    }

    /** @test */
    public function usuario_pode_ter_credencial_ativa_e_historico_de_deletadas(): void
    {
        // Criar histórico de 3 credenciais deletadas
        for ($i = 0; $i < 3; $i++) {
            $oldCredential = Credential::factory()->create([
                'user_id' => $this->user->id,
                'fscs' => '0000'.$i,
            ]);
            $oldCredential->delete();
        }

        // Criar credencial ativa atual
        $currentCredential = Credential::factory()->create([
            'user_id' => $this->user->id,
            'fscs' => '12345',
        ]);

        // Verificar contagens
        $this->assertEquals(1, $this->user->credentials()->count());
        $this->assertEquals(4, $this->user->credentialHistory()->count());
        $this->assertEquals(3, $this->user->credentials()->onlyTrashed()->count());
    }

    /** @test */
    public function credencial_deletada_mantem_todos_os_dados(): void
    {
        // Usar credencial negada para evitar conflitos de regra de negócio
        $credential = Credential::factory()->denied()->create([
            'user_id' => $this->user->id,
            'credential' => 'CRED-2024-001',
        ]);

        $originalData = [
            'fscs' => $credential->fscs,
            'type' => $credential->type,
            'secrecy' => $credential->secrecy,
            'credential' => $credential->credential,
            'user_id' => $credential->user_id,
        ];

        $credential->delete();

        $deletedCredential = Credential::withTrashed()->find($credential->id);

        // Verificar que todos os dados foram mantidos (exceto deleted_at)
        $this->assertEquals($originalData['fscs'], $deletedCredential->fscs);
        $this->assertEquals($originalData['type'], $deletedCredential->type);
        $this->assertEquals($originalData['secrecy'], $deletedCredential->secrecy);
        $this->assertEquals($originalData['credential'], $deletedCredential->credential);
        $this->assertEquals($originalData['user_id'], $deletedCredential->user_id);

        // Verificar que a credencial está soft deleted
        $this->assertTrue($deletedCredential->trashed());
    }
}
