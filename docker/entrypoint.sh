#!/bin/sh

echo "🚀 Iniciando aplicación Laravel..."

# Generar APP_KEY si no existe
if ! grep -q "APP_KEY=base64:" /var/www/html/.env 2>/dev/null; then
    echo "🔑 Generando APP_KEY..."
    php artisan key:generate --force || true
fi

# Crear storage link si no existe
if [ ! -L /var/www/html/public/storage ]; then
    echo "🔗 Creando storage link..."
    php artisan storage:link || true
fi

# Limpiar cache (sin fallar si hay error de conexión)
echo "🧹 Limpiando cache..."
php artisan config:clear || true
php artisan view:clear || true

# Corregir permisos
echo "🔒 Configurando permisos..."
chown -R appuser:appuser /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true

echo "✅ Aplicación lista!"

# Iniciar supervisord
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
