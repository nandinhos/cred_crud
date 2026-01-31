# 🎯 AGENTS.md - Orquestrador de Contexto

> **REGRA ZERO:** Antes de executar QUALQUER ação, consulte a documentação relevante.
> Nunca assuma. Sempre verifique. Documente tudo.

---

## 🚀 FERRAMENTAS MCP OBRIGATÓRIAS

### 🔥 Laravel Boost MCP (USO OBRIGATÓRIO)

```
╔══════════════════════════════════════════════════════════════════╗
║  ⚡ LARAVEL BOOST É OBRIGATÓRIO PARA TODA CODIFICAÇÃO            ║
║                                                                  ║
║  ANTES de escrever qualquer código Laravel/Filament:             ║
║  → Use as ferramentas do Laravel Boost MCP                       ║
║  → Consulte padrões e convenções via Laravel Boost               ║
║  → Gere código usando os assistentes do Laravel Boost            ║
║                                                                  ║
║  Esta ferramenta é o PADRÃO OURO para qualidade de código.       ║
╚══════════════════════════════════════════════════════════════════╝
```

#### Ferramentas Disponíveis no Laravel Boost

| Ferramenta | Quando Usar |
|------------|-------------|
| `search-docs` | **SEMPRE PRIMEIRO** - Buscar documentação antes de codar |
| `list-artisan-commands` | Antes de executar qualquer comando Artisan |
| `tinker` | Debugar código ou consultar Eloquent models |
| `database-query` | Apenas leitura do banco de dados |
| `browser-logs` | Ler logs, erros e exceções do navegador |
| `get-absolute-url` | Ao compartilhar URLs do projeto |

#### Regras do `search-docs`
```
✅ Use queries simples e amplas: ['rate limiting', 'routing', 'middleware']
✅ Passe múltiplas queries de uma vez
✅ Filtre por pacotes específicos quando souber qual precisa

❌ NÃO inclua nome/versão do pacote na query
   Errado: "filament 4 test resource table"
   Certo: "test resource table"
```

---

## 📚 EXEMPLOS PRÁTICOS - LARAVEL BOOST MCP

### 🔍 1. `search-docs` - Buscar Documentação (SEMPRE PRIMEIRO!)

**Cenário 1: Criar um Resource no Filament**
```bash
# ANTES de criar código, busque a documentação:
search-docs(['resource create', 'filament resource', 'resource form'])

# Resultado: Documentação específica do Filament 4 sobre:
# - Como criar resources
# - Estrutura de forms
# - Relacionamentos
# - Validação
```

**Cenário 2: Implementar Relacionamentos**
```bash
# Buscar docs sobre relacionamentos:
search-docs(['eloquent relationships', 'belongsTo', 'hasMany'])

# Ou buscar em pacote específico:
search-docs(['table relationship'], packages=['filament'])
```

**Cenário 3: Configurar Autenticação**
```bash
# Múltiplas queries para cobertura ampla:
search-docs([
    'authentication',
    'middleware auth',
    'policies authorization',
    'gates'
])
```

**Cenário 4: Troubleshooting de Erro**
```bash
# Erro com Livewire? Busque docs específicas:
search-docs(['livewire lifecycle', 'wire:model', 'livewire events'])

# Erro com validação?
search-docs(['validation rules', 'form request', 'custom validation'])
```

---

### 📋 2. `list-artisan-commands` - Verificar Comandos Disponíveis

**Antes de executar qualquer `make:` command:**
```bash
# ❌ ERRADO: Executar direto sem verificar opções
vendor/bin/sail artisan make:model Post

# ✅ CORRETO: Verificar opções disponíveis primeiro
list-artisan-commands('make:model')

# Resultado mostra opções como:
# --migration, --factory, --seed, --controller, --resource, --all, etc.

# Então execute com opções corretas:
vendor/bin/sail artisan make:model Post --all --pest
```

**Antes de usar comandos Filament:**
```bash
# Verificar comandos disponíveis do Filament
list-artisan-commands('make:filament')

# Ou filtrar por palavra-chave:
list-artisan-commands('filament')

# Resultado:
# - make:filament-resource
# - make:filament-page
# - make:filament-widget
# - make:filament-relation-manager
```

