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
- [ ] Usei `browser-logs` do Laravel Boost para ver erros?
- [ ] Usei `tinker` para debugar se necessário?
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
> - Laravel Boost MCP é sua primeira linha de ação para código
> - **TODOS** os comandos via `vendor/bin/sail`
> - Rodar `pint --dirty` antes de commits
> - Verificar rebuild de assets após mudanças de frontend
> - A documentação existe para ser usada. Ignorá-la causa retrabalho