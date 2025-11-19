# 🚀 Instalação - Sistema Laravel 12 + Filament 4

## 📋 Pré-requisitos

### Sistema Operacional
- ✅ **Linux/macOS/Windows** com Docker
- ✅ **Git** instalado
- ✅ **Docker** e **Docker Compose** funcionando

### Versões Requeridas
- 🐘 **PHP**: 8.3+
- 🚀 **Laravel**: 12.39.0
- 🎨 **Filament**: 4.2.2
- 🐳 **Docker**: 20.10+
- 📦 **Node.js**: 18+
- 🎼 **Composer**: 2.6+

---

## 🔄 Instalação Completa

### 1️⃣ Clonar Repositório
```bash
# Clonar o projeto
git clone [URL_DO_SEU_REPOSITORIO]
cd [NOME_DO_PROJETO]
```

### 2️⃣ Configurar Ambiente Docker
```bash
# Configurar variáveis Docker
export WWWGROUP=1000
export WWWUSER=1000

# Ou criar arquivo .env.docker (opcional)
echo "WWWGROUP=1000" > .env.docker
echo "WWWUSER=1000" >> .env.docker
```

### 3️⃣ Configurar Arquivo .env
```bash
# Copiar arquivo de exemplo
cp .env.example .env

# Editar configurações principais (se necessário)
# As configurações Docker já estão corretas:
# DB_HOST=mysql
# DB_PORT=3306
# DB_DATABASE=cred_crud
# DB_USERNAME=sail
# DB_PASSWORD=sail
```

### 4️⃣ Subir Containers Docker
```bash
# Subir containers em background
docker-compose up -d

# Verificar se containers estão rodando
docker-compose ps

# Aguardar containers iniciarem completamente (30-60 segundos)
sleep 60
```

### 5️⃣ Instalar Dependências
```bash
# Instalar dependências PHP
docker-compose exec laravel.test composer install

# Instalar dependências Node.js
docker-compose exec laravel.test npm install

# Gerar chave da aplicação (se necessário)
docker-compose exec laravel.test php artisan key:generate
```

### 6️⃣ Configurar Banco de Dados
```bash
# Executar migrações
docker-compose exec laravel.test php artisan migrate

# Criar usuário administrador
docker-compose exec laravel.test php artisan db:seed --class=AdminUserSeeder
```

### 7️⃣ Compilar Assets
```bash
# Compilar assets para produção
docker-compose exec laravel.test npm run build

# Verificar se manifest foi criado
docker-compose exec laravel.test ls -la public/build/manifest.json
```

### 8️⃣ Limpar Caches e Otimizar
```bash
# Limpar todos os caches
docker-compose exec laravel.test php artisan config:clear
docker-compose exec laravel.test php artisan cache:clear
docker-compose exec laravel.test php artisan route:clear
docker-compose exec laravel.test php artisan view:clear

# Otimizar autoload
docker-compose exec laravel.test composer dump-autoload
```

---

## ✅ Verificação da Instalação

### 🧪 Testes Básicos
```bash
# Verificar versão Laravel
docker-compose exec laravel.test php artisan --version

# Verificar conexão com banco
docker-compose exec laravel.test php artisan tinker --execute="echo \\DB::connection()->getPdo() ? 'DB: CONECTADO' : 'DB: ERRO';"

# Verificar usuário admin
docker-compose exec laravel.test php artisan tinker --execute="\$user = \\App\\Models\\User::where('email', 'admin@admin.com')->first(); echo 'Admin: ' . (\$user ? 'CRIADO' : 'ERRO');"

# Verificar rotas Filament
docker-compose exec laravel.test php artisan route:list --name=filament | wc -l
```

### 🌐 Acesso ao Sistema
1. **URL Principal**: `http://localhost/`
2. **Painel Admin**: `http://localhost/admin`
3. **Login Automático**: `http://localhost/login-admin`

### 🔐 Credenciais de Acesso
- **Email**: `admin@admin.com`
- **Senha**: `password`

---

## 🔧 Comandos Úteis Pós-Instalação

### 📋 Desenvolvimento
```bash
# Acessar container Laravel
docker-compose exec laravel.test bash

# Recompilar assets (desenvolvimento)
docker-compose exec laravel.test npm run dev

# Ver logs em tempo real
docker-compose exec laravel.test tail -f storage/logs/laravel.log

# Executar testes
docker-compose exec laravel.test php artisan test
```

### 🔄 Manutenção
```bash
# Reiniciar containers
docker-compose restart

# Reconstruir containers (se necessário)
docker-compose down && docker-compose up -d --build

# Backup do banco de dados
docker-compose exec laravel.test mysqldump -u sail -psail cred_crud > backup_$(date +%Y%m%d_%H%M%S).sql
```

