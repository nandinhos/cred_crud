# Lições Aprendidas - Laravel 12 + Filament 4

## 📚 Índice
- [Problemas Resolvidos](#problemas-resolvidos)
- [Migrações e Atualizações](#migrações-e-atualizações)
- [Configurações Críticas](#configurações-críticas)
- [Comandos Salvadores](#comandos-salvadores)
- [Prevenção de Problemas](#prevenção-de-problemas)
- [Melhorias e Customizações](#melhorias-e-customizações)

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

### ❌ ERRO: Falha de Conexão do MCP com Banco Docker
**Problema:** Ferramenta MCP `database-schema` falha ao conectar com host `mysql`.

**Causa:** O servidor MCP roda fora da rede Docker e não consegue resolver o hostname do container.

**✅ SOLUÇÃO:**
- Usar `vendor/bin/sail artisan schema:dump` para gerar arquivo SQL.
- Ler o arquivo `database/schema/mysql-schema.sql` diretamente.
- Para queries diretas, usar `vendor/bin/sail artisan tinker`.

**Lição:** Em ambientes Dockerizados, preferir ferramentas que operam via CLI do container (Sail) ou leitura de arquivos gerados.

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

---

## 📅 Data: 21/11/2025

### ❌ ERRO: Estilos Tailwind CSS não carregam em páginas customizadas do Filament

#### 🔴 Sintomas
- View Blade criada com classes Tailwind CSS puras não exibe estilos
- Card aparece sem formatação, apenas conteúdo HTML puro
- Classes como `bg-white`, `rounded-xl`, `shadow-lg` não são aplicadas
- Cache do navegador limpo não resolve o problema
- `npm run build` executado mas estilos não aparecem

#### 🔍 Causa Raiz
O Filament 4 possui seu próprio sistema de estilos e não processa automaticamente classes Tailwind CSS em views customizadas. O Filament usa seus componentes Blade nativos que já vêm estilizados com o tema do painel.

**Problema específico:**
- Views customizadas usando `<x-filament-panels::page>` não incluem automaticamente o CSS do Tailwind buildado
- Filament prioriza seus próprios componentes sobre HTML/Tailwind puro
- Classes Tailwind em elementos HTML puros não são processadas pelo sistema de estilos do Filament

#### ✅ Solução

**1. Usar componentes nativos do Filament em vez de HTML + Tailwind puro:**

```blade
<!-- ❌ ERRADO - HTML puro com classes Tailwind -->
<div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
    <h2 class="text-xl font-bold">Título</h2>
    <p class="text-sm text-gray-500">Conteúdo</p>
</div>

<!-- ✅ CORRETO - Componentes Filament -->
<x-filament::section>
    <x-slot name="heading">
        Título
    </x-slot>
    
    <div class="text-sm text-gray-500 dark:text-gray-400">
        Conteúdo
    </div>
</x-filament::section>
```

**2. Padrões de componentes Filament:**

```blade
<!-- Section com heading -->
<x-filament::section>
    <x-slot name="heading">Título da Seção</x-slot>
    Conteúdo aqui
</x-filament::section>

<!-- Badge com cores -->
<x-filament::badge color="success">Ativo</x-filament::badge>
<x-filament::badge color="danger">Vencido</x-filament::badge>
<x-filament::badge color="warning">Pendente</x-filament::badge>

<!-- Ícones -->
<x-filament::icon icon="heroicon-o-home" class="h-6 w-6" />

<!-- Grid com classes Filament -->
<div class="grid gap-6 md:grid-cols-2">
    <div class="flex gap-x-3">
        <x-filament::icon icon="heroicon-o-user" class="h-6 w-6 text-gray-400" />
        <div class="grid gap-y-1">
            <div class="text-sm font-medium text-gray-950 dark:text-white">Label</div>
            <div class="text-sm text-gray-500 dark:text-gray-400">Valor</div>
        </div>
    </div>
</div>
```

**3. Classes que funcionam com componentes Filament:**
- `grid`, `gap-6`, `md:grid-cols-2` - Layout grid
- `flex`, `gap-x-3` - Flexbox
- `text-sm`, `font-medium` - Tipografia
- `text-gray-950 dark:text-white` - Cores com dark mode
- `h-6 w-6` - Tamanhos

#### 📚 Boas Práticas

1. **Sempre usar componentes Filament primeiro:**
   - `<x-filament::section>` para seções
   - `<x-filament::badge>` para badges
   - `<x-filament::icon>` para ícones

2. **Verificar componentes disponíveis:**
   ```bash
   grep -r "x-filament::" vendor/filament/filament/resources/views/components/
   ```

3. **Limpar cache após mudanças em views:**
   ```bash
   vendor/bin/sail artisan view:clear
   vendor/bin/sail artisan cache:clear
   ```

4. **Testar imediatamente após mudanças:**
   - Não confiar apenas em "npm run build"
   - Acessar a página no navegador e inspecionar elementos
   - Verificar se as classes estão sendo aplicadas no HTML renderizado

#### 🎓 Lições Aprendidas

- **Filament != Tailwind puro**: Filament usa componentes próprios, não aceita Tailwind arbitrário
- **Verificar antes de buildar**: Testar a abordagem antes de executar builds desnecessários
- **Seguir convenções do framework**: Usar componentes nativos garante compatibilidade e estilos
- **Documentação é essencial**: Consultar docs do Filament para componentes disponíveis
- **Testar visualmente**: Não assumir que código está funcionando sem ver no navegador

#### 🔗 Referências
- Documentação Filament 4: https://filamentphp.com/docs/4.x/panels/pages
- Componentes Blade do Filament: `vendor/filament/filament/resources/views/components/`

---

## 🎨 Melhorias e Customizações

### ✅ Aplicação de Estilos Visuais no Filament 4

**Data:** 2024
**Contexto:** Layout do Filament estava muito simples, sem definição clara entre labels e dados, faltando cores e contraste visual.

#### 🎯 Soluções Implementadas

**1. Configuração de Cores Personalizadas**
```php
// app/Providers/Filament/AdminPanelProvider.php
->colors([
    'primary' => Color::Blue,
    'danger' => Color::Red,
    'gray' => Color::Slate,
    'info' => Color::Cyan,
    'success' => Color::Green,
    'warning' => Color::Orange,
])
->font('Inter')
```

**2. Adição de Ícones aos Campos de Formulário**
```php
// Exemplos de ícones aplicados
TextInput::make('name')
    ->prefixIcon('heroicon-o-user')
    ->label('Nome de Guerra')

Select::make('type')
    ->prefixIcon('heroicon-o-document-text')
    ->label('Tipo de Documento')

DatePicker::make('concession')
    ->prefixIcon('heroicon-o-calendar-days')
    ->label('Data de Concessão')
```

**3. Seções Organizadas com Ícones e Descrições**
```php
Section::make('Informações da Credencial')
    ->description('Dados principais da credencial de segurança')
    ->icon('heroicon-o-shield-check')
    ->collapsible()
    ->schema([...])
```

**4. Arquivo CSS Customizado**
```css
/* resources/css/filament-custom.css */
@layer components {
    /* Labels em negrito com melhor contraste */
    .fi-fo-field-wrp-label label {
        @apply font-semibold text-gray-800 dark:text-gray-200 text-sm;
    }

    /* Títulos de seções em azul */
    .fi-section-header-heading {
        @apply text-lg font-bold text-blue-600 dark:text-blue-400;
    }

    /* Cabeçalhos de tabelas com destaque */
    .fi-ta-header-cell {
        @apply font-bold text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-800;
    }
}
```

**5. Integração do CSS com Tailwind**
```css
/* resources/css/app.css */
@import './filament-custom.css';

@tailwind base;
@tailwind components;
@tailwind utilities;
```

#### 📝 Ícones Aplicados por Campo

**Credenciais:**
- Usuário Responsável: `heroicon-o-user`
- FSCS: `heroicon-o-identification`
- Tipo de Documento: `heroicon-o-document-text`
- Nível de Sigilo: `heroicon-o-lock-closed`
- Número da Credencial: `heroicon-o-hashtag`
- Data de Concessão: `heroicon-o-calendar-days`
- Data de Validade: `heroicon-o-clock`

**Usuários:**
- Nome de Guerra: `heroicon-o-user`
- Nome Completo: `heroicon-o-identification`
- Posto/Graduação: `heroicon-o-star`
- Unidade Militar: `heroicon-o-building-office`
- E-mail: `heroicon-o-envelope`
- Senha: `heroicon-o-lock-closed`
- Perfis: `heroicon-o-user-group`

**Seções:**
- Informações da Credencial: `heroicon-o-shield-check`
- Datas: `heroicon-o-calendar`
- Informações do Usuário: `heroicon-o-user-circle`
- Perfis e Permissões: `heroicon-o-shield-check`

#### ✅ Benefícios Obtidos

1. **Melhor Hierarquia Visual**: Labels e dados agora têm contraste claro
2. **Navegação Intuitiva**: Ícones facilitam identificação rápida dos campos
3. **Organização**: Seções colapsáveis mantêm formulários limpos
4. **Acessibilidade**: Cores e contrastes melhorados
5. **Profissionalismo**: Layout mais polido e moderno

#### 🔧 Comandos Utilizados

```bash
# Compilar assets do Tailwind
vendor/bin/sail npm run build

# Limpar cache do Filament
vendor/bin/sail artisan filament:cache-components

# Atualizar assets do Filament
vendor/bin/sail artisan filament:upgrade

# Limpar views
vendor/bin/sail artisan view:clear
```

#### ⚠️ Lições Importantes

1. **@import deve vir antes do Tailwind**: Ao usar `@import` no CSS, ele deve estar antes das diretivas `@tailwind`
2. **Usar @layer components**: Classes customizadas devem estar dentro de `@layer components` para evitar erros de compilação
3. **Cores do Tailwind**: Usar cores padrão do Tailwind (blue-600) ao invés de variáveis personalizadas (primary-600) para evitar erros
4. **Rebuild necessário**: Sempre rodar `npm run build` após mudanças em CSS
5. **Cache do Filament**: Limpar cache com `filament:cache-components` após mudanças estruturais

#### 📚 Referências

- Documentação Filament 4: https://filamentphp.com/docs
- Heroicons: https://heroicons.com
- Tailwind CSS: https://tailwindcss.com/docs

---

## 14. Verificação Completa do Sistema com Scripts Automatizados

**Data:** 21/11/2024  
**Problema:** Necessidade de verificar se o sistema Filament está 100% funcional antes de avançar para próximas tarefas.

**❌ Desafio:**
- Verificar múltiplos componentes manualmente é demorado e sujeito a erros
- Necessário validar: banco de dados, resources, rotas, autorização, assets, testes
- Ambiente Docker (Sail) dificulta acesso direto ao navegador

**✅ SOLUÇÃO:**
Criação de script PHP automatizado que verifica todos os componentes do sistema:

```php
// tmp_rovodev_visual_test.php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Verificações realizadas:
// 1. Conexão com banco de dados
// 2. Contagem de registros em tabelas
// 3. Usuários e roles
// 4. Credenciais cadastradas
// 5. Resources Filament (class_exists)
// 6. Rotas (router->getRoutes()->match)
// 7. Policies e Observers
// 8. Assets compilados (file_exists)
```

**📊 Resultados da Verificação:**
- ✅ 52 testes automatizados passando (102 assertions)
- ✅ Banco de dados: 10 usuários, 64 credenciais
- ✅ Todos os Resources Filament funcionando
- ✅ Todas as rotas administrativas disponíveis
- ✅ Assets compilados (CSS: 543KB, JS: 32KB total)
- ✅ Autorização por roles implementada
- ✅ Observers ativos

**💡 Melhores Práticas Identificadas:**

1. **Scripts de Verificação Automatizada**
   - Criar scripts PHP que usam o bootstrap do Laravel
   - Verificar componentes programaticamente
   - Gerar relatórios formatados em Markdown

2. **Checklist de Verificação Completa**
   ```
   □ Ambiente Docker/Sail rodando
   □ Banco de dados conectado e populado
   □ Resources Filament carregados
   □ Rotas registradas corretamente
   □ Assets compilados
   □ Testes automatizados passando
   □ Autorização funcionando
   ```

3. **Executar Testes Antes de Marcar Tarefa como Concluída**
   ```bash
   vendor/bin/sail artisan test
   vendor/bin/sail php tmp_rovodev_visual_test.php
   ```

4. **Limpar Arquivos Temporários**
   - Usar prefixo `tmp_rovodev_` para fácil identificação
   - Deletar após uso para manter workspace limpo

**🎯 Benefícios:**
- ✅ Verificação rápida e confiável (< 5 segundos)
- ✅ Detecção precoce de problemas
- ✅ Documentação automática do estado do sistema
- ✅ Confiança para avançar para próximas tarefas

**🔄 Ações preventivas:**
- Sempre verificar o sistema após grandes mudanças
- Manter scripts de verificação atualizados
- Incluir verificação no CI/CD pipeline
- Documentar estado do sistema antes de modificações

**📚 Referências:**
- Laravel Artisan Testing: https://laravel.com/docs/12.x/testing
- Pest PHP: https://pestphp.com/docs

---

## 15. Implementação de Regras de Negócio Complexas com Migração de Dados

**Data:** 21/11/2024  
**Problema:** Sistema tinha 64 credenciais distribuídas de forma incorreta entre 10 usuários, com sigilo inadequado para os tipos de documentos.

**❌ Situação Inicial:**
- 4 usuários com múltiplas credenciais (Admin: 38, João: 10, Ana: 12, Renan: 3)
- 5 usuários sem credenciais
- 21 TCMS com sigilo R ou S (incorreto - deveria ser AR)
- Sem controle de histórico de credenciais
- Regra de negócio não documentada adequadamente

**✅ SOLUÇÃO IMPLEMENTADA:**

### 1. Atualização da Documentação
```markdown
### Regras Gerais
- CADA USUÁRIO PODE TER APENAS UMA CREDENCIAL ATIVA POR VEZ
- Credenciais antigas ficam no histórico (soft delete)

### Níveis de Sigilo
- CRED: R (Reservado) ou S (Secreto)
- TCMS: AR (Acesso Restrito)
```

### 2. Atualização do Enum CredentialSecrecy
```php
enum CredentialSecrecy: string
{
    case ACESSO_RESTRITO = 'AR';  // Novo!
    case RESERVADO = 'R';
    case SECRETO = 'S';
    
    // Novos métodos de validação
    public static function optionsForType(CredentialType $type): array
    public static function isValidForType(string $secrecy, CredentialType $type): bool
}
```

### 3. Novos Relacionamentos no Model User
```php
// Credencial ativa (apenas 1)
public function activeCredential(): HasMany

// Histórico completo (incluindo deletadas)
public function credentialHistory(): HasMany
```

### 4. Script de Migração de Dados
Criado script PHP que:
- ✅ Cria backup automático do banco
- ✅ Corrige sigilo de 21 TCMS (R/S → AR)
- ✅ Move 59 credenciais excedentes para histórico
- ✅ Cria 5 novas credenciais TCMS para usuários sem credencial
- ✅ Valida regras de negócio ao final
- ✅ Usa transaction com rollback em caso de erro

### 5. Atualização de Testes
```php
test('forSecrecy retorna cor correta para Acesso Restrito', function () {
    expect(BadgeColor::forSecrecy('AR'))->toBe('info');
});
```

**📊 Resultados:**
- ✅ 10 usuários com exatamente 1 credencial ativa cada
- ✅ 59 credenciais preservadas no histórico
- ✅ 8 TCMS com sigilo AR (correto)
- ✅ 2 CRED com sigilo R ou S (correto)
- ✅ 53 testes passando (103 assertions)
- ✅ Zero perda de dados históricos

**💡 Melhores Práticas Identificadas:**

1. **Sempre Criar Backup Antes de Migração**
   ```bash
   mysqldump -u user -p database > backup_$(date +%Y%m%d_%H%M%S).sql
   ```

2. **Usar Transactions para Migrations de Dados**
   ```php
   DB::beginTransaction();
   try {
       // alterações
       DB::commit();
   } catch (\Exception $e) {
       DB::rollBack();
   }
   ```

3. **Documentar Regras de Negócio ANTES do Código**
   - Evita retrabalho
   - Facilita validação com stakeholders
   - Serve como contrato entre equipes

4. **Preservar Histórico com Soft Deletes**
   - Nunca delete dados permanentemente sem necessidade
   - Histórico é valioso para auditoria e análise
   - Use `withTrashed()` para consultas históricas

5. **Validar Regras Programaticamente**
   ```php
   // No final do script de migração
   $usersWithMultiple = User::withCount('credentials')
       ->having('credentials_count', '>', 1)->count();
   if ($usersWithMultiple === 0) {
       echo "✅ Regra validada\n";
   }
   ```

6. **Scripts de Migração como Código Descartável**
   - Use prefixo `tmp_rovodev_` para fácil identificação
   - Documente no commit o que foi feito
   - Delete após execução bem-sucedida

7. **Enums com Validação Contextual**
   ```php
   // Validar sigilo baseado no tipo
   CredentialSecrecy::isValidForType('AR', CredentialType::TCMS); // true
   CredentialSecrecy::isValidForType('AR', CredentialType::CRED); // false
   ```

**🎯 Benefícios:**
- ✅ Sistema 100% conforme regras de negócio
- ✅ Histórico completo preservado
- ✅ Validação automática de sigilo por tipo
- ✅ Zero impacto em funcionalidades existentes
- ✅ Testes garantem qualidade

**🔄 Ações preventivas:**
- Documentar regras de negócio desde o início
- Criar validações no nível de aplicação e banco
- Implementar observers para manter histórico automaticamente
- Adicionar testes de integração para regras de negócio

**📚 Referências:**
- Laravel Soft Deletes: https://laravel.com/docs/12.x/eloquent#soft-deleting
- Database Transactions: https://laravel.com/docs/12.x/database#database-transactions
- Enum Validation: https://www.php.net/manual/en/language.enumerations.php


---

## 🔐 Policies vs Permissions vs Roles - Precedência e Conflitos

**Data:** 2025-11-23  
**Contexto:** Sistema de backup com RBAC completo  
**Problema:** Botões de criar/editar/deletar sumiram após implementar `canAccess()` nos Resources

### ❌ PROBLEMA:

Ao implementar controle de acesso RBAC para o perfil "consulta", os botões de ação (Criar, Editar, Deletar) desapareceram para **todos os usuários**, incluindo admin e super_admin.

**Código problemático:**
```php
// UserResource.php
public static function canAccess(): bool
{
    return auth()->user()->hasRole(['admin', 'super_admin']);
}

// UserPolicy.php
public function create(User $user): bool
{
    return $user->can('create_users'); // ❌ Permissão não existe!
}
```

### 🔍 CAUSA RAIZ:

**1. Precedência de Verificação no Filament:**
- O Filament verifica **Policy primeiro**, depois os métodos do Resource
- Se a Policy retornar `false`, o Resource nunca é consultado

**2. Permissões Inexistentes:**
- A Policy verificava permissões que nunca foram criadas no seeder:
  - `create_users` ❌
  - `edit_users` ❌
  - `delete_users` ❌

**3. Diferença entre `can()` e `hasRole()`:**
- `$user->can('permission')` - Verifica permissão específica (Spatie Permission)
- `$user->hasRole('role')` - Verifica se usuário tem uma role

### ✅ SOLUÇÃO:

**1. Corrigir a Policy para usar `hasRole()` ao invés de `can()`:**

```php
// app/Policies/UserPolicy.php
public function create(User $user): bool
{
    return $user->hasRole(['admin', 'super_admin']);
}

public function update(User $user, User $model): bool
{
    return $user->hasRole(['admin', 'super_admin']);
}

public function delete(User $user, User $model): bool
{
    // Não pode deletar a si mesmo
    if ($user->id === $model->id) {
        return false;
    }
    
    return $user->hasRole(['admin', 'super_admin']);
}
```

**2. Adicionar métodos específicos no Resource (redundância segura):**

```php
// app/Filament/Resources/UserResource.php
public static function canCreate(): bool
{
    $user = auth()->user();
    return $user && $user->hasRole(['admin', 'super_admin']);
}

public static function canEdit($record): bool
{
    $user = auth()->user();
    return $user && $user->hasRole(['admin', 'super_admin']);
}

public static function canDelete($record): bool
{
    $user = auth()->user();
    return $user && $user->hasRole(['admin', 'super_admin']);
}
```

**3. Manter `shouldRegisterNavigation()` para ocultar do menu:**

```php
public static function shouldRegisterNavigation(): bool
{
    $user = auth()->user();
    return $user && $user->hasRole(['admin', 'super_admin']);
}
```

### 📋 CHECKLIST DE VERIFICAÇÃO:

Sempre que implementar RBAC em um Resource Filament:

- [ ] ✅ Verificar se as **permissões** usadas na Policy existem no seeder
- [ ] ✅ Decidir: usar `hasRole()` OU `can()` (não misturar)
- [ ] ✅ Implementar `shouldRegisterNavigation()` para ocultar menu
- [ ] ✅ Implementar `canAccess()` para bloquear acesso direto via URL
- [ ] ✅ Implementar `canCreate()`, `canEdit()`, `canDelete()` se necessário
- [ ] ✅ Testar com cada perfil (admin, super_admin, consulta)
- [ ] ✅ Limpar caches após mudanças: `filament:clear-cached-components`

### 🎯 ORDEM DE PRECEDÊNCIA (Filament):

```
1. Policy (se existir)
   ↓ se false, para aqui
2. Resource::canCreate()/canEdit()/canDelete()
   ↓ se false, para aqui
3. Resource::canAccess()
   ↓ se false, para aqui
4. Botão/Ação é exibida
```

### 💡 BOAS PRÁTICAS:

**✅ RECOMENDADO:**
```php
// Policy: Validação de negócio específica
public function create(User $user): bool
{
    return $user->hasRole(['admin', 'super_admin']);
}

// Resource: Controle de acesso geral
public static function canAccess(): bool
{
    return auth()->user()?->hasRole(['admin', 'super_admin']) ?? false;
}
```

**❌ EVITAR:**
```php
// Misturar hasRole() e can() sem certeza das permissões
public function create(User $user): bool
{
    return $user->can('create_users'); // Permissão existe?
}

// Bloquear tudo apenas com canAccess()
public static function canAccess(): bool
{
    return false; // Bloqueia criar/editar/deletar também!
}
```

### 🧪 COMANDOS DE DEBUG:

```bash
# Verificar permissões de um usuário
php artisan tinker
$user = User::find(1);
$user->permissions->pluck('name');
$user->roles->pluck('name');

# Limpar caches do Filament
php artisan filament:clear-cached-components
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

### 📊 RESULTADO:

- ✅ Botões aparecem para admin e super_admin
- ✅ Menu oculto para perfil "consulta"
- ✅ Acesso direto via URL bloqueado para "consulta"
- ✅ Policies usando roles ao invés de permissões inexistentes
- ✅ Redundância segura entre Policy e Resource

### 🔗 RELACIONADO:

- Issue #7.4 - Sistema de Backup e melhorias UX/RBAC
- Arquivo: `app/Policies/UserPolicy.php`
- Arquivo: `app/Filament/Resources/UserResource.php`
- Arquivo: `app/Filament/Resources/Credentials/CredentialResource.php`