**Verificar opções de migração:**
```bash
list-artisan-commands('migrate')

# Mostra: migrate, migrate:fresh, migrate:refresh, migrate:rollback, etc.
```

---

### 🧪 3. `tinker` - Debug Interativo (Substituir dd() e dump())

**Cenário 1: Testar Query Eloquent**
```bash
# ANTES de escrever código complexo, teste no tinker:
tinker("User::with('credentials')->where('active', true)->count()")

# Resultado: número de usuários ativos com credenciais carregadas
```

**Cenário 2: Verificar Relacionamentos**
```bash
# Testar se relacionamento está funcionando:
tinker("App\\Models\\User::find(1)->credentials")

# Ou verificar relacionamento reverso:
tinker("App\\Models\\Credential::find(1)->user")
```

**Cenário 3: Testar Lógica de Negócio**
```bash
# Verificar se método do model funciona:
tinker("App\\Models\\Credential::factory()->make()->isExpired()")

# Testar accessor:
tinker("App\\Models\\User::first()->full_name")

# Testar cast:
tinker("App\\Models\\Credential::first()->type")
```

**Cenário 4: Debug de Dados**
```bash
# Ver estrutura de dados:
tinker("User::first()->toArray()")

# Contar registros:
tinker("Credential::whereNull('deleted_at')->count()")

# Verificar últimos registros:
tinker("Credential::latest()->take(5)->pluck('name', 'id')")
```

---

### 💾 4. `database-query` - Consultar Banco (Read-Only)

**⚠️ IMPORTANTE: Apenas para LEITURA - não modifica dados**

**Cenário 1: Verificar Estrutura de Tabela**
```sql
-- Ver colunas da tabela credentials:
database-query("DESCRIBE credentials")

-- Ou no PostgreSQL:
database-query("SELECT column_name, data_type FROM information_schema.columns WHERE table_name = 'credentials'")
```

**Cenário 2: Analisar Dados Antes de Migração**
```sql
-- Verificar dados existentes antes de criar migração:
database-query("SELECT type, COUNT(*) as total FROM credentials GROUP BY type")

-- Verificar valores nulos:
database-query("SELECT COUNT(*) FROM credentials WHERE fscs IS NULL")

-- Verificar duplicatas:
database-query("SELECT credential, COUNT(*) as duplicates FROM credentials GROUP BY credential HAVING COUNT(*) > 1")
```

**Cenário 3: Debugar Relacionamentos**
```sql
-- Verificar foreign keys:
database-query("SELECT * FROM users WHERE id NOT IN (SELECT DISTINCT user_id FROM credentials WHERE user_id IS NOT NULL)")

-- Ver dados relacionados:
database-query("
    SELECT u.name, COUNT(c.id) as total_credentials 
    FROM users u 
    LEFT JOIN credentials c ON u.id = c.user_id 
    GROUP BY u.id, u.name
")
```

**Cenário 4: Análise de Performance**
```sql
-- Ver índices da tabela:
database-query("SHOW INDEXES FROM credentials")

-- Contar registros por status:
database-query("SELECT status, COUNT(*) FROM credentials GROUP BY status")
```

---

### 🐛 5. `browser-logs` - Ver Erros do Navegador

**Quando usar:**
- Erro 500 na aplicação
- Componente Livewire não está respondendo
- Formulário Filament não está salvando
- JavaScript não está funcionando

**Como usar:**
```bash
# Ler logs mais recentes do navegador:
browser-logs()

# Filtra automaticamente por:
# - Erros JavaScript
# - Exceções PHP (via Ajax)
# - Erros de console
# - Network errors (429, 500, 503, etc)
```

**Exemplo de workflow:**
```bash
1. Usuário reporta: "Botão não funciona no formulário"
2. Execute: browser-logs()
3. Resultado mostra: "419 CSRF token mismatch"
4. Solução: Verificar se token CSRF está sendo enviado
```

---

### 🔗 6. `get-absolute-url` - URLs Corretas do Projeto

