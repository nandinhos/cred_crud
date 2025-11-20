# Lições Aprendidas - Laravel 12 + Filament 4

## 📚 Índice
- [Problemas Resolvidos](#problemas-resolvidos)
- [Migrações e Atualizações](#migrações-e-atualizações)
- [Configurações Críticas](#configurações-críticas)
- [Comandos Salvadores](#comandos-salvadores)
- [Prevenção de Problemas](#prevenção-de-problemas)

---

## 🚨 Problemas Resolvidos

### ❌ ERRO: "Class 'Filament\Tables\Actions\EditAction' not found"

**Problema:** 
```php
// ❌ CÓDIGO ERRADO
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;

->actions([
    EditAction::make(),
    DeleteAction::make(),
])
```

**Causa:** No Filament 4, as classes `EditAction` e `DeleteAction` não existem no namespace `Tables\Actions`.

**✅ SOLUÇÃO:**
```php
// ✅ CÓDIGO CORRETO
use Filament\Actions\Action;

->actions([
    Action::make('edit')
        ->label('Editar')
        ->icon('heroicon-o-pencil')
        ->url(fn ($record) => route('filament.admin.resources.credentials.edit', $record)),
    Action::make('delete')
        ->label('Deletar')
        ->icon('heroicon-o-trash')
        ->color('danger')
        ->requiresConfirmation()
        ->action(fn ($record) => $record->delete()),
])
```

**Lição:** Sempre verificar a documentação oficial do Filament 4 antes de usar ações pré-definidas.

---

### ❌ ERRO: "Class 'Filament\Forms\Components\Section' not found"

**Problema:** 
```php
// ❌ CÓDIGO ERRADO
use Filament\Forms\Components\Section;

Forms\Components\Section::make('Título')
```

**Causa:** No Filament 4, Section foi movida para o namespace `Schemas\Components`.

**✅ SOLUÇÃO:**
```php
// ✅ CÓDIGO CORRETO
\Filament\Schemas\Components\Section::make('Informações da Credencial')
    ->description('Dados principais')
    ->schema([
        Forms\Components\TextInput::make('name'),
        // outros campos...
    ])
```

**Lição:** Componentes de layout (Section, Group, etc.) estão em `Schemas\Components`, não em `Forms\Components`.

---

### ❌ ERRO: "Vite manifest not found" - Error 500

**Problema:** 
```
Vite manifest not found at: /var/www/html/public/build/manifest.json
GET http://localhost/ 500 (Internal Server Error)
```

**Causa:** Assets frontend não foram compilados após atualização do Laravel/Filament.

**✅ SOLUÇÃO:**
```bash
# Dentro do container Docker
docker-compose exec laravel.test npm install
docker-compose exec laravel.test npm run build

# Verificar se manifest foi criado
docker-compose exec laravel.test ls -la public/build/manifest.json
```

**Lição:** Sempre recompilar assets após atualizações de dependências.

---

### ❌ ERRO: "Unable to find component: [table]" - ComponentNotFoundException

**Problema:** Componentes Livewire legacy conflitando com Filament 4.

**Causa:** Views antigas do sistema Blade + Livewire v2 conflitando com Livewire v3.

**✅ SOLUÇÃO:**
```bash
# Remover componentes legacy
rm app/Http/Livewire/Table.php
rm resources/views/livewire/table.blade.php
rm resources/views/credentials/index.blade.php

# Limpar autoload
composer dump-autoload
```

**Lição:** Fazer limpeza completa de arquivos legacy ao migrar para Filament.

---

### ❌ ERRO: 403 Forbidden no painel /admin

**Problema:** Usuário admin não consegue acessar painel Filament.

**Causa:** Método `canAccessPanel()` restritivo ou usuário não logado.

**✅ SOLUÇÃO:**
```php
// Model User.php
public function canAccessPanel(Panel $panel): bool
{
    // Admin principal sempre tem acesso
    if ($this->email === 'admin@admin.com') {
        return true;
    }
    
    // Outros usuários precisam de role
    return $this->hasRole('super_admin') || $this->hasRole('admin');
}

// Rota temporária para login automático
Route::get('/login-admin', function () {
    $user = \App\Models\User::where('email', 'admin@admin.com')->first();
    if ($user) {
        \Illuminate\Support\Facades\Auth::login($user);
        session()->regenerate();
        return redirect('/admin');
    }
    return redirect('/admin/login');
});
```

**Lição:** Sempre prever fallback para usuário admin principal.

---

### ❌ ERRO: "Table 'cred_crud.roles' doesn't exist"

**Problema:** Sistema de permissões não configurado.

**Causa:** Migrations do Spatie Permission não executadas.

**✅ SOLUÇÃO:**
```bash
# Publicar migrations
docker-compose exec laravel.test php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"

# Executar migrations
docker-compose exec laravel.test php artisan migrate

# Criar permissões e usuário admin
docker-compose exec laravel.test php artisan db:seed --class=AdminUserSeeder
```

**Lição:** Sempre verificar se todas as dependências de terceiros foram configuradas.

---

## 🔄 Migrações e Atualizações

### Laravel 10 → Laravel 12
```bash
# Atualizar composer.json
"php": "^8.3"
"laravel/framework": "^12.0"

# Atualizar Docker
# docker-compose.yml: sail-8.2/app → sail-8.3/app

# Remover lock e reinstalar
rm composer.lock
composer install
```

### Filament 3 → Filament 4
```bash
# Principais mudanças identificadas:
# 1. Forms\Form → Schemas\Schema
# 2. Tables\Actions removidas
# 3. navigationIcon sintaxe alterada
# 4. BadgeColumn → StatusColumn em alguns casos
```

---

## ⚙️ Configurações Críticas

### Docker com PHP 8.3
```yaml
# docker-compose.yml
laravel.test:
  build:
    context: ./vendor/laravel/sail/runtimes/8.3
    dockerfile: Dockerfile
  image: sail-8.3/app
```

### Variables de Ambiente Docker
```bash
# Antes de docker-compose up
export WWWGROUP=1000
export WWWUSER=1000

# Ou criar .env.local
echo "WWWGROUP=1000" > .env.local
echo "WWWUSER=1000" >> .env.local
```

### Composer Platform Config
```bash
# Se necessário forçar versão PHP
composer config platform.php 8.3.0

# Ou remover restrição temporariamente
composer config --unset platform.php
```

---

## 🆘 Comandos Salvadores

### Diagnóstico Rápido
```bash
# Verificar versões
php artisan --version
composer show | grep filament
php --version

# Verificar classes existentes
php artisan tinker --execute="echo class_exists('\\Filament\\Actions\\Action') ? 'OK' : 'ERRO';"

# Verificar rotas Filament
php artisan route:list --name=filament
```

### Reset Completo
```bash
# Limpar todos os caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Recompilar assets
npm install
npm run build

# Reotimizar autoload
composer dump-autoload
```

### Backup Emergencial
```bash
# Backup completo antes de mudanças críticas
docker-compose exec laravel.test mysqldump -u sail -psail cred_crud > backup_emergency_$(date +%Y%m%d_%H%M%S).sql

# Backup de arquivos importantes
cp composer.json composer.json.backup
cp .env .env.backup
```

---

## 🛡️ Prevenção de Problemas

### Checklist Antes de Alterações Grandes
- [ ] ✅ Backup do banco de dados criado
- [ ] ✅ Backup dos arquivos principais (composer.json, .env)
- [ ] ✅ Commit atual estável
- [ ] ✅ Documentação das versões atuais
- [ ] ✅ Testes básicos funcionando

### Versionamento Adequado
```json
// composer.json - Versões específicas para estabilidade
{
    "require": {
        "php": "^8.3",
        "laravel/framework": "^12.0",
        "filament/filament": "^4.0"
    }
}
```

### Estrutura de Testes
```bash
# Testes automáticos após cada mudança
php artisan route:list --name=filament | wc -l  # Deve retornar > 0
curl -s http://localhost/admin/login | grep -q "Sign in" && echo "OK" || echo "ERRO"
```

---

## 📝 Padrões de Commit

### Conventional Commits (Português)
```bash
# Correções
git commit -m "fix: corrige problema de ações do Filament 4"

# Novas funcionalidades  
git commit -m "feat: adiciona sistema de permissões com Spatie"

# Refatoração
git commit -m "refactor: migra sistema para Laravel 12 + Filament 4"

# Documentação
git commit -m "docs: adiciona guia de melhores práticas"
```

---

## 🎯 Regras de Ouro

1. **Sempre ler a documentação oficial** antes de implementar
2. **Fazer backup** antes de alterações críticas no banco
3. **Testar no navegador** após cada mudança importante
4. **Limpar caches** após atualizações de dependências
5. **Usar Docker** para garantir ambiente consistente
6. **Commits pequenos e frequentes** com mensagens claras
7. **Documentar problemas** e soluções para referência futura

---

**📅 Última atualização:** $(date +"%Y-%m-%d %H:%M:%S")  
**🔧 Versão do sistema:** Laravel 12.39.0 + Filament 4.2.2  
**📊 Status:** Sistema 100% funcional  
**🎯 Próxima revisão:** A cada problema novo identificado

---

## 📋 **IMPLEMENTAÇÃO DE PAINEL DE ADMINISTRAÇÃO COM FILAMENT 4**

### ❌ ERRO: UserResource não aparece no menu do Filament
**Situação:** Após criar UserResource, o menu não aparecia para o admin

**Problemas identificados:**
1. UserResource criado em namespace incorreto (`App\Filament\Resources\Users\UserResource`)
2. Usuário admin tinha role `super_admin` ao invés de `Super Admin` criada pelo seeder
3. Propriedades com tipos incorretos para Filament 4

**✅ SOLUÇÃO:**
```php
// 1. UserResource no namespace correto
namespace App\Filament\Resources; // ✅ CORRETO
// NÃO: namespace App\Filament\Resources\Users; // ❌ ERRADO

// 2. Corrigir role do usuário admin
$admin->removeRole('super_admin');
$admin->assignRole('Super Admin');

// 3. Estrutura correta do Filament 4
public static function form(Schema $schema): Schema // ✅ CORRETO
// NÃO: public static function form(Form $form): Form // ❌ ERRADO Filament 3

// 4. Remover propriedades problemáticas temporariamente
protected static ?string $navigationLabel = "Usuários"; // ✅ CORRETO
// NÃO: protected static ?string $navigationGroup = "Admin"; // ❌ Causava erro de tipo
```

### ❌ ERRO: Permissions de roles não funcionando corretamente
**Situação:** Admin não tinha permissions para `view_users` mesmo sendo super admin

**Causa:** Seeder criou roles duplicadas e usuário tinha role incorreta

**✅ SOLUÇÃO:**
```bash
# Verificar roles existentes
Spatie\Permission\Models\Role::all()->pluck('name');

# Atribuir role correta
$admin = User::where('email', 'admin@admin.com')->first();
$admin->assignRole('Super Admin'); // Role criada pelo seeder

# Verificar permissions
$admin->can('view_users'); // Deve retornar true
```

### ❌ ERRO: Problemas de permissões em arquivos Docker
**Situação:** Erro "Permission denied" ao tentar editar arquivos via find_and_replace_code

**Causa:** Arquivos criados pelo Docker têm ownership diferente

**✅ SOLUÇÃO:**
```bash
# Usar docker exec para operações de arquivo
docker-compose exec laravel.test php -r "file_put_contents('path', 'content');"

# OU criar diretórios via Docker
docker-compose exec laravel.test mkdir -p /var/www/html/path
```

### ❌ ERRO: Estrutura de páginas incorreta no Filament 4
**Situação:** Páginas do Resource em local errado causavam erros

**✅ SOLUÇÃO:**
```php
// Estrutura correta:
app/Filament/Resources/UserResource.php
app/Filament/Resources/UserResource/Pages/ListUsers.php
app/Filament/Resources/UserResource/Pages/CreateUser.php
app/Filament/Resources/UserResource/Pages/EditUser.php

// Namespace das páginas:
namespace App\Filament\Resources\UserResource\Pages;

// Referência no Resource:
public static function getPages(): array
{
    return [
        'index' => Pages\ListUsers::route('/'),
        'create' => Pages\CreateUser::route('/create'),
        'edit' => Pages\EditUser::route('/{record}/edit'),
    ];
}
```

### 🎯 **BOAS PRÁTICAS APRENDIDAS:**

#### 1. **Verificação de Sistema de Roles**
```bash
# Sempre verificar roles e permissions após seeder
php artisan tinker --execute="
User::find(1)->roles->pluck('name');
User::find(1)->getAllPermissions()->pluck('name');
"
```

#### 2. **Estrutura de Resource no Filament 4**
```php
// Usar Schema ao invés de Form
public static function form(Schema $schema): Schema
{
    return $schema->components([...]);
}

// Usar actions corretos na table
->recordActions([Actions\EditAction::make()])
->toolbarActions([Actions\BulkActionGroup::make([...])])
```

#### 3. **Relacionamentos em Resources**
```php
// Select para relacionamentos
Select::make('roles')
    ->multiple()
    ->relationship('roles', 'name')
    ->preload()
    ->searchable();

// Badge para mostrar relacionamentos
BadgeColumn::make('roles.name')
    ->colors(['danger' => 'Super Admin'])
    ->separator(', ');
```

#### 4. **Sistema de Permissões**
```php
// Policy sempre verifica permissions do Spatie
public function viewAny(User $user): bool
{
    return $user->can('view_users');
}

// Resource usa canViewAny para menu
public static function canViewAny(): bool
{
    return auth()->user()?->can('view_users') ?? false;
}
```

### 📝 **CHECKLIST para Resources do Filament:**
- [ ] Namespace correto: `App\Filament\Resources`
- [ ] Método `form()` usa `Schema` não `Form`
- [ ] Método `table()` usa actions corretos
- [ ] Páginas em `ResourceName/Pages/`
- [ ] Permissions configuradas no Resource
- [ ] Policy criada e registrada
- [ ] Roles atribuídas corretamente aos usuários
- [ ] Relacionamentos testados

---

### ❌ ERRO: Usuários com role 'consulta' não conseguem acessar painel Filament

**📅 Data:** 20/11/2025  
**🔧 Contexto:** Após implementar sistema RBAC, usuários com role 'consulta' não conseguiam acessar o painel admin

**🚨 Problema identificado:**
- Método `canAccessPanel()` no model User estava permitindo acesso apenas para roles 'admin' e 'super_admin'
- Usuários com role 'consulta' deveriam poder acessar o painel, mas com permissões limitadas (apenas visualização)
- O controle de acesso detalhado (criar/editar/deletar) já estava implementado corretamente no CredentialResource

**💡 Solução aplicada:**
```php
// ANTES - app/Models/User.php
public function canAccessPanel(Panel $panel): bool
{
    if ($this->email === 'admin@admin.com') {
        return true;
    }
    return $this->hasRole('super_admin') || $this->hasRole('admin');
}

// DEPOIS - app/Models/User.php  
public function canAccessPanel(Panel $panel): bool
{
    if ($this->email === 'admin@admin.com') {
        return true;
    }
    return $this->hasRole('super_admin') || $this->hasRole('admin') || $this->hasRole('consulta');
}
```

**✅ Validação:**
- Teste criado para verificar acesso de usuários 'consulta' ao painel
- Usuários 'consulta' podem acessar painel mas não podem criar/editar/deletar
- Todos os testes passando (8 testes, 19 assertions)

**📚 Lição aprendida:**
- No Filament, `canAccessPanel()` controla acesso GERAL ao painel
- Controle granular de permissões deve ser feito nos Resources individuais
- Sempre testar todos os tipos de usuários após implementar sistema de roles
- Separar claramente: acesso ao painel vs. permissões específicas de recursos

**🔄 Ações preventivas:**
- Sempre criar testes para cada tipo de role implementado
- Documentar claramente qual método controla qual tipo de acesso
- Revisar `canAccessPanel()` sempre que novos roles forem adicionados

---

### ❌ ERRO: Super admin não consegue acessar painel de gerenciamento de usuários

**📅 Data:** 20/11/2025  
**🔧 Contexto:** Após implementação do sistema RBAC, super admin não via o menu/painel de usuários

**🚨 Problema identificado:**
- Tarefa #12 estava marcada como "done" mas não foi completamente implementada
- Permissões de usuários (`view_users`, `create_users`, etc.) não foram criadas
- Recursos UserResource duplicados causando conflitos
- Super admin sem as permissões necessárias para acessar gestão de usuários

**💡 Solução aplicada:**
1. **Criação das permissões de usuários:**
```php
$userPermissions = [
    'view_users', 'view_any_users', 'create_users', 'update_users',
    'delete_users', 'delete_any_users', 'restore_users', 'restore_any_users',
    'force_delete_users', 'force_delete_any_users', 'replicate_users', 'reorder_users'
];
```

2. **Atribuição das permissões ao super admin:**
```php
$superAdmin = User::where('email', 'admin@admin.com')->first();
$superAdmin->givePermissionTo($userPermissions);
```

3. **Resolução de conflito de recursos duplicados:**
- Removido `app/Filament/Resources/Users/UserResource.php` (duplicado)
- Mantido `app/Filament/Resources/UserResource.php` (principal com controle de acesso)
- Removidas todas as páginas, schemas e tables duplicadas

**✅ Validação:**
- Super admin agora tem todas as 12 permissões de usuários
- `UserResource::canViewAny()` retorna true para super admin
- Rotas `/admin/users` funcionando corretamente
- Conflito de recursos resolvido

**📚 Lição aprendida:**
- No Filament, permissões devem ser criadas ANTES de marcar resource como implementado
- Evitar duplicação de Resources - usar apenas uma implementação
- Sempre testar acesso real ao painel após implementar recursos
- Verificar se o método `canViewAny()` está implementado nos Resources
- Não confiar apenas no status "done" das tarefas - fazer validação prática

**🔄 Ações preventivas:**
- Criar script de verificação de permissões para todos os Resources
- Implementar testes automatizados para acesso de diferentes roles
- Documentar claramente quais permissões cada Resource precisa
- Validar implementação completa antes de marcar tarefa como "done"

---

### ❌ ERRO: "Class Filament\Tables\Actions\EditAction not found" e Botão de Edição Invisível no UserResource

**📅 Data:** 20/11/2025  
**🔧 Contexto:** Ao acessar o Painel de Usuários (`/admin/users`), ocorreu erro de classe não encontrada para `EditAction`. Após correção do erro de importação, o botão de edição permaneceu invisível, embora a funcionalidade de edição fosse acessível clicando na linha.

**🚨 Problema identificado:**
- **Inconsistência de Namespace:** O projeto, embora declare Filament 4.x no `composer.json`, utiliza classes do namespace `Filament\Actions` (comum em Filament 3.x) em vez de `Filament\Tables\Actions` para ações de tabela. Isso causou erros `Class not found`.
- **Botão Invisível:** Mesmo após corrigir o namespace da `EditAction` e usar a estrutura `->actions([...])` (herdada de `CredentialResource`), o botão de edição não era renderizado visualmente na tabela de usuários. A funcionalidade de edição, porém, era disparada ao clicar na linha do registro.

**💡 Solução aplicada:**
1.  **Padronização do Namespace de Actions:** Alinhado com `CredentialResource`, todas as Actions nos Resources de Usuário foram configuradas para usar o namespace `Filament\Actions`.
    ```php
    // DEPOIS (UserResource.php e CredentialResource.php)
    use Filament\Actions\Action; // Para ações customizadas como 'edit'
    use Filament\Actions\EditAction; // Para a ação EditAction padrão
    use Filament\Actions\DeleteAction; // Para a ação DeleteAction padrão
    use Filament\Actions\BulkActionGroup;
    use Filament\Actions\DeleteBulkAction;
    ```
2.  **Uso de Ação Customizada para Edição:** Para contornar o problema de renderização do `EditAction` padrão, uma `Action` customizada foi implementada para o botão de edição.
    ```php
    // DEPOIS (UserResource.php)
    // No método table():
    ->actions([
        Action::make('edit')
            ->label('Editar') // Adicionado o label para tradução
            ->icon('heroicon-o-pencil')
            ->url(fn ($record): string => Pages\EditUser::getUrl(['record' => $record])),
        DeleteAction::make(),
    ])
    ```

**✅ Validação:**
- Erro `Class not found` para ações foi resolvido.
- O botão "Editar" agora é visível e funcional na tabela de usuários.
- O botão "Deletar" também é visível e funcional.
- A página de edição abre corretamente.
- A tradução do botão "Edit" para "Editar" foi aplicada.

**📚 Lição aprendida:**
- **Verificar Namespace de Actions:** Sempre confirmar o namespace correto para as Actions (`Filament\Actions` vs `Filament\Tables\Actions`), especialmente em projetos que podem estar usando versões mistas ou customizadas do Filament. O `composer.json` indica Filament 4, mas o projeto utiliza o namespace `Filament\Actions`, que é mais comum em Filament 3.
- **Renderização de Botões:** Se um botão de ação não renderizar, mas a funcionalidade de clique na linha funcionar, a causa pode ser um problema específico de renderização do componente de ação padrão. Uma solução é criar uma `Action` customizada, definindo explicitamente o `label`, `icon` e `url`.
- **Clareza na Intenção:** Certificar-se de que a intenção da ação é clara e visível para o usuário, seja através do ícone ou do texto do botão.
- **Priorizar "o que funciona":** Em caso de inconsistência de versões ou comportamentos inesperados, seguir a lógica de implementação que comprovadamente funciona em outras partes do projeto (e.g., `CredentialResource`).

**🔄 Ações preventivas:**
- Criar um "template" de Resource com as configurações de Actions já padronizadas para o projeto.
- Testar a visibilidade e funcionalidade de todos os botões CRUD após qualquer alteração nos Resources ou assets.
- Utilizar `php artisan tinker --execute="echo class_exists('Filament\\Actions\\Action') ? 'OK' : 'ERRO';"` para verificar a existência de classes em tempo de execução.