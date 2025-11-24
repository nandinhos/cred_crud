#!/bin/bash

# 🚀 Setup Automatizado - Laravel 12 + Filament 4
# Script de instalação completa após clonagem

echo "🚀 Iniciando setup do Sistema Laravel 12 + Filament 4..."
echo "=================================================="

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Função para log colorido
log() {
    echo -e "${GREEN}[$(date '+%H:%M:%S')]${NC} $1"
}

error() {
    echo -e "${RED}[ERRO]${NC} $1"
}

warning() {
    echo -e "${YELLOW}[AVISO]${NC} $1"
}

info() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

# Verificar se Docker está rodando
log "🐳 Verificando Docker..."
if ! docker --version &> /dev/null; then
    error "Docker não encontrado. Instale o Docker primeiro."
    exit 1
fi

if ! docker-compose --version &> /dev/null; then
    error "Docker Compose não encontrado. Instale o Docker Compose primeiro."
    exit 1
fi

log "✅ Docker e Docker Compose encontrados"

# Configurar variáveis de ambiente Docker
log "⚙️ Configurando variáveis de ambiente Docker..."
export WWWGROUP=1000
export WWWUSER=1000

# Criar arquivo .env se não existir
if [ ! -f .env ]; then
    log "📄 Criando arquivo .env..."
    cp .env.example .env
    log "✅ Arquivo .env criado"
else
    info "📄 Arquivo .env já existe"
fi

# Garantir que DB_HOST esteja correto para Docker
if grep -q "DB_HOST=127.0.0.1" .env; then
    log "🔧 Ajustando DB_HOST para 'mysql' no arquivo .env..."
    sed -i 's/DB_HOST=127.0.0.1/DB_HOST=mysql/g' .env
fi

# Garantir que WWWGROUP e WWWUSER existam no .env
if ! grep -q "WWWGROUP=" .env; then
    log "🔧 Adicionando WWWGROUP ao .env..."
    echo "WWWGROUP=1000" >> .env
fi

if ! grep -q "WWWUSER=" .env; then
    log "🔧 Adicionando WWWUSER ao .env..."
    echo "WWWUSER=1000" >> .env
fi

# Verificar se a pasta vendor existe
if [ ! -d "vendor" ]; then
    log "📦 Pasta vendor não encontrada. Instalando dependências com container temporário..."
    docker run --rm \
        -u "$(id -u):$(id -g)" \
        -v "$(pwd):/var/www/html" \
        -w /var/www/html \
        laravelsail/php83-composer:latest \
        composer install --ignore-platform-reqs
    
    if [ $? -eq 0 ]; then
        log "✅ Dependências instaladas com sucesso via container temporário"
    else
        error "Falha ao instalar dependências iniciais"
        exit 1
    fi
else
    info "📦 Pasta vendor já existe"
fi

# Subir containers
log "📦 Subindo containers Docker..."
if docker-compose up -d; then
    log "✅ Containers iniciados com sucesso"
else
    error "Falha ao iniciar containers"
    exit 1
fi

# Aguardar containers iniciarem
log "⏳ Aguardando containers iniciarem..."
sleep 30

# Verificar se containers estão rodando
log "🔍 Verificando status dos containers..."
docker-compose ps

# Verificar se container Laravel está acessível
log "🧪 Testando acesso ao container Laravel..."
if docker-compose exec -T laravel.test php --version > /dev/null 2>&1; then
    log "✅ Container Laravel acessível"
else
    error "Container Laravel não acessível. Aguardando mais tempo..."
    sleep 30
    if ! docker-compose exec -T laravel.test php --version > /dev/null 2>&1; then
        error "Falha ao acessar container Laravel"
        exit 1
    fi
fi

# Instalar dependências PHP
log "📥 Instalando dependências PHP..."
if docker-compose exec -T laravel.test composer install --no-interaction; then
    log "✅ Dependências PHP instaladas"
else
    error "Falha ao instalar dependências PHP"
    exit 1
fi

# Gerar chave da aplicação (se necessário)
log "🔑 Verificando chave da aplicação..."
if docker-compose exec -T laravel.test php artisan key:generate --force; then
    log "✅ Chave da aplicação configurada"
else
    warning "Problema ao gerar chave da aplicação"
fi

# Instalar dependências Node.js
log "📥 Instalando dependências Node.js..."
if docker-compose exec -T laravel.test npm install; then
    log "✅ Dependências Node.js instaladas"
else
    error "Falha ao instalar dependências Node.js"
    exit 1
fi

# Compilar assets
log "🎨 Compilando assets..."
if docker-compose exec -T laravel.test npm run build; then
    log "✅ Assets compilados com sucesso"
else
    error "Falha ao compilar assets"
    exit 1
fi

# Aguardar MySQL estar pronto
log "🗄️ Aguardando MySQL estar pronto..."
log "🗄️ Aguardando MySQL estar pronto..."
max_tries=60
count=0
connected=false

while [ $count -lt $max_tries ]; do
    if docker-compose exec -T laravel.test php artisan tinker --execute="try { \DB::connection()->getPdo(); echo 'DB_OK'; } catch (\Exception \$e) { }" 2>/dev/null | grep -q "DB_OK"; then
        connected=true
        break
    fi
    
    echo -n "."
    sleep 2
    count=$((count+1))
done

echo ""

