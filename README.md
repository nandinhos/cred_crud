# 🛡️ Sistema de Credenciais - Laravel 12 + Filament 4

<p align="center">
    <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="300" alt="Laravel Logo">
</p>

<p align="center">
    <img src="https://img.shields.io/badge/Laravel-12.39.0-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel">
    <img src="https://img.shields.io/badge/Filament-4.2.2-F59E0B?style=for-the-badge&logo=livewire&logoColor=white" alt="Filament">
    <img src="https://img.shields.io/badge/PHP-8.3+-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP">
    <img src="https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL">
    <img src="https://img.shields.io/badge/Docker-Ready-2496ED?style=for-the-badge&logo=docker&logoColor=white" alt="Docker">
</p>

## 🎯 Sobre o Projeto

Sistema moderno de gerenciamento de credenciais de segurança desenvolvido com **Laravel 12** e **Filament 4**. Oferece interface administrativa completa, sistema de permissões robusto e funcionalidades avançadas de CRUD com validações inteligentes.

### ✨ Principais Funcionalidades

- 🛡️ **Gestão Completa de Credenciais** - CRUD completo com validações
- 🎨 **Interface Moderna** - Painel administrativo Filament 4 
- 🔐 **Sistema de Permissões** - Spatie Permission + Filament Shield
- 📊 **Filtros Avançados** - Busca e filtragem inteligente
- 🔄 **Soft Delete** - Exclusão reversível de registros
- 📱 **Design Responsivo** - Funciona em todos os dispositivos
- 🚨 **Indicadores Visuais** - Status de validade por cores
- 📝 **Validações Inteligentes** - Regras de negócio automatizadas

## 🚀 Instalação Rápida

### 📋 Pré-requisitos
- 🐳 **Docker** e **Docker Compose**
- 🐘 **PHP 8.3+** 
- 🎼 **Composer 2.6+**
- 📦 **Node.js 18+**

### ⚡ Setup Automatizado
```bash
# 1. Clonar repositório
git clone https://github.com/nandinhos/cred_crud.git
cd cred_crud

# 2. Executar setup automático
chmod +x setup.sh
./setup.sh
```

### 🛠️ Instalação Manual (Via Sail)
```bash
# 1. Configurar ambiente
cp .env.example .env

# 2. Iniciar containers
./vendor/bin/sail up -d

# 3. Instalar dependências
./vendor/bin/sail composer install
./vendor/bin/sail npm install && ./vendor/bin/sail npm run build

# 4. Banco de dados
./vendor/bin/sail artisan migrate --seed
```

### 🌐 Acesso ao Sistema
- **URL Principal**: `http://localhost/`
- **Painel Admin**: `http://localhost/admin`
- **Login Rápido**: `http://localhost/login-admin`

### 🔐 Credenciais Padrão
- **Email**: `admin@admin.com`
- **Senha**: `password`

## 📚 Documentação Completa

### 📖 Guias Disponíveis
- 📋 **[INSTALL.md](INSTALL.md)** - Guia completo de instalação
- 🔧 **[Best Practices](.taskmaster/docs/best-practices-laravel12-filament4.md)** - Melhores práticas
- 📚 **[Useful Commands](.taskmaster/docs/useful-commands.md)** - Comandos úteis
- 🔍 **[Lessons Learned](.taskmaster/docs/lessons-learned.md)** - Problemas e soluções

## 🛠️ Tecnologias Utilizadas

### 🏗️ Backend
- **Laravel 12.39.0** - Framework PHP moderno
- **PHP 8.3+** - Linguagem de programação
- **MySQL 8.0** - Banco de dados
- **Spatie Permission** - Sistema de permissões

### 🎨 Frontend
- **Filament 4.2.2** - Painel administrativo
- **Livewire 3** - Interações reativas
- **TailwindCSS** - Framework CSS
- **Alpine.js** - JavaScript framework

### 🐳 Infrastructure
- **Docker** - Containerização
- **Laravel Sail** - Ambiente de desenvolvimento
- **Vite** - Build tool e bundler
- **NPM** - Gerenciador de pacotes

## 📊 Funcionalidades Detalhadas

