#!/bin/bash
# fix-permissions.sh
# Script para corrigir permissões do Laravel Sail
# Uso: ./fix-permissions.sh ou docker-compose exec laravel.test ./fix-permissions.sh

echo "🔧 Corrigindo permissões do Laravel..."

# Diretórios que precisam de permissão de escrita
WRITABLE_DIRS=(
    "storage"
    "storage/app"
    "storage/app/public"
    "storage/framework"
    "storage/framework/cache"
    "storage/framework/cache/data"
    "storage/framework/sessions"
    "storage/framework/views"
    "storage/logs"
    "bootstrap/cache"
)

# Criar diretórios se não existirem
for dir in "${WRITABLE_DIRS[@]}"; do
    if [ ! -d "$dir" ]; then
        mkdir -p "$dir"
        echo "📁 Criado: $dir"
    fi
done

# Definir proprietário (sail:sail para ambiente Docker)
if command -v docker &> /dev/null; then
    # Estamos fora do container
    echo "🐳 Executando dentro do container Docker..."
    docker-compose exec -T laravel.test bash -c "
        chown -R sail:sail storage bootstrap/cache
        find storage -type d -exec chmod 775 {} \;
        find storage -type f -exec chmod 664 {} \;
        find bootstrap/cache -type d -exec chmod 775 {} \;
        find bootstrap/cache -type f -exec chmod 664 {} \;
    "
else
    # Estamos dentro do container
    chown -R sail:sail storage bootstrap/cache 2>/dev/null || chown -R www-data:www-data storage bootstrap/cache
    find storage -type d -exec chmod 775 {} \;
    find storage -type f -exec chmod 664 {} \;
    find bootstrap/cache -type d -exec chmod 775 {} \;
    find bootstrap/cache -type f -exec chmod 664 {} \;
fi

echo "✅ Permissões corrigidas!"
echo ""
echo "📋 Resumo das permissões:"
echo "   - Diretórios: 775 (rwxrwxr-x)"
echo "   - Arquivos: 664 (rw-rw-r--)"
echo "   - Proprietário: sail:sail"
