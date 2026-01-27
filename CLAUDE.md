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

### Badge Color Centralization

**Badge Color Enum for Consistent Styling**

Use `App\Enums\BadgeColor` to centralize badge color logic across all Filament resources. This enum eliminates code duplication and ensures consistent visual styling throughout the application.

**Available Methods:**

```php
use App\Enums\BadgeColor;

// 1. forRole() - Returns color for user roles
BadgeColor::forRole('super_admin')->value  // 'danger' (red)
BadgeColor::forRole('admin')->value        // 'warning' (yellow)
BadgeColor::forRole('operador')->value     // 'success' (green)
BadgeColor::forRole('consulta')->value     // 'primary' (blue)
BadgeColor::forRole('unknown')->value      // 'gray' (default)

// 2. forSecrecy() - Returns color for secrecy levels
BadgeColor::forSecrecy('S')->value  // 'danger' (Secreto = red)
BadgeColor::forSecrecy('R')->value  // 'success' (Reservado = green)
BadgeColor::forSecrecy('X')->value  // 'gray' (unknown = gray)

// 3. forType() - Returns color for credential types
BadgeColor::forType('CRED')->value  // 'info' (cyan)
BadgeColor::forType('TCMS')->value  // 'warning' (yellow)
BadgeColor::forType('OTHER')->value // 'gray' (default)

// 4. forValidity() - Returns color based on validity date
BadgeColor::forValidity($pastDate)->value      // 'danger' (expired)
BadgeColor::forValidity($in15Days)->value      // 'warning' (expiring soon)
BadgeColor::forValidity($inOneYear)->value     // 'success' (valid)
BadgeColor::forValidity(null)->value           // 'gray' (no date)
```

**Color Mapping Tables:**

| Role | Color | Badge |
|------|-------|-------|
| super_admin | danger | 🔴 Red |
| admin | warning | 🟡 Yellow |
| operador | success | 🟢 Green |
| consulta | primary | 🔵 Blue |
| other | gray | ⚪ Gray |

| Secrecy | Color | Badge |
|---------|-------|-------|
| S (Secreto) | danger | 🔴 Red |
| R (Reservado) | success | 🟢 Green |
| other | gray | ⚪ Gray |

| Type | Color | Badge |
|------|-------|-------|
| CRED | info | 🔵 Cyan |
| TCMS | warning | 🟡 Yellow |
| other | gray | ⚪ Gray |

| Validity | Color | Badge |
|----------|-------|-------|
| Past date | danger | 🔴 Red |
| ≤ 30 days | warning | 🟡 Yellow |
| > 30 days | success | 🟢 Green |
| null | gray | ⚪ Gray |

**Usage in Filament Tables:**

```php
use App\Enums\BadgeColor;
use Filament\Tables\Columns\TextColumn;

// Example 1: Role badges
TextColumn::make('roles.name')
    ->label('Roles')
    ->badge()
    ->color(fn ($state) => BadgeColor::forRole($state)->value)
    ->formatStateUsing(fn ($state) => ucfirst($state))

// Example 2: Secrecy level badges
TextColumn::make('secrecy')
    ->label('Sigilo')
    ->badge()
    ->color(fn ($record) => BadgeColor::forSecrecy($record->secrecy)->value)
    ->formatStateUsing(fn ($state) => $state === 'S' ? 'Secreto' : 'Reservado')

// Example 3: Validity date badges
TextColumn::make('validity')
    ->label('Validade')
    ->date('d/m/Y')
    ->badge()
    ->color(fn ($record) => BadgeColor::forValidity($record->validity)->value)
    ->sortable()
```

**Important Notes:**

- Always use `->value` to get the string representation of the enum case
- The enum uses Carbon for date calculations in `forValidity()`
- Validity threshold is 30 days (configurable in the enum if needed)
- Complete test coverage available in `tests/Unit/BadgeColorEnumTest.php`

**Testing Reference:**

See `tests/Unit/BadgeColorEnumTest.php` for comprehensive unit tests covering all methods and edge cases.

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
- **Minimum coverage**: 80% (Current: 84.9%)
- **Detailed documentation**: See `.taskmaster/docs/testing-strategies.md` for:
  - Complete testing patterns and strategies
  - Filament/Livewire testing examples
  - Factory usage
  - Policy and Observer testing
  - Troubleshooting guide
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