### 🛡️ Gestão de Credenciais
- ✅ **Criar, Editar, Listar, Deletar** credenciais
- ✅ **Campos**: FSCS, Nome, Nível de Sigilo, Datas
- ✅ **Validações**: Unicidade, datas futuras, campos obrigatórios
- ✅ **Soft Delete**: Exclusão reversível
- ✅ **Busca Global**: Pesquisa em todos os campos

### 🎨 Interface Administrativa
- ✅ **Dashboard Moderno** com widgets informativos
- ✅ **Formulários Responsivos** com seções organizadas
- ✅ **Tabelas Inteligentes** com ordenação e filtros
- ✅ **Ações em Massa** otimizadas
- ✅ **Theme Claro/Escuro** automático

### 🔐 Segurança e Permissões
- ✅ **Autenticação Robusta** com Laravel Sanctum
- ✅ **Sistema de Roles** granular
- ✅ **Permissões por Recurso** customizáveis
- ✅ **Filament Shield** para gerenciamento visual

### 📈 Performance e Qualidade
- ✅ **Assets Otimizados** com Vite
- ✅ **Cache Inteligente** em múltiplas camadas
- ✅ **Autoload Otimizado** (9237+ classes)
- ✅ **Testes Automatizados** com Pest PHP

## 🎯 Estrutura do Projeto

```
📦 cred_crud/
├── 🚀 app/Filament/           # Recursos Filament
├── 📊 database/               # Migrações e seeders
├── 📚 .taskmaster/docs/       # Documentação técnica
├── 🐳 docker-compose.yml     # Configuração Docker
├── ⚡ setup.sh               # Script de instalação
├── 📋 INSTALL.md             # Guia de instalação
└── 🛠️ úteis comandos/        # Scripts de automação
```

## 🚀 Comandos Úteis

### 📦 Container
```bash
# Acessar container
docker-compose exec laravel.test bash

# Ver logs
docker-compose logs laravel.test -f

# Reiniciar serviços
docker-compose restart
```

### 🧹 Manutenção
```bash
# Limpar caches
docker-compose exec laravel.test php artisan optimize:clear

# Recompilar assets
docker-compose exec laravel.test npm run build

# Backup do banco
docker-compose exec laravel.test mysqldump -u sail -psail cred_crud > backup.sql
```

## 🔧 Troubleshooting

### ❌ Problemas Comuns

**Container não inicia:**
```bash
docker-compose down && docker-compose up -d --build
```

**Erro de assets:**
```bash
docker-compose exec laravel.test npm run build
```

**Erro 403 Forbidden em /admin:**
- Verifique se o usuário possui a role correta.
- Tente acessar `/login-admin` diretamente.
- Verifique o método `canAccessPanel` no User model.

**Erro de permissões:**
```bash
sudo chown -R $USER:$USER .
sudo chmod -R 755 storage bootstrap/cache
```

## 🤝 Contribuindo

1. **Fork** o projeto
2. **Crie** uma branch para sua feature (`git checkout -b feature/AmazingFeature`)
3. **Commit** suas mudanças (`git commit -m 'feat: adiciona AmazingFeature'`)
4. **Push** para a branch (`git push origin feature/AmazingFeature`)
5. **Abra** um Pull Request

### 📝 Padrão de Commits
Usamos [Conventional Commits](https://www.conventionalcommits.org/):
- `feat:` nova funcionalidade
- `fix:` correção de bug
- `docs:` documentação
- `style:` formatação
- `refactor:` refatoração
- `test:` testes

## 📄 Licença

Este projeto está licenciado sob a licença MIT. Veja o arquivo [LICENSE](LICENSE) para mais detalhes.

## 👨‍💻 Autor

**Desenvolvido com ❤️ por [nandinhos](https://github.com/nandinhos)**

---

<p align="center">
    <strong>🌟 Se este projeto foi útil, considere dar uma ⭐ no repositório! 🌟</strong>
</p>

## 📞 Suporte

- 📧 **Issues**: [GitHub Issues](https://github.com/nandinhos/cred_crud/issues)
- 📚 **Wiki**: [GitHub Wiki](https://github.com/nandinhos/cred_crud/wiki)
- 💬 **Discussões**: [GitHub Discussions](https://github.com/nandinhos/cred_crud/discussions)

---

**📅 Última atualização**: Novembro 2024  
**🔧 Versão**: 1.0.0  
**📊 Status**: Production Ready