if [ "$connected" = true ]; then
    log "✅ MySQL está pronto!"
else
    error "MySQL não ficou pronto a tempo. Último erro:"
    docker-compose exec -T laravel.test php artisan tinker --execute="try { \DB::connection()->getPdo(); } catch (\Exception \$e) { echo \$e->getMessage(); }"
    exit 1
fi

# Executar migrações
log "🗄️ Executando migrações do banco de dados..."
if docker-compose exec -T laravel.test php artisan migrate --force; then
    log "✅ Migrações executadas com sucesso"
else
    error "Falha ao executar migrações"
    exit 1
fi

# Executar seeders essenciais
log "👤 Criando roles e permissões..."
if docker-compose exec -T laravel.test php artisan db:seed --class=RolesAndPermissionsSeeder --force; then
    log "✅ Roles e permissões criadas"
else
    warning "Problema ao criar roles e permissões (podem já existir)"
fi

log "👤 Criando usuário administrador..."
if docker-compose exec -T laravel.test php artisan db:seed --class=AdminUserSeeder --force; then
    log "✅ Usuário administrador criado"
else
    warning "Problema ao criar usuário admin (pode já existir)"
fi

log "🏢 Criando offices e ranks..."
docker-compose exec -T laravel.test php artisan db:seed --class=OfficeSeeder --force 2>/dev/null
docker-compose exec -T laravel.test php artisan db:seed --class=RankSeeder --force 2>/dev/null
log "✅ Dados auxiliares criados"

# Limpar caches
log "🧹 Limpando caches..."
docker-compose exec -T laravel.test php artisan config:clear
docker-compose exec -T laravel.test php artisan cache:clear
docker-compose exec -T laravel.test php artisan route:clear
docker-compose exec -T laravel.test php artisan view:clear
docker-compose exec -T laravel.test php artisan filament:clear-cached-components 2>/dev/null

# Otimizar autoload
log "⚡ Otimizando autoload..."
docker-compose exec -T laravel.test composer dump-autoload

# Testes finais
log "🧪 Executando testes finais..."

# Testar conexão com banco
if docker-compose exec -T laravel.test php artisan tinker --execute="echo \\DB::connection()->getPdo() ? 'DB_OK' : 'DB_ERRO';" 2>/dev/null | grep -q "DB_OK"; then
    log "✅ Conexão com banco de dados funcionando"
else
    error "❌ Problema na conexão com banco de dados"
fi

# Verificar usuário admin
if docker-compose exec -T laravel.test php artisan tinker --execute="\$user = \\App\\Models\\User::where('email', 'admin@admin.com')->first(); echo \$user ? 'USER_OK' : 'USER_ERRO';" 2>/dev/null | grep -q "USER_OK"; then
    log "✅ Usuário admin criado com sucesso"
else
    error "❌ Problema na criação do usuário admin"
fi

# Verificar rotas Filament
ROTAS=$(docker-compose exec -T laravel.test php artisan route:list --name=filament 2>/dev/null | wc -l)
if [ "$ROTAS" -gt 5 ]; then
    log "✅ Rotas Filament carregadas ($ROTAS rotas)"
else
    error "❌ Problema nas rotas Filament"
fi

# Verificar assets
if docker-compose exec -T laravel.test ls public/build/manifest.json > /dev/null 2>&1; then
    log "✅ Assets compilados e manifest criado"
else
    error "❌ Problema com assets compilados"
fi

echo ""
echo "=================================================="
echo -e "${GREEN}🎉 SETUP CONCLUÍDO COM SUCESSO! 🎉${NC}"
echo "=================================================="
echo ""
echo -e "${BLUE}📋 INFORMAÇÕES DE ACESSO:${NC}"
echo "🌐 URL Principal: http://localhost/"
echo "🛡️ Painel Admin: http://localhost/admin"
echo "🚀 Login Rápido: http://localhost/login-admin"
echo ""
echo -e "${BLUE}🔐 CREDENCIAIS:${NC}"
echo "📧 Email: admin@admin.com"
echo "🔑 Senha: password"
echo ""
echo -e "${BLUE}🛠️ COMANDOS ÚTEIS:${NC}"
echo "📦 Acessar container: docker-compose exec laravel.test bash"
echo "📊 Ver logs: docker-compose logs laravel.test"
echo "🔄 Reiniciar: docker-compose restart"
echo ""
echo -e "${BLUE}📚 DOCUMENTAÇÃO:${NC}"
echo "📋 Install Guide: INSTALL.md"
echo "🔧 Best Practices: .taskmaster/docs/best-practices-laravel12-filament4.md"
echo "📖 Commands: .taskmaster/docs/useful-commands.md"
echo "🔍 Lessons: .taskmaster/docs/lessons-learned.md"
echo "🧪 Testing: .taskmaster/docs/testing-strategies.md"
echo ""
echo -e "${BLUE}✨ FUNCIONALIDADES DISPONÍVEIS:${NC}"
echo "👥 Gestão de Usuários (admin/super_admin)"
echo "🛡️ Gestão de Credenciais (CRED/TCMS)"
echo "💾 Sistema de Backups (5 mais recentes)"
echo "📊 Métricas do Sistema (comando metrics:collect)"
echo "📝 Auditoria de Ações (logs automáticos)"
echo ""
echo -e "${GREEN}✨ Acesse http://localhost/admin para começar! ✨${NC}"
echo ""