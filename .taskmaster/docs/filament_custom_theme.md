# Guia de Customização do Tema Filament (Custom Theme)

Este documento detalha a implementação completa do tema customizado do Filament v4 para o projeto CRED CRUD, seguindo a identidade visual da Força Aérea Brasileira (FAB) e utilizando Tailwind CSS v4.

---

## 1. Visão Geral

O projeto utiliza um **Custom Theme** oficial do Filament com a nova engine do **Tailwind CSS v4**, proporcionando:

- ✅ Controle total sobre o design sem quebrar compatibilidade
- ✅ Compilação otimizada via Vite com `@tailwindcss/vite`
- ✅ Identidade visual personalizada da Aeronáutica Brasileira
- ✅ Manutenção facilitada com variáveis CSS organizadas

### Stack Tecnológica

- **Laravel**: 12.39.0
- **Filament**: 4.2.2
- **Tailwind CSS**: 4.1.17 (engine nativa)
- **PHP**: 8.4.1
- **Vite**: 7.2.4

---

## 2. Estrutura de Arquivos

### Arquivos do Tema

```
resources/css/filament/admin/
├── theme.css              # CSS customizado principal
├── tailwind.config.js     # Configuração Tailwind (opcional)
└── postcss.config.js      # Configuração PostCSS (opcional)

app/Providers/Filament/
└── AdminPanelProvider.php # Configuração do painel

vite.config.js             # Configuração Vite com Tailwind 4

public/images/
└── secur.png             # Logo da FAB
```

### Descrição dos Arquivos

#### `resources/css/filament/admin/theme.css`
Arquivo principal contendo:
- Importação do tema base do Filament
- Definição de fontes com `@source` (sintaxe Tailwind v4)
- Variáveis CSS customizadas
- Overrides de componentes específicos

#### `vite.config.js`
Registra o tema para compilação:
```javascript
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/css/filament/admin/theme.css', // Tema customizado
            ],
            refresh: true,
        }),
        tailwindcss(), // Engine nativa do Tailwind 4
    ],
});
```

---

## 3. Configuração do Painel (AdminPanelProvider)

A ativação do tema e configurações estruturais de layout são feitas em `app/Providers/Filament/AdminPanelProvider.php`.

### Configuração Completa

```php
use Filament\Support\Colors\Color;

public function panel(Panel $panel): Panel
{
    return $panel
        ->default()
        ->id('admin')
        ->path('admin')
        ->login()
        
        // Paleta de cores da FAB
        ->colors([
            'primary' => Color::hex('#003DA5'), // Azul FAB
            'danger' => Color::Red,
            'gray' => Color::Slate,
            'info' => Color::hex('#0066CC'),    // Azul Céu
            'success' => Color::Green,
            'warning' => Color::Orange,
        ])
        
        // Tipografia
        ->font('Inter')
        
        // Tema customizado
        ->viteTheme('resources/css/filament/admin/theme.css')
        
        // Branding
        ->brandName('CRED CRUD - FAB')
        ->brandLogo(asset('images/secur.png'))
        ->brandLogoHeight('2.5rem')
        ->favicon(asset('favicon.ico'))
        
        // Layout
        ->maxContentWidth('full')
        ->sidebarWidth('13rem')
        
        // ... demais configurações
}
```

### Configurações de Layout Importantes

| Método | Descrição | Onde Ajustar |
|--------|-----------|--------------|
| `->sidebarWidth('13rem')` | Largura da sidebar lateral | **AdminPanelProvider** (não CSS) |
| `->maxContentWidth('full')` | Largura máxima do conteúdo | AdminPanelProvider |
| `->viteTheme()` | Carrega o CSS compilado do tema | AdminPanelProvider |

> ⚠️ **IMPORTANTE**: A largura da sidebar deve ser ajustada via `->sidebarWidth()`, pois o Filament controla essa dimensão dinamicamente.

---

## 4. Paleta de Cores

### Cores Principais da Aeronáutica

```css
:root {
    /* Cores Principais da Aeronáutica */
    --aero-blue-primary: #003DA5;   /* Azul FAB - Cor principal */
    --aero-blue-sky: #0066CC;       /* Azul céu - Intermediário */
    --aero-blue-light: #4A90E2;     /* Azul claro - Destaques */
    --aero-blue-dark: #002366;      /* Azul escuro - Textos */
    --aero-gold: #FFD700;           /* Dourado - Badges especiais */
    --aero-silver: #C0C0C0;         /* Prata - Secundário */
}
```

### Cores de Status