**Quando compartilhar URLs com o usuário:**
```bash
# ❌ ERRADO: Assumir URL
"Acesse http://localhost/admin/credentials"

# ✅ CORRETO: Usar get-absolute-url
get-absolute-url('/admin/credentials')

# Resultado pode ser:
# - http://localhost:80/admin/credentials
# - http://192.168.1.10:8080/admin/credentials
# - https://credcrud.local/admin/credentials
```

---

## 🔄 WORKFLOWS COMPLETOS COM BOOST

### Workflow 1: Criar um novo Filament Resource

```bash
# 1. Buscar documentação PRIMEIRO
search-docs(['filament resource', 'resource form', 'resource table'])

# 2. Verificar comando disponível
list-artisan-commands('make:filament-resource')

# 3. Criar o resource
vendor/bin/sail artisan make:filament-resource Department --generate --simple

# 4. Testar query no tinker
tinker("App\\Models\\Department::count()")

# 5. Verificar dados no banco
database-query("SELECT * FROM departments LIMIT 5")

# 6. Abrir no navegador
get-absolute-url('/admin/departments')

# 7. Se houver erro, verificar logs
browser-logs()
```

### Workflow 2: Debugar Problema de Performance

```bash
# 1. Verificar queries no banco
database-query("
    SELECT COUNT(*) as total_queries 
    FROM information_schema.processlist
")

# 2. Testar query no tinker com timing
tinker("
    $start = microtime(true);
    User::with('credentials')->get();
    $end = microtime(true);
    echo 'Tempo: ' . ($end - $start) . 's';
")

# 3. Buscar docs sobre otimização
search-docs(['eager loading', 'n+1 problem', 'query optimization'])

# 4. Verificar logs do navegador
browser-logs()
```

### Workflow 3: Implementar Nova Funcionalidade

```bash
# 1. Buscar documentação da feature
search-docs(['feature-name', 'related-concepts'])

# 2. Verificar comandos Artisan disponíveis
list-artisan-commands('make:')

# 3. Criar arquivos necessários
vendor/bin/sail artisan make:model Feature --all

# 4. Testar factory no tinker
tinker("App\\Models\\Feature::factory()->make()")

# 5. Verificar estrutura do banco
database-query("DESCRIBE features")

# 6. Rodar testes
vendor/bin/sail artisan test --filter=FeatureTest

# 7. Verificar no navegador
get-absolute-url('/features')
browser-logs()
```

---

### 📖 Context7 MCP
- Manter documentação externa atualizada
- Consultar docs oficiais quando Laravel Boost não cobrir

---

## 🏗️ STACK TÉCNICA (VERSÕES EXATAS)

| Tecnologia | Versão | Observação |
|------------|--------|------------|
| **PHP** | 8.4.1 | ✅ Migrado para PHP 8.4 - Usar features do PHP 8.4 |
| **Laravel** | 12.39.0 | ⚠️ Estrutura Laravel 10 (não migrou para nova estrutura) |
| **Filament** | 4.2.2 | Atenção às mudanças do v3 → v4 |
| **Livewire** | 3.6.4 | `wire:model.live` para updates em tempo real |
| **Tailwind CSS** | 4.1.17 | ⚠️ **CUIDADO:** v4 tem breaking changes massivos |
| **Pest** | 3.8.4 | Todos os testes devem usar Pest |
| **Alpine.js** | 3.x | Já incluso no Livewire |
| **Laravel Sail** | 1.x | **TODOS os comandos via Sail** |

### ⚠️ Estrutura do Projeto (Laravel 10)
```
Este projeto usa estrutura Laravel 10, NÃO a nova estrutura streamlined:

- Middleware: app/Http/Middleware/
- Providers: app/Providers/
- Middleware registration: app/Http/Kernel.php
- Exception handling: app/Exceptions/Handler.php
- Console/Schedule: app/Console/Kernel.php
- Rate limits: RouteServiceProvider ou app/Http/Kernel.php

NÃO existe bootstrap/app.php para configuração!
```

---

## ⚓ LARAVEL SAIL (OBRIGATÓRIO)

```
╔══════════════════════════════════════════════════════════════════╗
║  🐳 TODOS OS COMANDOS DEVEM SER EXECUTADOS VIA SAIL              ║
║                                                                  ║
║  O projeto roda dentro de containers Docker do Laravel Sail.     ║
║  NUNCA execute comandos diretamente - sempre use vendor/bin/sail ║
╚══════════════════════════════════════════════════════════════════╝
```

