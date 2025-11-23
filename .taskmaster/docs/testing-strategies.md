# Estratégias de Teste do Projeto

Este documento descreve os padrões, estratégias e melhores práticas de testes utilizados no projeto. Todos os testes são escritos usando **Pest PHP** e seguem as convenções do Laravel 12 e Filament 4.

## 📋 Índice

1. [Estrutura de Testes](#estrutura-de-testes)
2. [Padrões Pest](#padrões-pest)
3. [Testing Filament/Livewire](#testing-filamentlivewire)
4. [Factories](#factories)
5. [Testing Policies](#testing-policies)
6. [Testing Observers](#testing-observers)
7. [Comandos Úteis](#comandos-úteis)
8. [Exemplos Completos](#exemplos-completos)
9. [Troubleshooting](#troubleshooting)

---

## Estrutura de Testes

### Feature Tests vs Unit Tests

**Feature Tests** (`tests/Feature/`):
- Testam funcionalidades completas da aplicação
- Utilizam banco de dados (RefreshDatabase)
- Testam interações HTTP, Livewire, Filament
- Maior parte dos testes do projeto

**Unit Tests** (`tests/Unit/`):
- Testam unidades isoladas de código
- NÃO utilizam banco de dados
- Testam Enums, métodos auxiliares, lógica pura
- Mais rápidos, mas menos comuns

### Quando usar cada tipo?

```php
// ✅ Feature Test - Testa integração com banco de dados
it('user has many credentials', function () {
    $user = User::factory()->create();
    Credential::factory()->count(3)->create(['user_id' => $user->id]);
    
    expect($user->credentials)->toHaveCount(3);
});

// ✅ Unit Test - Testa lógica pura sem dependências
it('returns correct label for acesso restrito', function () {
    expect(CredentialSecrecy::ACESSO_RESTRITO->label())->toBe('Acesso Restrito');
});
```

---

## Padrões Pest

### Estrutura Básica

```php
<?php

use App\Models\User;
use App\Models\Credential;

// Setup executado antes de cada teste
beforeEach(function () {
    // Configuração comum
    $this->user = User::factory()->create();
});

// Teste individual
it('can create credential', function () {
    $credential = Credential::factory()->create(['user_id' => $this->user->id]);
    
    expect($credential->exists)->toBeTrue();
});
```

### Datasets

Use datasets para testar múltiplos casos com o mesmo código:

```php
it('validates secrecy for credential type', function (string $secrecy, CredentialType $type, bool $expected) {
    expect(CredentialSecrecy::isValidForType($secrecy, $type))->toBe($expected);
})->with([
    ['R', CredentialType::CRED, true],
    ['S', CredentialType::CRED, true],
    ['AR', CredentialType::CRED, false],
    ['AR', CredentialType::TCMS, true],
    ['R', CredentialType::TCMS, false],
]);
```

### Expectations Comuns

```php
// Assertions de banco de dados
$this->assertDatabaseHas('credentials', ['fscs' => '12345']);
$this->assertSoftDeleted('credentials', ['id' => $credential->id]);

// Expectations do Pest
expect($credential->status)->toBe('Ativa');
expect($user->credentials)->toHaveCount(3);
expect($value)->toBeNull();
expect($value)->not->toBeNull();
expect($array)->toContain('item');
expect($credential)->toBeInstanceOf(Credential::class);
```

---

## Testing Filament/Livewire

### Testing Filament Resources

```php
use Livewire\Livewire;
use App\Filament\Resources\CredentialResource\Pages\ListCredentials;
use App\Filament\Resources\CredentialResource\Pages\CreateCredential;
use App\Filament\Resources\CredentialResource\Pages\EditCredential;

// Test: Listar credenciais
it('can list credentials', function () {
    $credentials = Credential::factory()->count(10)->create();
    
    Livewire::test(ListCredentials::class)
        ->assertCanSeeTableRecords($credentials)
        ->assertCountTableRecords(10);
});

// Test: Criar credencial
it('can create credential', function () {
    $user = User::factory()->create();
    
    Livewire::test(CreateCredential::class)
        ->fillForm([
            'user_id' => $user->id,
            'fscs' => 'TEST-12345',
            'credential' => 'CRED-12345',
            'type' => CredentialType::CRED->value,
            'secrecy' => 'R',
        ])
        ->call('create')
        ->assertHasNoFormErrors();
    
    $this->assertDatabaseHas('credentials', [
        'fscs' => 'TEST-12345',
        'credential' => 'CRED-12345',
    ]);
});

// Test: Editar credencial
it('can edit credential', function () {
    $credential = Credential::factory()->create();
    
    Livewire::test(EditCredential::class, ['record' => $credential->id])
        ->fillForm([
            'observation' => 'Updated observation',
        ])
        ->call('save')
        ->assertHasNoFormErrors();
    
    expect($credential->refresh()->observation)->toBe('Updated observation');
});

// Test: Deletar credencial
it('can delete credential', function () {
    $credential = Credential::factory()->create();
    
    Livewire::test(EditCredential::class, ['record' => $credential->id])
        ->callAction('delete');
    
    $this->assertSoftDeleted('credentials', ['id' => $credential->id]);
});
```

### Testing Filament Tables

```php
// Buscar na tabela
Livewire::test(ListCredentials::class)
    ->searchTable('12345')
    ->assertCanSeeTableRecords($matchingCredentials)
    ->assertCanNotSeeTableRecords($otherCredentials);

// Filtrar tabela
Livewire::test(ListCredentials::class)
    ->filterTable('type', CredentialType::CRED->value)
    ->assertCanSeeTableRecords($credTypeCredentials);

// Ordenar tabela
Livewire::test(ListCredentials::class)
    ->sortTable('fscs', 'desc')
    ->assertCanSeeTableRecords($credentials, inOrder: true);
```

### Testing Filament Actions

```php
// Action em um registro
Livewire::test(EditCredential::class, ['record' => $credential->id])
    ->callAction('renew')
    ->assertNotified();

// Action em massa
Livewire::test(ListCredentials::class)
    ->callTableBulkAction('delete', $credentials)
    ->assertNotified();
```

---

## Factories

### User Factory

```php
// Usuário básico
$user = User::factory()->create();

// Usuário com role específica
$admin = User::factory()->create();
$admin->assignRole('admin');

// Usuário com relacionamentos
$user = User::factory()
    ->has(Credential::factory()->count(3))
    ->create();

// State: Admin
$admin = User::factory()->admin()->create();
```

### Credential Factory

```php
// Credencial básica
$credential = Credential::factory()->create();

// Credencial com dados específicos
$credential = Credential::factory()->create([
    'user_id' => $user->id,
    'fscs' => 'CUSTOM-001',
    'type' => CredentialType::CRED,
    'secrecy' => 'R',
]);

// Credencial vencida
$expired = Credential::factory()->create([
    'concession' => now()->subYears(3), // Validade será 2 anos após
]);

// Credencial negada
$denied = Credential::factory()->create([
    'fscs' => '00000',
]);
```

---

## Testing Policies

### Teste Direto de Policy

```php
use App\Models\User;
use App\Models\Credential;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    Permission::create(['name' => 'Visualizar Credenciais', 'guard_name' => 'web']);
});

it('user with permission can view credentials', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('Visualizar Credenciais');
    
    $credential = Credential::factory()->create();
    
    expect($user->can('view', $credential))->toBeTrue();
});

it('user without permission cannot view credentials', function () {
    $user = User::factory()->create();
    $credential = Credential::factory()->create();
    
    expect($user->can('view', $credential))->toBeFalse();
});

it('user cannot delete themselves', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('Excluir Usuários');
    
    expect($user->can('delete', $user))->toBeFalse();
});
```

---

## Testing Observers

### Setup com Autenticação

```php
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

it('logs credential creation', function () {
    $user = User::factory()->create();
    Auth::login($user); // Importante para observers que usam Auth::id()
    
    $credential = Credential::factory()->create();
    
    $this->assertDatabaseHas('activity_logs', [
        'log_name' => 'credentials',
        'description' => 'created',
        'subject_id' => $credential->id,
        'causer_id' => $user->id,
    ]);
});

it('logs without authenticated user', function () {
    // Sem Auth::login()
    $credential = Credential::factory()->create();
    
    $this->assertDatabaseHas('activity_logs', [
        'log_name' => 'credentials',
        'description' => 'created',
        'subject_id' => $credential->id,
        'causer_id' => null, // Sistema
    ]);
});
```

### Validando Propriedades JSON

```php
it('logs contain credential data', function () {
    $user = User::factory()->create();
    Auth::login($user);
    
    $credential = Credential::factory()->create([
        'fscs' => 'TEST-001',
    ]);
    
    $log = DB::table('activity_logs')
        ->where('subject_id', $credential->id)
        ->where('description', 'created')
        ->first();
    
    $properties = json_decode($log->properties, true);
    expect($properties['attributes']['fscs'])->toBe('TEST-001');
});
```

---

## Comandos Úteis

### Executar Testes

```bash
# Todos os testes
vendor/bin/sail artisan test

# Testes de um arquivo específico
vendor/bin/sail artisan test tests/Feature/Models/CredentialTest.php

# Filtrar por nome do teste
vendor/bin/sail artisan test --filter="can create credential"

# Testes com cobertura
vendor/bin/sail artisan test --coverage

# Testes com cobertura mínima
vendor/bin/sail artisan test --coverage --min=80

# Testes em paralelo (mais rápido)
vendor/bin/sail artisan test --parallel

# Testes com mais detalhes
vendor/bin/sail artisan test --verbose
```

### Debugging

```bash
# Parar no primeiro erro
vendor/bin/sail artisan test --stop-on-failure

# Ver output completo
vendor/bin/sail artisan test --display-errors

# Usar dd() ou dump() nos testes
it('debugs data', function () {
    $credential = Credential::factory()->create();
    dd($credential->toArray()); // Para e mostra dados
    dump($credential); // Mostra dados e continua
});
```

---

## Exemplos Completos

### Exemplo 1: Teste Completo de Resource Filament

```php
<?php

use App\Filament\Resources\CredentialResource\Pages\ListCredentials;
use App\Filament\Resources\CredentialResource\Pages\CreateCredential;
use App\Filament\Resources\CredentialResource\Pages\EditCredential;
use App\Models\Credential;
use App\Models\User;
use App\Enums\CredentialType;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // Setup de permissões
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    
    $permissions = [
        'Visualizar Credenciais',
        'Criar Credenciais',
        'Editar Credenciais',
        'Excluir Credenciais',
    ];

    foreach ($permissions as $permission) {
        Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
    }

    $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    $admin->syncPermissions(Permission::all());
    
    // Usuário admin autenticado
    $this->user = User::factory()->create();
    $this->user->assignRole('admin');
    $this->actingAs($this->user);
});

it('can render list page', function () {
    Livewire::test(ListCredentials::class)
        ->assertSuccessful();
});

it('can list credentials', function () {
    $credentials = Credential::factory()->count(5)->create();
    
    Livewire::test(ListCredentials::class)
        ->assertCanSeeTableRecords($credentials)
        ->assertCountTableRecords(5);
});

it('can create credential', function () {
    $user = User::factory()->create();
    
    Livewire::test(CreateCredential::class)
        ->fillForm([
            'user_id' => $user->id,
            'fscs' => 'TEST-12345',
            'credential' => 'CRED-12345',
            'type' => CredentialType::CRED->value,
            'secrecy' => 'R',
            'observation' => 'Test credential',
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertRedirect();
    
    $this->assertDatabaseHas('credentials', [
        'fscs' => 'TEST-12345',
        'credential' => 'CRED-12345',
        'user_id' => $user->id,
    ]);
});

it('validates required fields', function () {
    Livewire::test(CreateCredential::class)
        ->fillForm([
            'fscs' => '',
            'credential' => '',
        ])
        ->call('create')
        ->assertHasFormErrors(['fscs', 'credential', 'type', 'secrecy']);
});

it('can edit credential', function () {
    $credential = Credential::factory()->create();
    
    Livewire::test(EditCredential::class, ['record' => $credential->id])
        ->fillForm([
            'observation' => 'Updated observation',
        ])
        ->call('save')
        ->assertHasNoFormErrors();
    
    expect($credential->refresh()->observation)->toBe('Updated observation');
});

it('can delete credential', function () {
    $credential = Credential::factory()->create();
    
    Livewire::test(EditCredential::class, ['record' => $credential->id])
        ->callAction('delete');
    
    $this->assertSoftDeleted('credentials', ['id' => $credential->id]);
});

it('can search credentials', function () {
    $credential1 = Credential::factory()->create(['fscs' => 'SEARCH-001']);
    $credential2 = Credential::factory()->create(['fscs' => 'OTHER-002']);
    
    Livewire::test(ListCredentials::class)
        ->searchTable('SEARCH-001')
        ->assertCanSeeTableRecords([$credential1])
        ->assertCanNotSeeTableRecords([$credential2]);
});

it('can filter credentials by type', function () {
    $credType = Credential::factory()->create(['type' => CredentialType::CRED]);
    $tcmsType = Credential::factory()->create(['type' => CredentialType::TCMS]);
    
    Livewire::test(ListCredentials::class)
        ->filterTable('type', CredentialType::CRED->value)
        ->assertCanSeeTableRecords([$credType])
        ->assertCanNotSeeTableRecords([$tcmsType]);
});
```

### Exemplo 2: Teste de Relacionamentos com Soft Delete

```php
<?php

use App\Models\User;
use App\Models\Credential;

it('user has many credentials', function () {
    $user = User::factory()->create();
    Credential::factory()->count(3)->create(['user_id' => $user->id]);
    
    expect($user->credentials)->toHaveCount(3);
    expect($user->credentials->first())->toBeInstanceOf(Credential::class);
});

it('soft delete preserves credential', function () {
    $credential = Credential::factory()->create();
    
    $credential->delete();
    
    $this->assertSoftDeleted('credentials', ['id' => $credential->id]);
    
    $deletedCredential = Credential::withTrashed()->find($credential->id);
    expect($deletedCredential)->not->toBeNull();
    expect($deletedCredential->deleted_at)->not->toBeNull();
});

it('can restore soft deleted credential', function () {
    $credential = Credential::factory()->create();
    $credential->delete();
    
    $credential->restore();
    
    expect($credential->deleted_at)->toBeNull();
    $this->assertDatabaseHas('credentials', [
        'id' => $credential->id,
        'deleted_at' => null,
    ]);
});

it('with trashed includes soft deleted credentials', function () {
    $user = User::factory()->create();
    
    $active = Credential::factory()->create(['user_id' => $user->id]);
    $deleted = Credential::factory()->create(['user_id' => $user->id]);
    $deleted->delete();
    
    // Sem withTrashed
    expect($user->credentials)->toHaveCount(1);
    
    // Com withTrashed
    $all = $user->credentials()->withTrashed()->get();
    expect($all)->toHaveCount(2);
});
```

---

## Troubleshooting

### Problema: "Class not found"

**Solução**: Adicione o `use` correto no início do arquivo:

```php
use App\Models\User;
use App\Models\Credential;
use Livewire\Livewire;
```

### Problema: "Call to undefined function livewire()"

**Solução**: Use `Livewire::test()` ao invés de `livewire()`:

```php
// ❌ Errado
livewire(ListCredentials::class)

// ✅ Correto
use Livewire\Livewire;
Livewire::test(ListCredentials::class)
```

### Problema: "Database not found" ou "Table not found"

**Solução**: Certifique-se de que o teste está usando `RefreshDatabase`:

```php
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class); // Para testes Feature
```

### Problema: "Permission not found"

**Solução**: Crie as permissões no `beforeEach`:

```php
beforeEach(function () {
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    
    Permission::create(['name' => 'Visualizar Credenciais', 'guard_name' => 'web']);
});
```

### Problema: Observer não está sendo executado nos testes

**Solução**: Use `Auth::login($user)` antes de criar/atualizar o model:

```php
$user = User::factory()->create();
Auth::login($user);

$credential = Credential::factory()->create(); // Observer será executado com Auth::id()
```

### Problema: Teste passa individualmente mas falha na suite completa

**Solução**: Limpe o cache de permissões no `beforeEach`:

```php
beforeEach(function () {
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
});
```

### Problema: Filament test retorna 403 Forbidden

**Solução**: Certifique-se de que:
1. O usuário tem as permissões necessárias
2. O usuário tem uma role que pode acessar o painel
3. Você chamou `$this->actingAs($user)` antes do teste

```php
$user = User::factory()->create();
$user->assignRole('admin');
$user->givePermissionTo('Visualizar Credenciais');
$this->actingAs($user);
```

---

## Cobertura de Código

### Meta do Projeto

- **Mínimo**: 80%
- **Atual**: 84.9%
- **Ideal**: 90%+

### Componentes que devem ter 100% de cobertura:

- ✅ Models
- ✅ Policies
- ✅ Observers
- ✅ Enums
- ✅ Filament Resources (pages, schemas, tables)

### Componentes que podem ter cobertura menor:

- Controllers não utilizados
- View Components não utilizados
- Service Providers de funcionalidades não implementadas
- Middlewares específicos não utilizados

---

## Referências

- [Pest PHP Documentation](https://pestphp.com/docs)
- [Filament Testing Documentation](https://filamentphp.com/docs/testing)
- [Laravel Testing Documentation](https://laravel.com/docs/testing)
- [Livewire Testing Documentation](https://livewire.laravel.com/docs/testing)

---

**Última atualização**: 2024-11-23  
**Versão**: 1.0  
**Cobertura atual**: 84.9%