```css
:root {
    /* Cores de Status */
    --status-active: #10B981;       /* Verde - Ativa */
    --status-pending: #F59E0B;      /* Âmbar - Pendente */
    --status-expired: #EF4444;      /* Vermelho - Vencida */
    --status-denied: #6B7280;       /* Cinza - Negada */
    --status-processing: #8B5CF6;   /* Roxo - Em Processamento */
}
```

### Mapeamento de Badges

Os badges seguem o padrão do Enum `BadgeColor`:

| Status | Cor | Classe | Uso |
|--------|-----|--------|-----|
| Ativa | Verde (`success`) | `--status-active` | Credenciais ativas |
| Pendente | Âmbar (`warning`) | `--status-pending` | Em análise |
| Expirada | Vermelho (`danger`) | `--status-expired` | Fora da validade |
| Negada | Cinza (`gray`) | `--status-denied` | Acesso negado |
| Processando | Roxo (`info`) | `--status-processing` | Em processamento |

---

## 5. Customizações CSS (`theme.css`)

### 5.1. Estrutura do Arquivo

```css
@import '../../../../vendor/filament/filament/resources/css/theme.css';

@source '../../../../app/Filament/**/*.php';
@source '../../../../resources/views/filament/**/*.blade.php';
@source '../../../../vendor/filament/**/*.blade.php';

:root {
    /* Variáveis CSS */
}

/* Customizações e overrides */
```

### 5.2. Sintaxe `@source` (Tailwind v4)

A diretiva `@source` é **exclusiva do Tailwind CSS v4** e substitui o `content` do `tailwind.config.js`:

```css
@source '../../../../app/Filament/**/*.php';
@source '../../../../resources/views/filament/**/*.blade.php';
@source '../../../../vendor/filament/**/*.blade.php';
```

Isso instrui o Tailwind 4 a escanear esses arquivos em busca de classes CSS.

### 5.3. Overrides e `!important`

Devido à alta especificidade do CSS do Filament, é **pragmático e necessário** usar `!important` em alguns casos:

#### Exemplo: Padding de Células de Tabela

```css
.fi-ta-cell {
    padding-left: 0.75rem !important;
    padding-right: 0.75rem !important;
    padding-top: 0.5rem !important;
    padding-bottom: 0.5rem !important;
}
```

#### Exemplo: Layout Principal

```css
.fi-main {
    @apply px-4 md:px-6;
    padding-top: 0.75rem !important;
}
```

> 💡 **Dica**: Para overrides "fortes" em componentes do Filament, propriedades CSS padrão com `!important` são mais eficazes e seguras contra conflitos de parser do que `@apply`.

### 5.4. Uso de Variáveis CSS

As variáveis podem ser reutilizadas em toda a aplicação:

```css
.custom-badge {
    background-color: var(--aero-gold);
    color: var(--aero-blue-dark);
}
```

---

## 6. Tipografia

### Configuração de Fonte

- **Fonte Principal**: Inter
- **Fallback**: system-ui, sans-serif
- **Aplicação**: Definida no `AdminPanelProvider` via `->font('Inter')`

### Fonte Inter

A fonte Inter é carregada automaticamente pelo Filament. Para customizar:

```css
:root {
    font-family: 'Inter', system-ui, -apple-system, sans-serif;
}
```

---

## 7. Branding

### Configuração de Identidade Visual

| Elemento | Valor | Localização |
|----------|-------|-------------|
| Nome da Aplicação | CRED CRUD - FAB | AdminPanelProvider |
| Logo | `/public/images/secur.png` | AdminPanelProvider |
| Altura do Logo | 2.5rem | AdminPanelProvider |
| Favicon | `/public/favicon.ico` | AdminPanelProvider |

### Personalização da Logo

Para alterar a logo:

1. Substitua o arquivo em `/public/images/secur.png`
2. Ou altere o caminho no `AdminPanelProvider`:
   ```php
   ->brandLogo(asset('images/nova-logo.png'))
   ```

---

## 8. Processo de Build

### Desenvolvimento (Hot Module Replacement)

```bash
vendor/bin/sail npm run dev
# ou
npm run dev
```

Com HMR ativo, as alterações são refletidas instantaneamente no navegador.

### Produção (Build Final)

```bash
vendor/bin/sail npm run build
# ou
npm run build
```

> ⚠️ **IMPORTANTE**: Se as alterações de estilo não aparecerem, execute `npm run build` e limpe os caches:

```bash
vendor/bin/sail artisan config:clear
vendor/bin/sail artisan view:clear
vendor/bin/sail artisan cache:clear
```

---

## 9. Testes

### Testes de Recursos do Filament

Todos os testes passaram com sucesso:

```bash
vendor/bin/sail artisan test --filter=CredentialResourceTest
```

**Cobertura de Testes:**
- ✅ CRUD de Credenciais
- ✅ Listagem e filtros de tabelas
- ✅ Formulários e validações
- ✅ Badges e componentes visuais
- ✅ Autenticação e autorização

---

## 10. Dicas de Manutenção e Boas Práticas

### 10.1. Nunca Editar `vendor/`

❌ **NUNCA** edite arquivos em `vendor/filament/`

✅ **SEMPRE** faça overrides no `theme.css`

### 10.2. Tailwind v4 - Diferenças Importantes

- ✅ Use `@source` no CSS ao invés de `content` no config
- ✅ Sintaxe `@apply` ainda é suportada
- ✅ Para overrides fortes, use propriedades CSS padrão com `!important`
- ✅ A engine nativa é mais rápida e eficiente

### 10.3. Sidebar e Layout

Para ajustar a largura da sidebar:

```php
// ✅ Correto - no AdminPanelProvider
->sidebarWidth('14rem')

// ❌ Errado - não funciona via CSS
.fi-sidebar { width: 14rem; }
```

### 10.4. Responsividade

Utilize as media queries do Tailwind ou CSS puro:

```css
/* Com Tailwind */
.custom-element {
    @apply px-4 md:px-6 lg:px-8;
}

/* CSS puro */
@media (max-width: 768px) {
    .custom-element {
        padding: 1rem;
    }
}
```

### 10.5. Dark Mode

O projeto está preparado para dark mode. Para customizações:

```css
:root {
    --custom-bg: #ffffff;
}

:root.dark {
    --custom-bg: #1a1a1a;
}
```

---

## 11. Componentes Personalizados

### Cards e Dashboard

- Cards com sombra suave (`shadow-sm`)
- Hover effects para melhor feedback visual
- Layout responsivo com grid system do Tailwind

### Badges Customizados

Para criar badges com cores da Aeronáutica:

```php
use App\Enums\BadgeColor;

Badge::make('status')
    ->color(BadgeColor::tryFrom($record->status)?->getFilamentColor())
```

---

## 12. Compatibilidade

| Tecnologia | Versão | Status |
|------------|--------|--------|
| Laravel | 12.39.0 | ✅ |
| Filament | 4.2.2 | ✅ |
| Tailwind CSS | 4.1.17 | ✅ |
| PHP | 8.4.1 | ✅ |
| Dark Mode | Habilitado | ✅ |
| Responsivo | Mobile-first | ✅ |

---

## 13. Troubleshooting

### Problema: Estilos não aparecem após alterações

**Solução:**
```bash
vendor/bin/sail npm run build
vendor/bin/sail artisan view:clear
```

### Problema: Erro de compilação do Tailwind

**Solução:**
- Verifique se `@tailwindcss/vite` está instalado
- Confirme que `vite.config.js` tem `tailwindcss()` nos plugins
- Execute `npm install` novamente

### Problema: Cores não aplicadas

**Solução:**
- Verifique se `->colors()` está no `AdminPanelProvider`
- Limpe o cache com `php artisan config:clear`
- Reconstrua com `npm run build`

---

## 14. Futuras Melhorias

### Roadmap de Temas

1. **Animações Personalizadas**
   - Transições suaves em modais
   - Loading states customizados
   - Microinterações

2. **Dark Mode Avançado**
   - Paleta específica para modo escuro
   - Toggle de tema manual
   - Preferência salva no banco

3. **Componentes Adicionais**
   - Cards de estatísticas customizados
   - Timeline de eventos
   - Notificações personalizadas

4. **Acessibilidade**
   - Contraste WCAG AAA
   - Suporte a leitores de tela
   - Navegação por teclado otimizada

---

## 15. Referências

- [Documentação oficial Filament 4 - Themes](https://filamentphp.com/docs/4.x/panels/themes)
- [Documentação oficial Filament 4 - Appearance](https://filamentphp.com/docs/4.x/panels/appearance)
- [Tailwind CSS v4 Documentation](https://tailwindcss.com/docs)
- [Vite Plugin Tailwind CSS](https://tailwindcss.com/docs/installation/vite)

---

**Documento criado em:** 22/11/2025  
**Última atualização:** 22/11/2025  
**Versão:** 2.0 - Consolidado e expandido