### Comandos Sail Essenciais
```bash
# Iniciar/Parar containers
vendor/bin/sail up -d
vendor/bin/sail stop

# Artisan
vendor/bin/sail artisan migrate
vendor/bin/sail artisan make:model NomeModel --all

# Composer
vendor/bin/sail composer install
vendor/bin/sail composer require pacote/nome

# NPM / Assets
vendor/bin/sail npm run dev
vendor/bin/sail npm run build

# Testes
vendor/bin/sail artisan test
vendor/bin/sail artisan test --filter=NomeDoTeste

# Pint (formatação) - RODAR ANTES DE COMMITS
vendor/bin/sail bin pint --dirty

# PHP direto
vendor/bin/sail php script.php

# Abrir no navegador
vendor/bin/sail open
```

---

## 📚 ÍNDICE DE DOCUMENTAÇÃO (CONSULTA OBRIGATÓRIA)

| Domínio | Arquivo | Quando Consultar |
|---------|---------|------------------|
| **Gestão de Tarefas** | `.taskmaster/docs/taskmaster-commands.md` | Antes de qualquer operação com tasks |
| **Boas Práticas** | `.taskmaster/docs/best-practices-laravel12-filament4.md` | Antes de implementar qualquer feature |
| **Credenciais/Banco** | `.taskmaster/docs/credentials-system.md` | Antes de acessar banco ou serviços externos |
| **Lições Aprendidas** | `.taskmaster/docs/lessons-learned.md` | ANTES de debugar qualquer erro |
| **Comandos Úteis** | `.taskmaster/docs/useful-commands.md` | Para operações de infraestrutura |
| **PRD do Projeto** | `.taskmaster/docs/PRD/` | Para entender escopo e requisitos |
| **🎯 Migração Stack 2025** | `.taskmaster/docs/plano-migracao-stack-2025.md` | **CRÍTICO:** Plano de upgrade de tecnologias |

---

## 🔄 FLUXO PRINCIPAL DE TRABALHO

```
┌─────────────────────────────────────────────────────────────────┐
│  1. INICIAR SESSÃO                                              │
│     └─► task-master next (descobrir próxima tarefa)             │
├─────────────────────────────────────────────────────────────────┤
│  2. ANTES DE CODAR                                              │
│     ├─► Ler documentação relevante (ver índice acima)           │
│     ├─► Consultar lessons-learned.md (erros similares?)         │
│     ├─► ⚡ search-docs do Laravel Boost (OBRIGATÓRIO)           │
│     └─► Analisar estrutura do banco se necessário               │
├─────────────────────────────────────────────────────────────────┤
│  3. DURANTE IMPLEMENTAÇÃO                                       │
│     ├─► task-master set-status --id=<id> --status=in-progress   │
│     ├─► ⚡ USAR LARAVEL BOOST PARA GERAR/VALIDAR CÓDIGO         │
│     ├─► Usar comandos Artisan via Sail (make:model, etc)        │
│     ├─► Seguir convenções de arquivos irmãos (siblings)         │
│     └─► Backup do banco ANTES de qualquer alteração             │
├─────────────────────────────────────────────────────────────────┤
│  4. APÓS IMPLEMENTAÇÃO                                          │
│     ├─► vendor/bin/sail bin pint --dirty (formatar código)      │
│     ├─► vendor/bin/sail artisan test --filter=<teste>           │
│     ├─► Verificar se precisa rebuild assets (npm run build)     │
│     ├─► Verificar página/funcionalidade manualmente             │
│     ├─► task-master set-status --id=<id> --status=done          │
│     └─► Commit (conventional commits, em português)             │
├─────────────────────────────────────────────────────────────────┤
│  5. EM CASO DE ERRO                                             │
│     ├─► PRIMEIRO: Consultar lessons-learned.md                  │
│     ├─► ⚡ Usar Laravel Boost (browser-logs, tinker)            │
│     ├─► Resolver o problema                                     │
│     └─► Documentar solução em lessons-learned.md                │
└─────────────────────────────────────────────────────────────────┘
```

