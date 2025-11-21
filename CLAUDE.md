# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Sistema de gerenciamento de credenciais de segurança desenvolvido com **Laravel 12** e **Filament 4**. O sistema oferece CRUD completo de credenciais, painel administrativo moderno, e sistema robusto de permissões baseado em roles (RBAC).

## Technology Stack

- **Laravel**: 12.39.0 (usando estrutura Laravel 10 - sem migração para nova estrutura)
- **Filament**: 4.2.2 (admin panel)
- **PHP**: 8.3+ (requerido)
- **Database**: MySQL 8.0
- **Frontend**: Livewire 3, Alpine.js 3, TailwindCSS 3
- **Testing**: Pest PHP 3
- **Permissions**: Spatie Permission + Filament Shield
- **Environment**: Laravel Sail (Docker)

## Development Environment

### Docker/Sail Commands

**CRITICAL**: All commands MUST be executed through Laravel Sail (Docker):

```bash
# Start services
vendor/bin/sail up -d

# Run Artisan commands
vendor/bin/sail artisan [command]

# Run tests
vendor/bin/sail artisan test
vendor/bin/sail artisan test --filter=[testName]
vendor/bin/sail artisan test tests/Feature/ExampleTest.php

# Build assets
vendor/bin/sail npm install
vendor/bin/sail npm run build   # For production
vendor/bin/sail npm run dev     # For development

# Code formatting
vendor/bin/sail bin pint --dirty

# Access container
docker-compose exec laravel.test bash

# Database backup
docker-compose exec laravel.test mysqldump -u sail -psail cred_crud > backup.sql
```

### Default Credentials

- **URL**: `http://localhost/admin`
- **Quick Login**: `http://localhost/login-admin` (auto-login route)
- **Admin Email**: `admin@admin.com`
- **Password**: `password`

## Architecture & Key Patterns

### Laravel 10 Structure (Important!)

This project was upgraded from Laravel 10 to Laravel 12 **without migrating to the new streamlined structure**. This is intentional and recommended by Laravel:

- Middleware: `app/Http/Middleware/` and registered in `app/Http/Kernel.php`
- Service Providers: `app/Providers/`
- No `bootstrap/app.php` application configuration
- Exception handling: `app/Exceptions/Handler.php`
- Console commands: `app/Console/Kernel.php`

### Database Schema

**Main Tables:**
- `users` - User authentication and profiles
- `credentials` - Security credentials (FSCS, name, secrecy level, dates)
- `roles` - User roles (super_admin, admin, operador, consulta)
- `permissions` - Granular permissions via Spatie Permission
- `model_has_roles` / `model_has_permissions` - Permission relationships

**Key Relationships:**
- `User` hasMany `Credential` (user_id foreign key with SET NULL on delete)
- `User` belongsToMany `Role` (via Spatie Permission)
- `User` belongsToMany `Permission` (via Spatie Permission)

### Role-Based Access Control (RBAC)

**Roles Hierarchy:**
1. `super_admin` - Full access to everything
2. `admin` - Full access to credentials and users
3. `operador` - Can manage credentials (not implemented fully)
4. `consulta` - Read-only access to credentials and users

**Access Control Pattern:**
```php
// Model: User.php
public function canAccessPanel(Panel $panel): bool
{
    // Controls GENERAL panel access
    if ($this->email === 'admin@admin.com') return true;
    return $this->hasRole(['super_admin', 'admin', 'consulta']);
}

// Resource: CredentialResource.php / UserResource.php
public static function canAccess(): bool
{
    // Controls access to resource menu
    return auth()->user()?->hasRole(['admin', 'super_admin', 'consulta']) ?? false;
}

public static function canCreate(): bool
{
    // Granular permission for creating records
    return auth()->user()?->hasRole(['admin', 'super_admin']) ?? false;
}
```

**Key Distinction:**
- `canAccessPanel()` in User model → General panel access
- `canAccess()` in Resources → Visibility of resource menu
- `canCreate()`, `canEdit()`, `canDelete()` → Granular permissions per action

### Filament 4 Specific Patterns

**CRITICAL Namespace Changes from Filament 3:**

