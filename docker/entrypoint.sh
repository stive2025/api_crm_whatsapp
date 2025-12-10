#!/bin/sh

echo "🚀 Iniciando aplicación Laravel..."

# Esperar a que la base de datos esté lista
echo "⏳ Esperando la base de datos..."
while ! nc -z db 3306; do
    sleep 1
done
echo "✅ Base de datos lista"

# Ejecutar migraciones
echo "📦 Ejecutando migraciones..."
php artisan migrate --force

# Crear storage link si no existe
if [ ! -L /var/www/html/public/storage ]; then
    echo "🔗 Creando storage link..."
    php artisan storage:link
fi

# Limpiar cache
echo "🧹 Limpiando cache..."
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "✅ Aplicación lista!"

# Iniciar supervisord
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
