# Plano de Refatoração - Tema Aeronáutica

**Branch:** `feature/theme-aeronautica`  
**Data Início:** 21/11/2024  
**Responsável:** Rovo Dev AI  
**Objetivo:** Implementar tema customizado da Aeronáutica Brasileira no Filament 4

---

## 📋 ESCOPO DO PROJETO

### Objetivo Geral
Criar um tema profissional e institucional para o sistema CRED CRUD, refletindo a identidade visual da Força Aérea Brasileira (FAB), com cores azuis características e elementos visuais que transmitam segurança, profissionalismo e hierarquia militar.

### Resultados Esperados
- ✅ Interface com identidade visual da Aeronáutica
- ✅ Tema customizado totalmente funcional
- ✅ Todos os componentes Filament estilizados
- ✅ Dashboard com widgets informativos
- ✅ Zero regressões (todos os testes passando)
- ✅ Documentação completa das customizações

---

## 🎨 PALETA DE CORES

### Cores Principais
```css
--aero-blue-primary:   #003DA5  /* Azul FAB - Cor principal */
--aero-blue-sky:       #0066CC  /* Azul céu - Intermediário */
--aero-blue-light:     #4A90E2  /* Azul claro - Destaques */
--aero-blue-dark:      #002366  /* Azul escuro - Textos */
--aero-gold:           #FFD700  /* Dourado - Badges especiais */
--aero-silver:         #C0C0C0  /* Prata - Secundário */
```

### Cores de Status
```css
--status-active:       #10B981  /* Verde - Ativa */
--status-pending:      #F59E0B  /* Âmbar - Pendente */
--status-expired:      #EF4444  /* Vermelho - Vencida */
--status-denied:       #6B7280  /* Cinza - Negada */
--status-processing:   #8B5CF6  /* Roxo - Em Processamento */
```

---

## 📦 FASES DO PROJETO

### FASE 1: Configuração do Tema (2-3 horas)
**Objetivo:** Criar e configurar o tema customizado no Filament

#### Tarefa 1.1: Criar Tema Filament
- [ ] Executar `php artisan make:filament-theme`
- [ ] Configurar estrutura de pastas
- [ ] Registrar tema no AdminPanelProvider
- [ ] Testar: Verificar se o tema é carregado

**Teste de Verificação:**
```bash
vendor/bin/sail artisan tinker --execute="
echo 'Verificando registro do tema...' . PHP_EOL;
\$panel = Filament::getCurrentPanel();
echo 'Theme: ' . (\$panel->hasTheme() ? 'Registrado' : 'Não registrado') . PHP_EOL;
"
```