===

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to enhance the user's satisfaction building Laravel applications.

## Foundational Context
This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.4.15
- filament/filament (FILAMENT) - v4
- laravel/framework (LARAVEL) - v12
- laravel/prompts (PROMPTS) - v0
- laravel/sanctum (SANCTUM) - v4
- livewire/livewire (LIVEWIRE) - v3
- laravel/mcp (MCP) - v0
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- pestphp/pest (PEST) - v3
- phpunit/phpunit (PHPUNIT) - v11
- alpinejs (ALPINEJS) - v3
- tailwindcss (TAILWINDCSS) - v4

## Conventions
- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts
- Do not create verification scripts or tinker when tests cover that functionality and prove it works. Unit and feature tests are more important.

## Application Structure & Architecture
- Stick to existing directory structure - don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling
- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `vendor/bin/sail npm run build`, `vendor/bin/sail npm run dev`, or `vendor/bin/sail composer run dev`. Ask them.

## Replies
- Be concise in your explanations - focus on what's important rather than explaining obvious details.

## Documentation Files
- You must only create documentation files if explicitly requested by the user.


=== boost rules ===

## Laravel Boost
- Laravel Boost is an MCP server that comes with powerful tools designed specifically for this application. Use them.

## Artisan
- Use the `list-artisan-commands` tool when you need to call an Artisan command to double check the available parameters.

## URLs
- Whenever you share a project URL with the user you should use the `get-absolute-url` tool to ensure you're using the correct scheme, domain / IP, and port.

## Tinker / Debugging
- You should use the `tinker` tool when you need to execute PHP to debug code or query Eloquent models directly.
- Use the `database-query` tool when you only need to read from the database.

## Reading Browser Logs With the `browser-logs` Tool
- You can read browser logs, errors, and exceptions using the `browser-logs` tool from Boost.
- Only recent browser logs will be useful - ignore old logs.

## Searching Documentation (Critically Important)
- Boost comes with a powerful `search-docs` tool you should use before any other approaches. This tool automatically passes a list of installed packages and their versions to the remote Boost API, so it returns only version-specific documentation specific for the user's circumstance. You should pass an array of packages to filter on if you know you need docs for particular packages.
- The 'search-docs' tool is perfect for all Laravel related packages, including Laravel, Inertia, Livewire, Filament, Tailwind, Pest, Nova, Nightwatch, etc.
- You must use this tool to search for Laravel-ecosystem documentation before falling back to other approaches.
- Search the documentation before making code changes to ensure we are taking the correct approach.
- Use multiple, broad, simple, topic based queries to start. For example: `['rate limiting', 'routing rate limiting', 'routing']`.
- Do not add package names to queries - package information is already shared. For example, use `test resource table`, not `filament 4 test resource table`.

### Available Search Syntax
- You can and should pass multiple queries at once. The most relevant results will be returned first.

1. Simple Word Searches with auto-stemming - query=authentication - finds 'authenticate' and 'auth'
2. Multiple Words (AND Logic) - query=rate limit - finds knowledge containing both "rate" AND "limit"
3. Quoted Phrases (Exact Position) - query="infinite scroll" - Words must be adjacent and in that order
4. Mixed Queries - query=middleware "rate limit" - "middleware" AND exact phrase "rate limit"
5. Multiple Queries - queries=["authentication", "middleware"] - ANY of these terms


=== php rules ===

## PHP

- Always use curly braces for control structures, even if it has one line.

### Constructors
- Use PHP 8 constructor property promotion in `__construct()`.
    - <code-snippet>public function __construct(public GitHub $github) { }</code-snippet>
- Do not allow empty `__construct()` methods with zero parameters.

### Type Declarations
- Always use explicit return type declarations for methods and functions.
- Use appropriate PHP type hints for method parameters.

<code-snippet name="Explicit Return Types and Method Params" lang="php">
protected function isAccessible(User $user, ?string $path = null): bool
{
    ...
}
</code-snippet>

## Comments
- Prefer PHPDoc blocks over comments. Never use comments within the code itself unless there is something _very_ complex going on.

