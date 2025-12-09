# Comandos Úteis - Laravel 12 + Filament 4

## 📋 Índice
- [Setup Inicial](#setup-inicial)
- [Desenvolvimento Diário](#desenvolvimento-diário)
- [Troubleshooting](#troubleshooting)
- [Backup e Restore](#backup-e-restore)
- [Performance](#performance)
- [Scripts Automatizados](#scripts-automatizados)

---

## 🚀 Setup Inicial

### Configuração do Ambiente Docker
```bash
# Configurar variáveis de ambiente Docker
export WWWGROUP=1000
export WWWUSER=1000

# Subir containers
docker-compose up -d

# Verificar status dos containers
docker-compose ps

# Acessar container Laravel
docker-compose exec laravel.test bash
```

### Instalação Inicial das Dependências
```bash
# Dentro do container
composer install
npm install
npm run build

# Executar migrations
php artisan migrate

# Criar usuário admin
php artisan db:seed --class=AdminUserSeeder
```

---

## 💻 Desenvolvimento Diário

### Comandos Frequentes
```bash
# Limpar caches (usar após alterações)
php artisan config:clear && php artisan cache:clear && php artisan route:clear && php artisan view:clear

# Recompilar assets
npm run build

# Verificar rotas Filament
php artisan route:list --name=filament

# Otimizar autoload
composer dump-autoload

# Verificar logs em tempo real
tail -f storage/logs/laravel.log
```

### Testes Rápidos
```bash
# Verificar se sistema está funcionando
php artisan tinker --execute="echo 'Laravel: ' . app()->version(); echo PHP_EOL; echo 'Filament: ' . (class_exists('\\Filament\\Filament') ? 'OK' : 'ERRO');"

# Testar usuário admin
php artisan tinker --execute="\$user = \\App\\Models\\User::where('email', 'admin@admin.com')->first(); echo \$user ? 'Admin OK' : 'Admin NÃO ENCONTRADO';"

# Verificar permissões
php artisan tinker --execute="\$user = \\App\\Models\\User::where('email', 'admin@admin.com')->first(); echo 'Roles: '; print_r(\$user->roles->pluck('name')->toArray());"
```

---

## 🔧 Troubleshooting

### Problemas de Permissão Docker
```bash
# Opção 1: Usar o script de correção de permissões
./fix-permissions.sh

# Opção 2: Corrigir manualmente dentro do container
docker-compose exec laravel.test bash -c "
    chown -R sail:sail storage bootstrap/cache
    find storage -type d -exec chmod 775 {} \;
    find storage -type f -exec chmod 664 {} \;
    find bootstrap/cache -type d -exec chmod 775 {} \;
    find bootstrap/cache -type f -exec chmod 664 {} \;
"

# Opção 3: Corrigir permissões no host (fora do container)
sudo chown -R $USER:$USER .
sudo chmod -R 755 storage bootstrap/cache

# Recriar containers se necessário
docker-compose down
docker-compose up -d --build
```

### Problemas de Autoload/Classes
```bash
# Limpar e recompilar autoload
composer dump-autoload --optimize
php artisan clear-compiled

# Verificar classe específica
php artisan tinker --execute="echo class_exists('\\App\\Filament\\Resources\\Credentials\\CredentialResource') ? 'OK' : 'ERRO';"

# Verificar namespaces Filament
php artisan tinker --execute="echo class_exists('\\Filament\\Actions\\Action') ? 'Action OK' : 'Action ERRO';"
```

### Problemas de Assets/Vite
```bash
# Recompilar assets do zero
rm -rf node_modules package-lock.json
npm install
npm run build

# Verificar manifest
ls -la public/build/manifest.json

# Limpar cache de views
php artisan view:clear
```

### Problemas de Banco de Dados
```bash
# Verificar conexão
php artisan tinker --execute="echo \\DB::connection()->getPdo() ? 'DB OK' : 'DB ERRO';"

# Verificar migrations
php artisan migrate:status

# Resetar banco (CUIDADO!)
php artisan migrate:fresh --seed
```

---

## 💾 Backup e Restore

### Backup do Banco de Dados
```bash
# Backup completo
docker-compose exec laravel.test mysqldump -u sail -psail cred_crud > backup_$(date +%Y%m%d_%H%M%S).sql

# Backup apenas estrutura
docker-compose exec laravel.test mysqldump -u sail -psail --no-data cred_crud > structure_$(date +%Y%m%d_%H%M%S).sql

# Backup apenas dados
docker-compose exec laravel.test mysqldump -u sail -psail --no-create-info cred_crud > data_$(date +%Y%m%d_%H%M%S).sql
```

### Restore do Banco de Dados
```bash
# Restaurar backup completo
docker-compose exec -T laravel.test mysql -u sail -psail cred_crud < backup_20231119_143000.sql

# Verificar restore
docker-compose exec laravel.test mysql -u sail -psail -e "USE cred_crud; SHOW TABLES;"
```

### Backup de Arquivos Importantes
```bash
# Backup de configurações
cp composer.json composer.json.backup
cp .env .env.backup
cp docker-compose.yml docker-compose.yml.backup

# Backup de migrações customizadas
tar -czf migrations_backup_$(date +%Y%m%d).tar.gz database/migrations/
```

---

## ⚡ Performance

### Otimização para Produção
```bash
# Otimizar composer
composer install --optimize-autoloader --no-dev

# Cache de configuração
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Otimizar eventos
php artisan event:cache

# Compilar assets otimizados
npm run build
```

### Limpeza de Cache Desenvolvimento
```bash
# Script completo de limpeza
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan event:clear
composer dump-autoload
```

### Monitoramento de Performance
```bash
# Verificar uso de memória
php artisan tinker --execute="echo 'Memory: ' . memory_get_usage(true) / 1024 / 1024 . ' MB';"

# Verificar tempo de resposta das rotas
time curl -s http://localhost/admin/login > /dev/null

# Verificar log de queries (em desenvolvimento)
tail -f storage/logs/laravel.log | grep "query"
```

---

## 🤖 Scripts Automatizados

### Script de Setup Completo
```bash
#!/bin/bash
# setup-project.sh

echo "🚀 Configurando projeto Laravel 12 + Filament 4..."

# Variáveis de ambiente Docker
export WWWGROUP=1000
export WWWUSER=1000

# Subir containers
echo "📦 Subindo containers Docker..."
docker-compose up -d

# Aguardar containers iniciarem
echo "⏳ Aguardando containers iniciarem..."
sleep 30

# Instalar dependências
echo "📥 Instalando dependências..."
docker-compose exec laravel.test composer install
docker-compose exec laravel.test npm install

# Compilar assets
echo "🎨 Compilando assets..."
docker-compose exec laravel.test npm run build

# Executar migrations
echo "🗄️ Executando migrations..."
docker-compose exec laravel.test php artisan migrate

# Criar usuário admin
echo "👤 Criando usuário admin..."
docker-compose exec laravel.test php artisan db:seed --class=AdminUserSeeder

# Limpar caches
echo "🧹 Limpando caches..."
docker-compose exec laravel.test php artisan config:clear
docker-compose exec laravel.test php artisan cache:clear

echo "✅ Setup concluído! Acesse: http://localhost/admin"
```

### Script de Reset do Sistema
```bash
#!/bin/bash
# reset-system.sh

echo "🔄 Resetando sistema..."

# Backup automático
echo "💾 Criando backup..."
docker-compose exec laravel.test mysqldump -u sail -psail cred_crud > "backup_reset_$(date +%Y%m%d_%H%M%S).sql"

# Resetar banco
echo "🗄️ Resetando banco de dados..."
docker-compose exec laravel.test php artisan migrate:fresh

# Recriar usuário admin
echo "👤 Recriando usuário admin..."
docker-compose exec laravel.test php artisan db:seed --class=AdminUserSeeder

# Limpar tudo
echo "🧹 Limpando sistema..."
docker-compose exec laravel.test php artisan config:clear
docker-compose exec laravel.test php artisan cache:clear
docker-compose exec laravel.test php artisan route:clear
docker-compose exec laravel.test php artisan view:clear
docker-compose exec laravel.test composer dump-autoload

echo "✅ Reset concluído!"
```

### Script de Diagnóstico
```bash
#!/bin/bash
# diagnose-system.sh

echo "🔍 Diagnóstico do Sistema Laravel 12 + Filament 4"
echo "=================================================="

# Verificar containers
echo "📦 Status dos Containers:"
docker-compose ps

echo ""
echo "🐘 Versão PHP:"
docker-compose exec laravel.test php --version

echo ""
echo "🚀 Versão Laravel:"
docker-compose exec laravel.test php artisan --version

echo ""
echo "🎨 Verificação Filament:"
docker-compose exec laravel.test php artisan tinker --execute="echo 'Filament: ' . (class_exists('\\Filament\\Filament') ? 'OK' : 'ERRO'); echo PHP_EOL;"

echo ""
echo "🗄️ Status do Banco:"
docker-compose exec laravel.test php artisan tinker --execute="try { echo 'DB: ' . (\\DB::connection()->getPdo() ? 'CONECTADO' : 'ERRO'); } catch(Exception \$e) { echo 'DB: ERRO - ' . \$e->getMessage(); } echo PHP_EOL;"

echo ""
echo "👤 Usuário Admin:"
docker-compose exec laravel.test php artisan tinker --execute="\$user = \\App\\Models\\User::where('email', 'admin@admin.com')->first(); echo 'Admin: ' . (\$user ? 'EXISTE' : 'NÃO ENCONTRADO'); echo PHP_EOL;"

echo ""
echo "🛡️ Rotas Filament:"
docker-compose exec laravel.test php artisan route:list --name=filament | wc -l

echo ""
echo "📱 Assets:"
docker-compose exec laravel.test ls -la public/build/manifest.json 2>/dev/null && echo "Assets: OK" || echo "Assets: ERRO - Recompilar com 'npm run build'"

echo ""
echo "✅ Diagnóstico concluído!"
```

### Script de Atualização Segura
```bash
#!/bin/bash
# safe-update.sh

echo "🔄 Atualização Segura do Sistema"
echo "================================"

# Backup automático
echo "💾 Criando backup completo..."
BACKUP_FILE="backup_before_update_$(date +%Y%m%d_%H%M%S).sql"
docker-compose exec laravel.test mysqldump -u sail -psail cred_crud > "$BACKUP_FILE"
echo "Backup criado: $BACKUP_FILE"

# Backup de arquivos
cp composer.json composer.json.backup
cp .env .env.backup

# Atualizar dependências
echo "📦 Atualizando dependências..."
docker-compose exec laravel.test composer update

# Recompilar assets
echo "🎨 Recompilando assets..."
docker-compose exec laravel.test npm install
docker-compose exec laravel.test npm run build

# Executar migrations
echo "🗄️ Executando migrations..."
docker-compose exec laravel.test php artisan migrate

# Limpar caches
echo "🧹 Limpando caches..."
docker-compose exec laravel.test php artisan config:clear
docker-compose exec laravel.test php artisan cache:clear
docker-compose exec laravel.test composer dump-autoload

# Teste final
echo "🧪 Testando sistema..."
docker-compose exec laravel.test php artisan route:list --name=filament > /dev/null && echo "✅ Rotas OK" || echo "❌ Problema nas rotas"

echo "✅ Atualização concluída!"
echo "📋 Backup disponível em: $BACKUP_FILE"
```

---

## 📝 Comandos de Desenvolvimento Filament

### Criação de Resources
```bash
# Criar resource básico
php artisan make:filament-resource [Entity]

# Criar resource com geração automática
php artisan make:filament-resource [Entity] --generate

# Criar resource com soft deletes
php artisan make:filament-resource [Entity] --soft-deletes
```

### Criação de Componentes
```bash
# Criar página customizada
php artisan make:filament-page [PageName]

# Criar widget
php artisan make:filament-widget [WidgetName]

# Criar relation manager
php artisan make:filament-relation-manager [Resource] [relationship]
```

### Comandos de Permissão (Shield)
```bash
# Instalar Shield
php artisan shield:install

# Gerar permissões
php artisan shield:generate --all

# Criar super admin
php artisan shield:super-admin
```

---

**📝 Documento criado em:** $(date +"%Y-%m-%d %H:%M:%S")  
**🔧 Versão do sistema:** Laravel 12.39.0 + Filament 4.2.2  
**📊 Uso:** Comandos testados em ambiente Docker  
**🎯 Manutenção:** Atualizar conforme novas necessidades