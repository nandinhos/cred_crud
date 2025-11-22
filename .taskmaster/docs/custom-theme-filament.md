# Custom Theme Filament 4 - Aeronáutica Brasileira

## Visão Geral

Este documento descreve a implementação do tema customizado para o painel administrativo Filament 4, seguindo a identidade visual da Força Aérea Brasileira (FAB).

## Paleta de Cores

### Cores Principais
- **Primary (#003DA5)**: Azul FAB - Cor principal do sistema
- **Info (#0066CC)**: Azul Céu - Cor secundária e informativa
- **Success**: Verde (padrão Filament) - Status positivo
- **Warning**: Laranja (padrão Filament) - Alertas
- **Danger**: Vermelho (padrão Filament) - Erros e ações críticas
- **Gray**: Slate (padrão Filament) - Textos e backgrounds neutros

### Cores Customizadas Adicionais
- **Dourado (#FFD700)**: Para badges especiais (TCMS)
- **Prata (#C0C0C0)**: Secundário

## Implementação

### Configuração no AdminPanelProvider

O tema é configurado diretamente no `app/Providers/Filament/AdminPanelProvider.php`:

```php
->colors([
    'primary' => Color::hex('#003DA5'), // Azul FAB
    'danger' => Color::Red,
    'gray' => Color::Slate,
    'info' => Color::hex('#0066CC'), // Azul Céu
    'success' => Color::Green,
    'warning' => Color::Orange,
])
->font('Inter')
->brandName('CRED CRUD - FAB')
->brandLogo(asset('images/secur.png'))
->brandLogoHeight('2.5rem')
```

## Tipografia

- **Fonte Principal**: Inter
- **Fallback**: system-ui, sans-serif

## Branding

- **Nome da Aplicação**: CRED CRUD - FAB
- **Logo**: `/public/images/secur.png`
- **Altura do Logo**: 2.5rem
- **Favicon**: `/public/favicon.ico`

## Componentes Personalizados

### Badges
Os badges seguem o padrão do Enum `BadgeColor`:
- **Success** (Verde): Credenciais ativas
- **Warning** (Âmbar): Status pendente
- **Danger** (Vermelho): Credenciais expiradas ou negadas
- **Info** (Azul): Informações gerais

### Cards e Dashboard
- Cards com sombra suave
- Hover effects para melhor feedback visual
- Layout responsivo

## Arquivos de Tema (Referência)

### Estrutura preparada para futuras customizações

```
resources/css/filament/admin/
├── tailwind.config.js  # Configuração Tailwind específica
├── theme.css          # CSS customizado (preparado para uso futuro)
└── postcss.config.js  # Configuração PostCSS
```

**Nota**: Atualmente, o tema CSS customizado não está sendo utilizado devido a incompatibilidades com o Tailwind 3.4.x e Filament 4. A personalização é feita exclusivamente através do `AdminPanelProvider`.

## Compatibilidade

- ✅ Laravel 12
- ✅ Filament 4.2
- ✅ Tailwind CSS 3.4.18
- ✅ Dark Mode Ready
- ✅ Responsivo

## Testes

Todos os testes de recursos do Filament passaram com sucesso:
- ✅ CRUD de Credenciais
- ✅ Listagem e filtros
- ✅ Formulários e validações
- ✅ Badges e componentes visuais

## Futuras Melhorias

Para futuras versões, quando o Filament suportar melhor temas customizados com Tailwind 4:

1. Ativar o `viteTheme` no AdminPanelProvider
2. Utilizar o `theme.css` preparado com customizações avançadas
3. Implementar animações e transições personalizadas
4. Adicionar mais variações de cores para dark mode

## Referências

- [Documentação oficial Filament 4 - Themes](https://filamentphp.com/docs/4.x/panels/themes)
- [Documentação oficial Filament 4 - Appearance](https://filamentphp.com/docs/4.x/panels/appearance)
- [Tailwind CSS 3.4 Documentation](https://tailwindcss.com/docs)