---

## ⚠️ CHECKPOINTS OBRIGATÓRIOS

### Checkpoint 0: Laravel Boost (SEMPRE PRIMEIRO)
- [ ] Usei `search-docs` para buscar documentação relevante?
- [ ] Verifiquei ferramentas disponíveis no Laravel Boost para esta tarefa?
- [ ] Usei `list-artisan-commands` para verificar opções do comando?

### Checkpoint 1: Antes de Iniciar Qualquer Tarefa
- [ ] Consultei `taskmaster-commands.md` para comandos corretos?
- [ ] Verifiquei em `lessons-learned.md` se há problemas similares já resolvidos?
- [ ] Li a documentação técnica relevante para esta tarefa?
- [ ] Entendo o escopo completo da tarefa e suas dependências?

### Checkpoint 2: Antes de Acessar Banco de Dados
- [ ] Consultei `credentials-system.md` para credenciais corretas?
- [ ] Identifiquei o container, porta e database corretos?
- [ ] Mapeei os campos das tabelas envolvidas?
- [ ] Criei backup antes de modificar dados?

### Checkpoint 3: Antes de Fazer Commit
- [ ] Rodei `vendor/bin/sail bin pint --dirty`?
- [ ] Todos os testes passaram?
- [ ] Verifiquei se precisa rebuild de assets (`npm run build`)?
- [ ] A página/funcionalidade está funcionando corretamente?
- [ ] O status da tarefa foi atualizado?
- [ ] O commit segue o padrão Conventional Commits em português?

### Checkpoint 4: Em Caso de Falha
- [ ] Consultei `lessons-learned.md` PRIMEIRO?
- [ ] ⚡ Usei `browser-logs` do Laravel Boost para ver erros?
- [ ] ⚡ Usei `tinker` para debugar queries/models?
- [ ] ⚡ Usei `database-query` para verificar dados?
- [ ] ⚡ Usei `search-docs` para buscar solução na documentação?
- [ ] Após resolver, documentei a solução em `lessons-learned.md`?

---

## 🎨 PADRÕES DE CÓDIGO

### PHP 8.4+
```php
// ✅ Constructor Property Promotion
public function __construct(
    public GitHub $github,
    private readonly UserRepository $users,
) {}

// ✅ Sempre declarar tipos de retorno
protected function isAccessible(User $user, ?string $path = null): bool
{
    // ...
}

// ✅ Sempre usar chaves em estruturas de controle
if ($condition) {
    return true;
}

// ❌ Não permitir __construct() vazio
// ❌ Não usar comentários inline - preferir PHPDoc
```

### Eloquent & Database
```php
// ✅ Usar Model::query() ao invés de DB::
User::query()->where('active', true)->get();

// ✅ Eager loading para evitar N+1
$posts = Post::with(['author', 'comments'])->get();

// ✅ Relacionamentos com type hints
public function author(): BelongsTo
{
    return $this->belongsTo(User::class);
}

// ❌ Evitar DB:: facade
// ❌ Evitar raw queries quando Eloquent resolve
```

### Filament 4 (Mudanças Importantes)
```php
// ✅ Ícones agora usam Enum
use Filament\Support\Icons\Heroicon;
->icon(Heroicon::OutlinePlus)

// ✅ Actions todas em Filament\Actions\Action
use Filament\Actions\Action;

// ✅ Layout components em Filament\Schemas\Components
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;

// ⚠️ deferFilters() agora é padrão em tabelas
// ⚠️ File visibility agora é 'private' por padrão
// ⚠️ Grid, Section, Fieldset não span all columns por padrão
```

### Livewire 3 (Mudanças do v2)
```php
// ✅ Namespace correto
namespace App\Livewire; // NÃO App\Http\Livewire

// ✅ Model binding em tempo real
wire:model.live="search" // NÃO wire:model

// ✅ Dispatch de eventos
$this->dispatch('evento'); // NÃO emit() ou dispatchBrowserEvent()

// ✅ Layout padrão
components.layouts.app // NÃO layouts.app
```