## PHPDoc Blocks
- Add useful array shape type definitions for arrays when appropriate.

## Enums
- Typically, keys in an Enum should be TitleCase. For example: `FavoritePerson`, `BestLake`, `Monthly`.


=== sail rules ===

## Laravel Sail

- This project runs inside Laravel Sail's Docker containers. You MUST execute all commands through Sail.
- Start services using `vendor/bin/sail up -d` and stop them with `vendor/bin/sail stop`.
- Open the application in the browser by running `vendor/bin/sail open`.
- Always prefix PHP, Artisan, Composer, and Node commands** with `vendor/bin/sail`. Examples:
- Run Artisan Commands: `vendor/bin/sail artisan migrate`
- Install Composer packages: `vendor/bin/sail composer install`
- Execute node commands: `vendor/bin/sail npm run dev`
- Execute PHP scripts: `vendor/bin/sail php [script]`
- View all available Sail commands by running `vendor/bin/sail` without arguments.


=== laravel/core rules ===

## Do Things the Laravel Way

- Use `vendor/bin/sail artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using the `list-artisan-commands` tool.
- If you're creating a generic PHP class, use `vendor/bin/sail artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Database
- Always use proper Eloquent relationship methods with return type hints. Prefer relationship methods over raw queries or manual joins.
- Use Eloquent models and relationships before suggesting raw database queries
- Avoid `DB::`; prefer `Model::query()`. Generate code that leverages Laravel's ORM capabilities rather than bypassing them.
- Generate code that prevents N+1 query problems by using eager loading.
- Use Laravel's query builder for very complex database operations.

### Model Creation
- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `list-artisan-commands` to check the available options to `vendor/bin/sail artisan make:model`.

### APIs & Eloquent Resources
- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

### Controllers & Validation
- Always create Form Request classes for validation rather than inline validation in controllers. Include both validation rules and custom error messages.
- Check sibling Form Requests to see if the application uses array or string based validation rules.

### Queues
- Use queued jobs for time-consuming operations with the `ShouldQueue` interface.

### Authentication & Authorization
- Use Laravel's built-in authentication and authorization features (gates, policies, Sanctum, etc.).

### URL Generation
- When generating links to other pages, prefer named routes and the `route()` function.

### Configuration
- Use environment variables only in configuration files - never use the `env()` function directly outside of config files. Always use `config('app.name')`, not `env('APP_NAME')`.