```php
// ✅ CORRECT for Filament 4 in this project
use Filament\Actions\Action;          // For custom actions
use Filament\Actions\EditAction;       // Standard edit action
use Filament\Actions\DeleteAction;     // Standard delete action
use Filament\Actions\BulkActionGroup;
use Filament\Schemas\Schema;           // NOT Forms\Form
use Filament\Schemas\Components\Section; // Layout components

// ❌ WRONG - Do not use these
use Filament\Tables\Actions\EditAction;  // Does not exist in this project
use Filament\Forms\Form;                 // Use Schemas\Schema instead
use Filament\Forms\Components\Section;   // Use Schemas\Components\Section
```

**Resource Structure:**
```php
class ExampleResource extends Resource
{
    // Form uses Schema, not Form
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            \Filament\Schemas\Components\Section::make('Title')
                ->schema([/* form fields */])
                ->columns(2),
        ]);
    }

    // Table actions pattern
    public static function table(Table $table): Table
    {
        return $table
            ->actions([
                EditAction::make(),    // From Filament\Actions
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('delete')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->delete()),
                ]),
            ]);
    }

    // Pages structure
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExample::route('/'),
            'create' => Pages\CreateExample::route('/create'),
            'edit' => Pages\EditExample::route('/{record}/edit'),
        ];
    }
}
```

**File Structure for Resources:**
```
app/Filament/Resources/
├── CredentialResource.php              # Main resource
├── Credentials/
│   ├── Pages/
│   │   ├── ListCredentials.php
│   │   ├── CreateCredential.php
│   │   └── EditCredential.php
│   ├── Schemas/
│   │   └── CredentialForm.php
│   └── Tables/
│       └── CredentialsTable.php
└── UserResource.php
    └── UserResource/
        └── Pages/
            ├── ListUsers.php
            ├── CreateUser.php
            └── EditUser.php
```

### Credential Business Logic

**Validation Rules:**
- FSCS must be unique (ignoring soft-deleted records)
- Validity date must be in the future (`after: today`)
- Secrecy levels: 'R' (Reservado) or 'S' (Secreto)
- Soft delete enabled for audit trail

**Visual Indicators:**
```php
// Validity status colors
- Red (danger): Expired credentials
- Yellow (warning): Expiring within 30 days
- Green (success): Valid credentials
```

## Common Tasks

### Creating New Filament Resources

```bash
# Use Filament artisan commands
vendor/bin/sail artisan make:filament-resource EntityName --generate

# With specific options
vendor/bin/sail artisan make:filament-resource EntityName \
    --generate \
    --soft-deletes \
    --no-interaction
```

### Running Tests

```bash
# Run all tests
vendor/bin/sail artisan test

# Run specific test file
vendor/bin/sail artisan test tests/Feature/RoleAuthorizationTest.php

# Run tests matching pattern
vendor/bin/sail artisan test --filter=testName

# Run with coverage (if configured)
vendor/bin/sail artisan test --coverage
```

### Database Operations

```bash
# Run migrations
vendor/bin/sail artisan migrate

# Run specific seeder
vendor/bin/sail artisan db:seed --class=DatabaseSeeder

# Fresh database with seeding
vendor/bin/sail artisan migrate:fresh --seed

# Create backup before changes
docker-compose exec laravel.test mysqldump -u sail -psail cred_crud > backup_$(date +%Y%m%d_%H%M%S).sql
```

### Asset Compilation

```bash
# Build for production
vendor/bin/sail npm run build

# Development mode with hot reload
vendor/bin/sail npm run dev

# If Vite manifest error occurs
vendor/bin/sail npm install
vendor/bin/sail npm run build
```

### Cache Management

```bash
# Clear all caches
vendor/bin/sail artisan optimize:clear

# Individual cache clear
vendor/bin/sail artisan config:clear
vendor/bin/sail artisan cache:clear
vendor/bin/sail artisan route:clear
vendor/bin/sail artisan view:clear

# Optimize for production
vendor/bin/sail composer dump-autoload --optimize
vendor/bin/sail artisan config:cache
vendor/bin/sail artisan route:cache
```

## Important Conventions

### Commit Messages

All commits MUST be in Portuguese using Conventional Commits:

```bash
feat: adiciona nova funcionalidade
fix: corrige bug no sistema de login
docs: atualiza documentação
refactor: refatora código do UserResource
test: adiciona testes para autenticação
```

### Testing Requirements

- Every change MUST be programmatically tested
- Write Pest tests (not PHPUnit syntax)
- Tests must cover: happy paths, failure paths, edge cases
- Run minimum necessary tests before committing
- Test files: `tests/Feature/` and `tests/Unit/`
- **Example:**
  ```php
  it('can list credentials', function () {
      $user = User::factory()->create();
      $user->assignRole('admin');
      
      $this->actingAs($user)
          ->get(CredentialResource::getUrl('index'))
          ->assertSuccessful();
  });
  ```

### Code Formatting

```bash
# Always run Pint before committing
vendor/bin/sail bin pint --dirty

# Format all files
vendor/bin/sail bin pint
```

## Known Issues & Solutions

### Issue: Vite Manifest Not Found (Error 500)

**Cause:** Frontend assets not compiled after dependency updates

**Solution:**
```bash
docker-compose exec laravel.test npm install
docker-compose exec laravel.test npm run build
ls -la public/build/manifest.json  # Verify
```

### Issue: 403 Forbidden on /admin

**Cause:** User lacks proper role or not authenticated

**Solution:**
- Use quick login route: `http://localhost/login-admin`
- Verify user has correct role in database
- Check `canAccessPanel()` method in User model

### Issue: EditAction Button Not Visible

**Cause:** Namespace inconsistency in Filament 4

**Solution:** Use custom Action instead:
```php
use Filament\Actions\Action;

->actions([
    Action::make('edit')
        ->label('Editar')
        ->icon('heroicon-o-pencil')
        ->url(fn ($record) => Pages\EditEntity::getUrl(['record' => $record])),
])
```

### Issue: Permissions Not Working

**Cause:** User has wrong role name or permissions not seeded

**Solution:**
```bash
# Check user roles
vendor/bin/sail artisan tinker --execute="User::find(1)->roles->pluck('name');"

# Reseed permissions
vendor/bin/sail artisan db:seed --class=DatabaseSeeder
```

## Documentation References

- **Best Practices**: `.taskmaster/docs/best-practices-laravel12-filament4.md`
- **Lessons Learned**: `.taskmaster/docs/lessons-learned.md`
- **Database Schema**: `.taskmaster/docs/database_schema.md`
- **Useful Commands**: `.taskmaster/docs/useful-commands.md`
- **Installation Guide**: `INSTALL.md`
- **README**: `README.md`

## Additional Resources

### Task Management

This project uses Task Master for task tracking. Commands available in `.taskmaster/docs/taskmaster-commands.md`.

### MCP Servers

Laravel Boost MCP server is configured with tools for:
- Database schema inspection
- Artisan command listing
- Documentation search (version-specific)
- Tinker execution
- Error log reading
- Browser log inspection

### Debugging

```bash
# Check Laravel version
vendor/bin/sail artisan --version

# Check if Filament class exists
vendor/bin/sail artisan tinker --execute="echo class_exists('\\Filament\\Actions\\Action') ? 'OK' : 'ERRO';"

# List Filament routes
vendor/bin/sail artisan route:list --name=filament

# Read application logs
vendor/bin/sail artisan tinker --execute="echo file_get_contents(storage_path('logs/laravel.log'));" | tail -50
```

## Quick Reference: URL Patterns

```php
// Generate absolute URLs
use function Filament\Support\get_absolute_url;
$url = get_absolute_url('/admin/credentials');

// Named routes
route('filament.admin.resources.credentials.index')
route('filament.admin.resources.users.edit', ['record' => $user])

// Page URLs in Resources
Pages\EditCredential::getUrl(['record' => $record])
```

## Before Making Changes

1. **Read database schema**: Check `.taskmaster/docs/database_schema.md`
2. **Check lessons learned**: Review `.taskmaster/docs/lessons-learned.md` for similar issues
3. **Create backup**: Always backup database before structural changes
4. **Run tests**: Ensure existing tests pass before making changes
5. **Follow conventions**: Check sibling files for naming and structure patterns