### Tailwind CSS 4.1.17 ⚠️ ATENÇÃO ESPECIAL
```html
<!-- ⚠️ PROJETO USA TAILWIND v4 - BREAKING CHANGES SIGNIFICATIVOS -->
<!-- 📖 Consultar: .taskmaster/docs/plano-migracao-stack-2025.md -->

<!-- ✅ Usar gap ao invés de margin para listas -->
<div class="flex gap-4">
    <div>Item 1</div>
    <div>Item 2</div>
</div>

<!-- ✅ Suportar dark mode se existente no projeto -->
<div class="bg-white dark:bg-gray-800">

<!-- ❌ Não usar margin para espaçamento entre itens -->

<!-- ⚠️ CUIDADO: Classes Tailwind v4 podem ter sintaxe diferente da v3 -->
<!-- 📋 Verificar sempre a documentação oficial antes de usar novas classes -->
```

---

## 🧪 TESTES (PEST)

### Regras Gerais
```bash
# Criar teste de feature (padrão)
vendor/bin/sail artisan make:test NomeTest --pest

# Criar teste unitário
vendor/bin/sail artisan make:test NomeTest --pest --unit

# Rodar teste específico
vendor/bin/sail artisan test --filter=NomeDoTeste

# Rodar arquivo específico
vendor/bin/sail artisan test tests/Feature/NomeTest.php
```

### Estrutura de Testes Pest
```php
// Teste básico
it('creates a user', function () {
    $user = User::factory()->create();
    expect($user)->toBeInstanceOf(User::class);
});

// Teste Filament
it('can list users', function () {
    $users = User::factory()->count(3)->create();
    
    livewire(ListUsers::class)
        ->assertCanSeeTableRecords($users);
});

// Teste com dataset
it('validates email', function (string $email, bool $valid) {
    // ...
})->with([
    'valid email' => ['test@example.com', true],
    'invalid email' => ['not-an-email', false],
]);

// ✅ Usar assertForbidden(), assertNotFound() ao invés de assertStatus(403)
// ✅ Usar factories com states existentes
// ✅ Autenticar antes de testar Filament
```

---

## 📋 REGRAS DE GESTÃO DE TAREFAS

### Criação de Nova Tarefa
```bash
# 1. Verificar complexidade
task-master analyze-complexity --research

# 2. Expandir em sub-tarefas se necessário
task-master expand --id=<id> --research

# 3. Gerar arquivos de tarefas
task-master generate
```

### Atualização de Status
```bash
# Iniciar tarefa
task-master set-status --id=<id> --status=in-progress

# Finalizar tarefa
task-master set-status --id=<id> --status=done

# ⚠️ REGRA: Status da tarefa pai = reflexo das sub-tarefas
```

---

## 🛡️ REGRAS DE SEGURANÇA DO BANCO DE DADOS

```
⚠️ ANTES DE QUALQUER OPERAÇÃO NO BANCO:

1. Consultar credentials-system.md
2. Verificar container correto (docker ps)
3. Confirmar database correto
4. CRIAR BACKUP:
   vendor/bin/sail artisan backup:run --only-db
   # ou manualmente:
   vendor/bin/sail mysql -u root -p < backup.sql
5. Mapear campos das tabelas envolvidas (usar database-query do Boost)
6. Executar operação
7. Validar resultado

⚠️ Em migrations: incluir TODOS os atributos da coluna ao modificar,
   senão serão perdidos!
```

---

## 🎨 ASSETS & FRONTEND

### Quando Rebuildar Assets
```bash
# Após alterar:
# - Arquivos CSS/Tailwind
# - Componentes Blade com classes Tailwind novas
# - Arquivos JavaScript

vendor/bin/sail npm run build

# Durante desenvolvimento
vendor/bin/sail npm run dev
```

### Erro de Vite Manifest
```
Se aparecer: "Unable to locate file in Vite manifest"

Solução: vendor/bin/sail npm run build
```

---

## ✅ PADRÃO DE COMMITS

```
Formato: <tipo>(<escopo>): <descrição em português>

Tipos permitidos:
- feat: Nova funcionalidade
- fix: Correção de bug
- docs: Documentação
- style: Formatação (também rodar pint)
- refactor: Refatoração
- test: Testes
- chore: Manutenção

Exemplo:
feat(financeiro): adiciona cálculo de comissões de artistas
fix(dashboard): corrige exibição de valores projetados
```