### Testing
- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `vendor/bin/sail artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

### Vite Error
- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `vendor/bin/sail npm run build` or ask the user to run `vendor/bin/sail npm run dev` or `vendor/bin/sail composer run dev`.


=== laravel/v12 rules ===

## Laravel 12

- Use the `search-docs` tool to get version specific documentation.
- This project upgraded from Laravel 10 without migrating to the new streamlined Laravel file structure.
- This is **perfectly fine** and recommended by Laravel. Follow the existing structure from Laravel 10. We do not to need migrate to the new Laravel structure unless the user explicitly requests that.

### Laravel 10 Structure
- Middleware typically lives in `app/Http/Middleware/` and service providers in `app/Providers/`.
- There is no `bootstrap/app.php` application configuration in a Laravel 10 structure:
    - Middleware registration happens in `app/Http/Kernel.php`
    - Exception handling is in `app/Exceptions/Handler.php`
    - Console commands and schedule register in `app/Console/Kernel.php`
    - Rate limits likely exist in `RouteServiceProvider` or `app/Http/Kernel.php`

### Database
- When modifying a column, the migration must include all of the attributes that were previously defined on the column. Otherwise, they will be dropped and lost.
- Laravel 11 allows limiting eagerly loaded records natively, without external packages: `$query->latest()->limit(10);`.

### Models
- Casts can and likely should be set in a `casts()` method on a model rather than the `$casts` property. Follow existing conventions from other models.


=== livewire/core rules ===

## Livewire Core
- Use the `search-docs` tool to find exact version specific documentation for how to write Livewire & Livewire tests.
- Use the `vendor/bin/sail artisan make:livewire [Posts\CreatePost]` artisan command to create new components
- State should live on the server, with the UI reflecting it.
- All Livewire requests hit the Laravel backend, they're like regular HTTP requests. Always validate form data, and run authorization checks in Livewire actions.

## Livewire Best Practices
- Livewire components require a single root element.
- Use `wire:loading` and `wire:dirty` for delightful loading states.
- Add `wire:key` in loops:

    ```blade
    @foreach ($items as $item)
        <div wire:key="item-{{ $item->id }}">
            {{ $item->name }}
        </div>
    @endforeach
    ```

- Prefer lifecycle hooks like `mount()`, `updatedFoo()` for initialization and reactive side effects:

<code-snippet name="Lifecycle hook examples" lang="php">
    public function mount(User $user) { $this->user = $user; }
    public function updatedSearch() { $this->resetPage(); }
</code-snippet>


## Testing Livewire

<code-snippet name="Example Livewire component test" lang="php">
    Livewire::test(Counter::class)
        ->assertSet('count', 0)
        ->call('increment')
        ->assertSet('count', 1)
        ->assertSee(1)
        ->assertStatus(200);
</code-snippet>


    <code-snippet name="Testing a Livewire component exists within a page" lang="php">
        $this->get('/posts/create')
        ->assertSeeLivewire(CreatePost::class);
    </code-snippet>


=== livewire/v3 rules ===

## Livewire 3

### Key Changes From Livewire 2
- These things changed in Livewire 2, but may not have been updated in this application. Verify this application's setup to ensure you conform with application conventions.
    - Use `wire:model.live` for real-time updates, `wire:model` is now deferred by default.
    - Components now use the `App\Livewire` namespace (not `App\Http\Livewire`).
    - Use `$this->dispatch()` to dispatch events (not `emit` or `dispatchBrowserEvent`).
    - Use the `components.layouts.app` view as the typical layout path (not `layouts.app`).

### New Directives
- `wire:show`, `wire:transition`, `wire:cloak`, `wire:offline`, `wire:target` are available for use. Use the documentation to find usage examples.

### Alpine
- Alpine is now included with Livewire, don't manually include Alpine.js.
- Plugins included with Alpine: persist, intersect, collapse, and focus.

### Lifecycle Hooks
- You can listen for `livewire:init` to hook into Livewire initialization, and `fail.status === 419` for the page expiring:

<code-snippet name="livewire:load example" lang="js">
document.addEventListener('livewire:init', function () {
    Livewire.hook('request', ({ fail }) => {
        if (fail && fail.status === 419) {
            alert('Your session expired');
        }
    });

    Livewire.hook('message.failed', (message, component) => {
        console.error(message);
    });
});
</code-snippet>


=== pint/core rules ===

## Laravel Pint Code Formatter

- You must run `vendor/bin/sail bin pint --dirty` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/sail bin pint --test`, simply run `vendor/bin/sail bin pint` to fix any formatting issues.


=== pest/core rules ===

## Pest
### Testing
- If you need to verify a feature is working, write or update a Unit / Feature test.

### Pest Tests
- All tests must be written using Pest. Use `vendor/bin/sail artisan make:test --pest {name}`.
- You must not remove any tests or test files from the tests directory without approval. These are not temporary or helper files - these are core to the application.
- Tests should test all of the happy paths, failure paths, and weird paths.
- Tests live in the `tests/Feature` and `tests/Unit` directories.
- Pest tests look and behave like this:
<code-snippet name="Basic Pest Test Example" lang="php">
it('is true', function () {
    expect(true)->toBeTrue();
});
</code-snippet>

### Running Tests
- Run the minimal number of tests using an appropriate filter before finalizing code edits.
- To run all tests: `vendor/bin/sail artisan test`.
- To run all tests in a file: `vendor/bin/sail artisan test tests/Feature/ExampleTest.php`.
- To filter on a particular test name: `vendor/bin/sail artisan test --filter=testName` (recommended after making a change to a related file).
- When the tests relating to your changes are passing, ask the user if they would like to run the entire test suite to ensure everything is still passing.

### Pest Assertions
- When asserting status codes on a response, use the specific method like `assertForbidden` and `assertNotFound` instead of using `assertStatus(403)` or similar, e.g.:
<code-snippet name="Pest Example Asserting postJson Response" lang="php">
it('returns all', function () {
    $response = $this->postJson('/api/docs', []);

    $response->assertSuccessful();
});
</code-snippet>

