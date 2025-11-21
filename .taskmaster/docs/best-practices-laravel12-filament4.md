# Melhores Práticas - Laravel 12 + Filament 4

## 📋 Índice
- [Configuração Inicial](#configuração-inicial)
- [Estrutura de Projeto](#estrutura-de-projeto)
- [Filament Resources](#filament-resources)
- [Validações e Formulários](#validações-e-formulários)
- [Sistema de Permissões](#sistema-de-permissões)
- [Performance e Otimização](#performance-e-otimização)
- [Testes e Deployment](#testes-e-deployment)

## 🚀 Configuração Inicial

### Requisitos de Sistema
```bash
# PHP 8.3+ obrigatório para Laravel 12
php: ^8.3

# Dependências principais
laravel/framework: ^12.0
filament/filament: ^4.0
```

### Docker Setup
```yaml
# docker-compose.yml - usar PHP 8.3
laravel.test:
  build:
    context: ./vendor/laravel/sail/runtimes/8.3
  image: sail-8.3/app
```

### Configuração de Ambiente
```env
# .env essenciais
APP_ENV=local
APP_DEBUG=true
APP_KEY=base64:...

# Database para Docker
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=cred_crud
DB_USERNAME=sail
DB_PASSWORD=sail
```

## 🏗️ Estrutura de Projeto

### Organização de Arquivos Filament
```
app/Filament/
├── Resources/
│   └── [Entity]/
│       ├── [Entity]Resource.php
│       └── Pages/
│           ├── Create[Entity].php
│           ├── Edit[Entity].php
│           └── List[Entities].php
├── Pages/
└── Widgets/
```

### User Model para Filament
```php
<?php
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    use HasRoles;
    
    public function canAccessPanel(Panel $panel): bool
    {
        // 1. Acesso Global ao Painel
        if ($this->email === 'admin@admin.com') {
            return true;
        }
        
        return $this->hasRole(['super_admin', 'admin', 'consulta']);
    }
}
```

### Authorization Patterns (Policy vs canAccess)
- **canAccessPanel (User Model)**: Controla quem pode *logar* no painel admin.
- **Policies (App\Policies)**: Controla *o que* o usuário pode fazer com cada Resource (view, create, update, delete).
- **Spatie Permissions**: Usado dentro das Policies para verificar roles/permissions.

**Exemplo de Policy:**
```php
public function viewAny(User $user): bool
{
    return $user->hasPermissionTo('view_any_credential');
}

public function create(User $user): bool
{
    return $user->hasPermissionTo('create_credential');
}
```

## 🛡️ Filament Resources

### Resource Básico (Sintaxe Correta Filament 4)
```php
<?php
namespace App\Filament\Resources;

use App\Models\[Entity];
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class [Entity]Resource extends Resource
{
    protected static ?string $model = [Entity]::class;
    protected static ?string $navigationIcon = 'heroicon-o-shield-check';
    
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            // Componentes do formulário
        ]);
    }
    
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Colunas da tabela
            ])
            ->actions([
                // ✅ CORRETO: Usar Actions do namespace Filament\Actions
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('delete')
                        ->label('Deletar Selecionados')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->delete()),
                ]),
            ]);
    }
}
```

### Formulários com Seções
```php
Forms\Components\Section::make('Informações Principais')
    ->description('Dados essenciais da entidade')
    ->schema([
        Forms\Components\TextInput::make('name')
            ->label('Nome')
            ->required()
            ->maxLength(255),
            
        Forms\Components\Select::make('type')
            ->label('Tipo')
            ->options([
                'option1' => 'Opção 1',
                'option2' => 'Opção 2',
            ])
            ->nullable(),
    ])
    ->columns(2),
```

### Tabelas com Indicadores Visuais
```php
Tables\Columns\TextColumn::make('validity')
    ->label('Validade')
    ->date('d/m/Y')
    ->sortable()
    ->color(function ($state) {
        if (!$state) return 'gray';
        $validity = \Carbon\Carbon::parse($state);
        
        if ($validity->isPast()) {
            return 'danger';
        } elseif ($validity->diffInDays(now()) <= 30) {
            return 'warning';
        }
        return 'success';
    })
    ->icon(function ($state) {
        if (!$state) return null;
        $validity = \Carbon\Carbon::parse($state);
        
        if ($validity->isPast()) {
            return 'heroicon-o-exclamation-triangle';
        } elseif ($validity->diffInDays(now()) <= 30) {
            return 'heroicon-o-clock';
        }
        return 'heroicon-o-check-circle';
    }),
```

## ✅ Validações e Formulários

### Validações Avançadas
```php
// Unique com Soft Deletes
Forms\Components\TextInput::make('code')
    ->unique(ignoreRecord: true, modifyRuleUsing: function ($rule, $get) {
        return $rule->whereNull('deleted_at');
    }),

// Validação de Data Futura
Forms\Components\DatePicker::make('validity')
    ->required()
    ->after('today'),

// Select com Nullable
Forms\Components\Select::make('status')
    ->options(['active' => 'Ativo', 'inactive' => 'Inativo'])
    ->nullable(),
```

### Casts no Model
```php
protected $casts = [
    'created_at' => 'datetime',
    'validity' => 'date',
    'concession' => 'date',
];
```

## 🔐 Sistema de Permissões

### Instalação e Configuração
```bash
# Instalar Spatie Permission
composer require spatie/permission

# Publicar migrations
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"

# Executar migrations
php artisan migrate
```

### Seeder de Permissões
```php
<?php
// AdminUserSeeder.php
public function run(): void
{
    // Criar permissões
    $permissions = [
        'view_credential',
        'view_any_credential',
        'create_credential',
        'update_credential',
        'delete_credential',
    ];

    foreach ($permissions as $permission) {
        Permission::firstOrCreate(['name' => $permission]);
    }

    // Criar role e atribuir permissões
    $role = Role::firstOrCreate(['name' => 'super_admin']);
    $role->givePermissionTo(Permission::all());

    // Criar usuário admin
    $user = User::firstOrCreate(
        ['email' => 'admin@admin.com'],
        [
            'name' => 'Administrator',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]
    );

    $user->assignRole('super_admin');
}
```

## ⚡ Performance e Otimização

### Assets e Vite
```bash
# Sempre compilar assets para produção
npm install
npm run build

# Verificar se manifest.json foi criado
ls -la public/build/manifest.json
```

### Cache e Otimização
```bash
# Limpar caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Otimizar para produção
composer dump-autoload --optimize
php artisan config:cache
php artisan route:cache
```

## 🧪 Testes e Deployment

### Testes Essenciais
### Testes com Pest PHP
**Estrutura Básica:**
```php
// tests/Feature/CredentialTest.php

use App\Models\User;
use App\Filament\Resources\CredentialResource;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

it('can render credential list page', function () {
    $user = User::factory()->create();
    $user->assignRole('admin');

    actingAs($user)
        ->get(CredentialResource::getUrl('index'))
        ->assertSuccessful();
});

it('cannot access credentials without permission', function () {
    $user = User::factory()->create();
    // Sem role atribuída

    actingAs($user)
        ->get(CredentialResource::getUrl('index'))
        ->assertForbidden();
});
```

### Factories e Seeders
**Factory Pattern:**
```php
// database/factories/CredentialFactory.php
public function definition(): array
{
    return [
        'fscs' => $this->faker->unique()->bothify('??###'),
        'name' => $this->faker->company(),
        'secrecy' => $this->faker->randomElement(['R', 'S']),
        'validity' => $this->faker->dateTimeBetween('now', '+1 year'),
    ];
}
```

**Uso em Testes:**
```php
$credential = Credential::factory()->create([
    'secrecy' => 'S'
]);
```

### Backup antes de Alterações
```bash
# Backup do banco de dados
docker-compose exec laravel.test mysqldump -u sail -psail database_name > backup_$(date +%Y%m%d_%H%M%S).sql
```

### Comandos Úteis
```bash
# Recriar resource Filament
php artisan make:filament-resource [Entity] --generate

# Criar usuário Filament
php artisan make:filament-user

# Publicar assets Filament
php artisan filament:assets
```

## 🎯 Checklist de Qualidade

### ✅ Antes de Commit
- [ ] Testes unitários executados
- [ ] Sistema funcionando no navegador
- [ ] Assets compilados (npm run build)
- [ ] Caches limpos
- [ ] Backup do banco criado (se necessário)
- [ ] Conventional commits em português

### ✅ Antes de Deploy
- [ ] Migrações testadas
- [ ] Seeders funcionando
- [ ] Permissões configuradas
- [ ] Assets otimizados
- [ ] Logs verificados
- [ ] Performance testada

---

**📝 Documento criado em:** $(date +"%Y-%m-%d %H:%M:%S")
**🔄 Última atualização:** Implementação Laravel 12 + Filament 4
**👤 Responsável:** Rovo Dev AI Assistant