---

## 🛠️ Solução de Problemas

### ❌ Container não inicia
```bash
# Verificar logs do container
docker-compose logs laravel.test

# Corrigir permissões
sudo chown -R $USER:$USER .
sudo chmod -R 755 storage bootstrap/cache
```

### ❌ Erro de Assets/Vite
```bash
# Recompilar assets
docker-compose exec laravel.test npm install
docker-compose exec laravel.test npm run build
```

### ❌ Erro de Banco de Dados
```bash
# Verificar status do MySQL
docker-compose exec mysql mysql -u sail -psail -e "SELECT 1;"

# Resetar banco (CUIDADO - REMOVE TODOS OS DADOS)
docker-compose exec laravel.test php artisan migrate:fresh --seed
```

### ❌ Erro 500 no navegador
```bash
# Verificar logs
docker-compose exec laravel.test tail -20 storage/logs/laravel.log

# Limpar tudo
docker-compose exec laravel.test php artisan config:clear
docker-compose exec laravel.test php artisan cache:clear
docker-compose exec laravel.test composer dump-autoload
```

---

## 📚 Funcionalidades Disponíveis

### 🛡️ Sistema de Credenciais
- ✅ **CRUD Completo**: Criar, listar, editar, deletar
- ✅ **Validações Inteligentes**: Datas, unicidade, campos obrigatórios
- ✅ **Filtros Avançados**: Por nível de sigilo e status de validade
- ✅ **Soft Delete**: Exclusão reversível
- ✅ **Indicadores Visuais**: Status de validade por cores
- ✅ **Busca Global**: Em todos os campos

### 🔐 Sistema de Permissões
- ✅ **Spatie Permission**: Controle granular de acesso
- ✅ **Filament Shield**: Interface de gerenciamento
- ✅ **Roles e Permissions**: Sistema flexível
- ✅ **Super Admin**: Acesso total ao sistema

### 🎨 Interface Moderna
- ✅ **Filament 4**: Interface administrativa moderna
- ✅ **Responsive Design**: Funciona em todos os dispositivos
- ✅ **Dark/Light Mode**: Tema adaptável
- ✅ **Formulários Inteligentes**: Validação em tempo real

---

## 📖 Documentação Adicional

### 📁 Arquivos de Referência
- 📋 **`.taskmaster/docs/best-practices-laravel12-filament4.md`**: Melhores práticas
- 🔧 **`.taskmaster/docs/useful-commands.md`**: Comandos úteis e scripts
- 📚 **`.taskmaster/docs/lessons-learned.md`**: Problemas resolvidos e soluções

### 🔗 Links Úteis
- **Laravel 12 Docs**: https://laravel.com/docs/12.x
- **Filament 4 Docs**: https://filamentphp.com/docs/4.x
- **Spatie Permission**: https://spatie.be/docs/laravel-permission

---

## 🆘 Suporte

### 🔍 Diagnóstico Automático
Use o script de diagnóstico incluído:
```bash
bash .taskmaster/docs/diagnose-system.sh
```

### 📝 Logs Importantes
- **Laravel**: `storage/logs/laravel.log`
- **Docker**: `docker-compose logs`
- **MySQL**: `docker-compose logs mysql`

### ✅ Checklist de Instalação
- [ ] ✅ Docker funcionando
- [ ] ✅ Containers ativos (`docker-compose ps`)
- [ ] ✅ Dependências instaladas (`composer.lock` existe)
- [ ] ✅ Assets compilados (`public/build/manifest.json` existe)
- [ ] ✅ Migrações executadas
- [ ] ✅ Usuário admin criado
- [ ] ✅ Painel acessível em `http://localhost/admin`

---

**📅 Versão do Guia**: $(date +"%Y-%m-%d %H:%M:%S")  
**🔧 Sistema**: Laravel 12.39.0 + Filament 4.2.2  
**🐳 Ambiente**: Docker + Sail  
**📊 Status**: Production Ready

---

## 🎯 Próximos Passos

Após instalação bem-sucedida:
1. ✅ **Teste o sistema**: Acesse `http://localhost/admin`
2. 📋 **Explore funcionalidades**: Crie algumas credenciais de teste
3. 📚 **Leia documentação**: Consulte arquivos em `.taskmaster/docs/`
4. 🚀 **Desenvolva**: Use as melhores práticas documentadas

**🌟 Sistema pronto para uso! Bem-vindo ao Laravel 12 + Filament 4!** 🌟