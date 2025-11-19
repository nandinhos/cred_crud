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