### Mocking
- Mocking can be very helpful when appropriate.
- When mocking, you can use the `Pest\Laravel\mock` Pest function, but always import it via `use function Pest\Laravel\mock;` before using it. Alternatively, you can use `$this->mock()` if existing tests do.
- You can also create partial mocks using the same import or self method.

### Datasets
- Use datasets in Pest to simplify tests which have a lot of duplicated data. This is often the case when testing validation rules, so consider going with this solution when writing tests for validation rules.

<code-snippet name="Pest Dataset Example" lang="php">
it('has emails', function (string $email) {
    expect($email)->not->toBeEmpty();
})->with([
    'james' => 'james@laravel.com',
    'taylor' => 'taylor@laravel.com',
]);
</code-snippet>


=== tailwindcss/core rules ===

## Tailwind Core

- Use Tailwind CSS classes to style HTML, check and use existing tailwind conventions within the project before writing your own.
- Offer to extract repeated patterns into components that match the project's conventions (i.e. Blade, JSX, Vue, etc..)
- Think through class placement, order, priority, and defaults - remove redundant classes, add classes to parent or child carefully to limit repetition, group elements logically
- You can use the `search-docs` tool to get exact examples from the official documentation when needed.

### Spacing
- When listing items, use gap utilities for spacing, don't use margins.

    <code-snippet name="Valid Flex Gap Spacing Example" lang="html">
        <div class="flex gap-8">
            <div>Superior</div>
            <div>Michigan</div>
            <div>Erie</div>
        </div>
    </code-snippet>


### Dark Mode
- If existing pages and components support dark mode, new pages and components must support dark mode in a similar way, typically using `dark:`.


=== tailwindcss/v4 rules ===

## Tailwind 4

- Always use Tailwind CSS v4 - do not use the deprecated utilities.
- `corePlugins` is not supported in Tailwind v4.
- In Tailwind v4, configuration is CSS-first using the `@theme` directive — no separate `tailwind.config.js` file is needed.
<code-snippet name="Extending Theme in CSS" lang="css">
@theme {
  --color-brand: oklch(0.72 0.11 178);
}
</code-snippet>

- In Tailwind v4, you import Tailwind using a regular CSS `@import` statement, not using the `@tailwind` directives used in v3:

<code-snippet name="Tailwind v4 Import Tailwind Diff" lang="diff">
   - @tailwind base;
   - @tailwind components;
   - @tailwind utilities;
   + @import "tailwindcss";
</code-snippet>


### Replaced Utilities
- Tailwind v4 removed deprecated utilities. Do not use the deprecated option - use the replacement.
- Opacity values are still numeric.