---

## 🔍 PROTOCOLO ANTI-ALUCINAÇÃO

### Quando NÃO Souber Algo:
1. **PARE** - Não assuma nem invente
2. **search-docs** - Use Laravel Boost primeiro
3. **CONSULTE** - Verifique a documentação do projeto
4. **PERGUNTE** - Se ainda não souber, pergunte ao usuário
5. **DOCUMENTE** - Registre a informação descoberta

### Hierarquia de Consulta:
```
1º → Laravel Boost MCP (search-docs, list-artisan-commands)
2º → Documentação do projeto (.taskmaster/docs/)
3º → Lições aprendidas (lessons-learned.md)
4º → Context7 MCP (documentação externa)
5º → Perguntar ao usuário
```

### Sinais de Alerta (PARE e VERIFIQUE):
- "Acho que..." → USE search-docs
- "Provavelmente..." → VERIFIQUE os fatos
- "Deve ser..." → CONFIRME antes de agir
- "Vou criar manualmente..." → USE LARAVEL BOOST PRIMEIRO
- Erro desconhecido → CONSULTE lessons-learned.md PRIMEIRO
- Comando Artisan → USE list-artisan-commands PRIMEIRO

---

## 📝 TEMPLATE: Registro de Lição Aprendida

```markdown
## [DATA] - Título do Problema

**Contexto:** Onde/quando ocorreu

**Erro:** Descrição do erro ou mensagem

**Causa Raiz:** O que causou o problema

**Solução:** Passos para resolver

**Laravel Boost ajudou?** Sim/Não - Como?

**Prevenção:** Como evitar no futuro

**Tags:** #laravel #filament #database #laravelboost #livewire #etc
```

---

## 🚀 COMANDOS RÁPIDOS DE REFERÊNCIA

### Task Master
```bash
task-master next
task-master set-status --id=<id> --status=<status>
task-master list
task-master expand --id=<id>
task-master analyze-complexity --research
```

### Laravel Boost MCP
```bash
# Buscar documentação (SEMPRE PRIMEIRO!)
search-docs(['query1', 'query2', 'query3'])
search-docs(['query'], packages=['filament'])

# Verificar comandos Artisan
list-artisan-commands('make:model')
list-artisan-commands('filament')

# Debug interativo
tinker("User::count()")
tinker("App\\Models\\User::first()")

# Consultar banco (read-only)
database-query("DESCRIBE credentials")
database-query("SELECT * FROM users LIMIT 5")

# Ver erros do navegador
browser-logs()

# Obter URL absoluta
get-absolute-url('/admin/credentials')
```

### Laravel Sail
```bash
vendor/bin/sail up -d
vendor/bin/sail artisan <comando>
vendor/bin/sail composer <comando>
vendor/bin/sail npm run build
vendor/bin/sail artisan test --filter=<teste>
vendor/bin/sail bin pint --dirty
```

---

> **LEMBRE-SE:** 
> - ⚡ **Laravel Boost MCP é OBRIGATÓRIO** - Sempre use `search-docs` antes de codar
> - 🐳 **TODOS** os comandos via `vendor/bin/sail` (Docker)
> - 🧪 Use `tinker` e `database-query` para debugar ao invés de `dd()`
> - 🐛 Use `browser-logs` ao invés de inspecionar manualmente o console
> - 📋 Use `list-artisan-commands` antes de executar comandos Artisan
> - 🎨 Rodar `pint --dirty` antes de commits
> - 🏗️ Verificar rebuild de assets após mudanças de frontend
> - 📖 A documentação existe para ser usada. Ignorá-la causa retrabalho
> 
> **🚀 Laravel Boost = Produtividade + Qualidade + Zero Alucinações**

===

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to enhance the user's satisfaction building Laravel applications.

## Foundational Context
This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.4.17
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


=== tests rules ===

## Test Enforcement

- Every change must be programmatically tested. Write a new test or update an existing test, then run the affected tests to make sure they pass.
- Run the minimum number of tests needed to ensure code quality and speed. Use `vendor/bin/sail artisan test` with a specific filename or filter.


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