#### Tarefa 1.2: Configurar Cores Brand
- [ ] Atualizar `AdminPanelProvider` com cores da Aeronáutica
- [ ] Definir cor primária (#003DA5)
- [ ] Definir cores de status
- [ ] Configurar dark mode (opcional)
- [ ] Testar: Visualizar no navegador

**Código de Implementação:**
```php
->colors([
    'primary' => Color::hex('#003DA5'),
    'sky' => Color::hex('#0066CC'),
    'gold' => Color::hex('#FFD700'),
])
```

**Teste de Verificação:**
```bash
# Acessar /admin e verificar se as cores mudaram
vendor/bin/sail artisan route:list --path=admin | head -5
```

#### Tarefa 1.3: Logo e Identidade Visual
- [ ] Adicionar logo da FAB (ou placeholder)
- [ ] Configurar favicon
- [ ] Definir título do painel
- [ ] Ajustar brand name
- [ ] Testar: Verificar logo no header

**Teste de Verificação:**
- Acessar /admin
- Verificar presença do logo no header
- Verificar favicon no browser tab

---

### FASE 2: Customização de Componentes (3-4 horas)
**Objetivo:** Estilizar todos os componentes Filament

#### Tarefa 2.1: Customizar Tabelas
- [ ] Estilizar headers das tabelas
- [ ] Ajustar bordas e espaçamentos
- [ ] Customizar hover states
- [ ] Aplicar cores da Aeronáutica
- [ ] Testar: Listar credenciais

**Arquivo:** `resources/css/filament/aeronautica/theme.css`

**Teste de Verificação:**
```bash
vendor/bin/sail artisan test tests/Feature/Filament/CredentialResourceTest.php
```

#### Tarefa 2.2: Customizar Formulários
- [ ] Estilizar inputs e selects
- [ ] Customizar labels
- [ ] Ajustar placeholders
- [ ] Aplicar focus states azul
- [ ] Testar: Criar/editar credencial

**Teste de Verificação:**
```bash
vendor/bin/sail artisan test --filter="pode criar credencial"
```

#### Tarefa 2.3: Customizar Botões e Actions
- [ ] Aplicar estilo azul nos botões primários
- [ ] Ajustar hover e active states
- [ ] Customizar ícones
- [ ] Manter tooltips funcionais
- [ ] Testar: Ações da tabela

**CSS Exemplo:**
```css
.fi-btn-primary {
    background: var(--aero-blue-primary);
    border-color: var(--aero-blue-primary);
}

.fi-btn-primary:hover {
    background: var(--aero-blue-sky);
    transform: translateY(-1px);
}
```

#### Tarefa 2.4: Customizar Badges
- [ ] Badge azul para CRED
- [ ] Badge dourado para TCMS
- [ ] Badges de status coloridos
- [ ] Ajustar tamanhos e fontes
- [ ] Testar: Visualizar na tabela

**Teste de Verificação:**
- Acessar lista de credenciais
- Verificar cores dos badges
- Confirmar legibilidade

#### Tarefa 2.5: Customizar Cards e Widgets
- [ ] Aplicar borda superior azul
- [ ] Customizar sombras
- [ ] Ajustar espaçamentos internos
- [ ] Adicionar hover effects
- [ ] Testar: Dashboard

---

### FASE 3: Dashboard e Widgets (2-3 horas)
**Objetivo:** Criar dashboard informativo com estatísticas

#### Tarefa 3.1: Stats Widgets
- [ ] Widget: Total de Credenciais
- [ ] Widget: Credenciais Ativas
- [ ] Widget: Vencendo em 30 dias
- [ ] Widget: Credenciais Vencidas
- [ ] Testar: Dashboard carrega

**Implementação:**
```bash
vendor/bin/sail artisan make:filament-widget StatsOverview --stats
```

**Teste de Verificação:**
```php
it('dashboard exibe stats corretamente', function () {
    $this->actingAs($superAdmin);
    
    $response = $this->get('/admin');
    
    $response->assertSuccessful();
    $response->assertSee('Total de Credenciais');
    $response->assertSee('Ativas');
    $response->assertSee('Vencendo');
});
```

#### Tarefa 3.2: Chart Widget
- [ ] Criar widget de gráfico
- [ ] Gráfico: Credenciais por mês
- [ ] Aplicar cores da Aeronáutica
- [ ] Testar: Dados corretos

**Implementação:**
```bash
vendor/bin/sail artisan make:filament-widget CredentialsChart --chart
```

#### Tarefa 3.3: Recent Activity Widget
- [ ] Widget de atividades recentes
- [ ] Listar últimas 5 credenciais
- [ ] Exibir status e tipo
- [ ] Testar: Dados em tempo real

---

### FASE 4: Sidebar e Navegação (1-2 horas)
**Objetivo:** Customizar menu lateral e navegação

#### Tarefa 4.1: Customizar Sidebar
- [ ] Aplicar cores da Aeronáutica
- [ ] Customizar ícones dos menus
- [ ] Ajustar hover states
- [ ] Adicionar separadores visuais
- [ ] Testar: Navegação funcional

**CSS Customização:**
```css
.fi-sidebar-nav-item-active {
    background: linear-gradient(90deg, #003DA5 0%, transparent 100%);
    border-left: 4px solid #FFD700;
}
```

#### Tarefa 4.2: Customizar Topbar
- [ ] Header com gradiente azul
- [ ] Badge de role do usuário
- [ ] Menu de perfil estilizado
- [ ] Testar: Responsividade

---

### FASE 5: Testes e Validação (2-3 horas)
**Objetivo:** Garantir que tudo funciona perfeitamente

#### Tarefa 5.1: Testes Automatizados
- [ ] Executar todos os testes existentes
- [ ] Criar testes específicos para tema
- [ ] Verificar componentes customizados
- [ ] Testar em diferentes resoluções

**Comandos de Teste:**
```bash
# Todos os testes
vendor/bin/sail artisan test

# Testes de Feature
vendor/bin/sail artisan test tests/Feature/

# Testes do Filament
vendor/bin/sail artisan test tests/Feature/Filament/
```

**Critério de Sucesso:**
- ✅ 100% dos testes passando
- ✅ Zero regressões
- ✅ Tempo de execução < 10 segundos

#### Tarefa 5.2: Testes Visuais
- [ ] Testar em Chrome
- [ ] Testar em Firefox
- [ ] Testar em Safari (se disponível)
- [ ] Testar modo claro e escuro
- [ ] Testar responsividade (mobile, tablet, desktop)

**Checklist de Validação Visual:**
```markdown
□ Logo aparece corretamente
□ Cores da Aeronáutica aplicadas
□ Badges com cores corretas
□ Tabelas legíveis e bem espaçadas
□ Formulários funcionais e bonitos
□ Botões com hover effects
□ Dashboard com widgets corretos
□ Sidebar navegável
□ Sem elementos quebrados
□ Performance aceitável (< 2s carregamento)
```

#### Tarefa 5.3: Validação de Performance
- [ ] Medir tempo de carregamento
- [ ] Verificar tamanho dos assets
- [ ] Otimizar se necessário
- [ ] Testar com cache limpo

**Comandos:**
```bash
# Limpar cache
vendor/bin/sail artisan cache:clear
vendor/bin/sail artisan view:clear
vendor/bin/sail artisan config:clear

# Recompilar assets
vendor/bin/sail npm run build

# Verificar tamanho
du -sh public/build/assets/
```

---

### FASE 6: Documentação e Finalização (1-2 horas)
**Objetivo:** Documentar e preparar para merge

#### Tarefa 6.1: Documentação Técnica
- [ ] Documentar estrutura do tema
- [ ] Listar variáveis CSS customizadas
- [ ] Explicar componentes criados
- [ ] Criar guia de manutenção

**Arquivo:** `.taskmaster/docs/theme-aeronautica-documentation.md`

#### Tarefa 6.2: Screenshots e Preview
- [ ] Capturar screenshots do dashboard
- [ ] Capturar screenshots das páginas principais
- [ ] Atualizar README com imagens
- [ ] Criar demo visual (se necessário)

#### Tarefa 6.3: Atualizar Lições Aprendidas
- [ ] Adicionar entry sobre customização de temas
- [ ] Documentar desafios encontrados
- [ ] Listar melhores práticas
- [ ] Adicionar referências úteis

---

## 🧪 ESTRATÉGIA DE TESTES

### Testes Unitários
```php
// tests/Unit/ThemeTest.php
test('tema aeronautica está registrado', function () {
    $panel = Filament::getCurrentPanel();
    expect($panel->getId())->toBe('admin');
});

test('cores da aeronautica estao definidas', function () {
    $colors = config('filament.theme.colors');
    expect($colors)->toHaveKey('primary');
});
```

### Testes de Feature
```php
// tests/Feature/Filament/ThemeTest.php
test('dashboard carrega com tema customizado', function () {
    $user = User::factory()->create();
    $user->assignRole('super_admin');
    
    $response = $this->actingAs($user)->get('/admin');
    
    $response->assertSuccessful();
    $response->assertSee('CRED CRUD');
});

test('widgets do dashboard são renderizados', function () {
    $user = User::factory()->create();
    $user->assignRole('super_admin');
    
    $response = $this->actingAs($user)->get('/admin');
    
    $response->assertSee('Total de Credenciais');
    $response->assertSee('Ativas');
});
```

### Testes de Regressão
```bash
# Executar TODOS os testes existentes
vendor/bin/sail artisan test

# Verificar que nada quebrou
# Todos devem passar: 53 testes, 103 assertions
```

---

## 📊 MÉTRICAS DE SUCESSO

### Critérios de Aceitação
- ✅ **Identidade Visual**: Cores da Aeronáutica aplicadas em 100% dos componentes
- ✅ **Funcionalidade**: Zero perda de funcionalidades existentes
- ✅ **Testes**: 100% dos testes passando (mínimo 53 testes)
- ✅ **Performance**: Carregamento < 3 segundos
- ✅ **Responsividade**: Funcional em mobile, tablet e desktop
- ✅ **Acessibilidade**: Contraste adequado (WCAG AA)
- ✅ **Documentação**: Completa e clara

### KPIs
| Métrica | Meta | Como Medir |
|---------|------|------------|
| Testes Passando | 100% | `vendor/bin/sail artisan test` |
| Tempo de Carregamento | < 3s | DevTools Network Tab |
| Tamanho dos Assets | < 500KB | `du -sh public/build/` |
| Cobertura de Código | > 80% | PHPUnit coverage (opcional) |
| Bugs Visuais | 0 | Testes manuais |

---

## 🔄 WORKFLOW DE DESENVOLVIMENTO

### Processo por Tarefa
1. **Implementar** a tarefa
2. **Testar** automaticamente (comandos fornecidos)
3. **Validar** visualmente no navegador
4. **Commit** com mensagem descritiva
5. **Documentar** problemas ou soluções

### Padrão de Commits
```
feat(theme): adiciona cores da aeronáutica ao tema
fix(theme): corrige hover state dos botões
style(theme): ajusta espaçamentos do dashboard
test(theme): adiciona testes para widgets
docs(theme): documenta customizações do tema
```

### Checklist Antes de Cada Commit
- [ ] Código formatado (Pint)
- [ ] Testes passando
- [ ] Visual validado
- [ ] Sem console.log ou debug code
- [ ] Comentários em código complexo

---

## 🚨 PLANO DE ROLLBACK

### Se Algo Der Errado
```bash
# Voltar para main
git checkout main

# Deletar branch (se necessário)
git branch -D feature/theme-aeronautica

# Recriar branch limpa
git checkout -b feature/theme-aeronautica

# Ou fazer revert de commits específicos
git revert <commit-hash>
```

### Backup de Segurança
```bash
# Antes de começar grandes mudanças
git tag backup-before-theme

# Para restaurar (se necessário)
git reset --hard backup-before-theme
```

---

## 📅 CRONOGRAMA ESTIMADO

| Fase | Duração | Dias |
|------|---------|------|
| FASE 1: Configuração | 2-3h | Dia 1 manhã |
| FASE 2: Componentes | 3-4h | Dia 1 tarde |
| FASE 3: Dashboard | 2-3h | Dia 2 manhã |
| FASE 4: Navegação | 1-2h | Dia 2 tarde |
| FASE 5: Testes | 2-3h | Dia 2 tarde |
| FASE 6: Docs | 1-2h | Dia 3 manhã |
| **TOTAL** | **11-17h** | **2-3 dias** |

---

## 📚 REFERÊNCIAS

### Documentação Oficial
- Filament Themes: https://filamentphp.com/docs/4.x/panels/themes
- Tailwind CSS: https://tailwindcss.com/docs
- Laravel Vite: https://laravel.com/docs/12.x/vite

### Inspirações Visuais
- Tailwind UI: https://tailwindui.com
- Filament Demo: https://demo.filamentphp.com
- Material Design: https://material.io/design

### Cores e Design
- Paleta da FAB: Baseado em análise visual
- Heroicons: https://heroicons.com (ícones)
- Coolors: https://coolors.co (paleta)

---

## ✅ PRÓXIMOS PASSOS

1. **Revisar este plano** - Confirmar se está claro e completo
2. **Iniciar FASE 1** - Criar tema Filament
3. **Executar tarefa por tarefa** - Seguir o plano
4. **Testar continuamente** - Não acumular problemas
5. **Documentar progresso** - Atualizar este documento

---

**Status:** 📋 Planejamento Concluído  
**Branch Criada:** ✅ `feature/theme-aeronautica`  
**Pronto para Iniciar:** ✅ Aguardando aprovação