| Deprecated |	Replacement |
|------------+--------------|
| bg-opacity-* | bg-black/* |
| text-opacity-* | text-black/* |
| border-opacity-* | border-black/* |
| divide-opacity-* | divide-black/* |
| ring-opacity-* | ring-black/* |
| placeholder-opacity-* | placeholder-black/* |
| flex-shrink-* | shrink-* |
| flex-grow-* | grow-* |
| overflow-ellipsis | text-ellipsis |
| decoration-slice | box-decoration-slice |
| decoration-clone | box-decoration-clone |


=== tests rules ===

## Test Enforcement

- Every change must be programmatically tested. Write a new test or update an existing test, then run the affected tests to make sure they pass.
- Run the minimum number of tests needed to ensure code quality and speed. Use `vendor/bin/sail artisan test` with a specific filename or filter.


=== filament/filament rules ===

## Filament
- Filament is used by this application, check how and where to follow existing application conventions.
- Filament is a Server-Driven UI (SDUI) framework for Laravel. It allows developers to define user interfaces in PHP using structured configuration objects. It is built on top of Livewire, Alpine.js, and Tailwind CSS.
- You can use the `search-docs` tool to get information from the official Filament documentation when needed. This is very useful for Artisan command arguments, specific code examples, testing functionality, relationship management, and ensuring you're following idiomatic practices.
- Utilize static `make()` methods for consistent component initialization.

### Artisan
- You must use the Filament specific Artisan commands to create new files or components for Filament. You can find these with the `list-artisan-commands` tool, or with `php artisan` and the `--help` option.
- Inspect the required options, always pass `--no-interaction`, and valid arguments for other options when applicable.

### Filament's Core Features
- Actions: Handle doing something within the application, often with a button or link. Actions encapsulate the UI, the interactive modal window, and the logic that should be executed when the modal window is submitted. They can be used anywhere in the UI and are commonly used to perform one-time actions like deleting a record, sending an email, or updating data in the database based on modal form input.
- Forms: Dynamic forms rendered within other features, such as resources, action modals, table filters, and more.
- Infolists: Read-only lists of data.
- Notifications: Flash notifications displayed to users within the application.
- Panels: The top-level container in Filament that can include all other features like pages, resources, forms, tables, notifications, actions, infolists, and widgets.
- Resources: Static classes that are used to build CRUD interfaces for Eloquent models. Typically live in `app/Filament/Resources`.
- Schemas: Represent components that define the structure and behavior of the UI, such as forms, tables, or lists.
- Tables: Interactive tables with filtering, sorting, pagination, and more.
- Widgets: Small component included within dashboards, often used for displaying data in charts, tables, or as a stat.

### Relationships
- Determine if you can use the `relationship()` method on form components when you need `options` for a select, checkbox, repeater, or when building a `Fieldset`:

<code-snippet name="Relationship example for Form Select" lang="php">
Forms\Components\Select::make('user_id')
    ->label('Author')
    ->relationship('author')
    ->required(),
</code-snippet>


## Testing
- It's important to test Filament functionality for user satisfaction.
- Ensure that you are authenticated to access the application within the test.
- Filament uses Livewire, so start assertions with `livewire()` or `Livewire::test()`.

### Example Tests

<code-snippet name="Filament Table Test" lang="php">
    livewire(ListUsers::class)
        ->assertCanSeeTableRecords($users)
        ->searchTable($users->first()->name)
        ->assertCanSeeTableRecords($users->take(1))
        ->assertCanNotSeeTableRecords($users->skip(1))
        ->searchTable($users->last()->email)
        ->assertCanSeeTableRecords($users->take(-1))
        ->assertCanNotSeeTableRecords($users->take($users->count() - 1));
</code-snippet>

<code-snippet name="Filament Create Resource Test" lang="php">
    livewire(CreateUser::class)
        ->fillForm([
            'name' => 'Howdy',
            'email' => 'howdy@example.com',
        ])
        ->call('create')
        ->assertNotified()
        ->assertRedirect();

    assertDatabaseHas(User::class, [
        'name' => 'Howdy',
        'email' => 'howdy@example.com',
    ]);
</code-snippet>

<code-snippet name="Testing Multiple Panels (setup())" lang="php">
    use Filament\Facades\Filament;

    Filament::setCurrentPanel('app');
</code-snippet>

<code-snippet name="Calling an Action in a Test" lang="php">
    livewire(EditInvoice::class, [
        'invoice' => $invoice,
    ])->callAction('send');

    expect($invoice->refresh())->isSent()->toBeTrue();
</code-snippet>


### Important Version 4 Changes
- File visibility is now `private` by default.
- The `deferFilters` method from Filament v3 is now the default behavior in Filament v4, so users must click a button before the filters are applied to the table. To disable this behavior, you can use the `deferFilters(false)` method.
- The `Grid`, `Section`, and `Fieldset` layout components no longer span all columns by default.
- The `all` pagination page method is not available for tables by default.
- All action classes extend `Filament\Actions\Action`. No action classes exist in `Filament\Tables\Actions`.
- The `Form` & `Infolist` layout components have been moved to `Filament\Schemas\Components`, for example `Grid`, `Section`, `Fieldset`, `Tabs`, `Wizard`, etc.
- A new `Repeater` component for Forms has been added.
- Icons now use the `Filament\Support\Icons\Heroicon` Enum by default. Other options are available and documented.

### Organize Component Classes Structure
- Schema components: `Schemas/Components/`
- Table columns: `Tables/Columns/`
- Table filters: `Tables/Filters/`
- Actions: `Actions/`
</laravel-boost-guidelines>
