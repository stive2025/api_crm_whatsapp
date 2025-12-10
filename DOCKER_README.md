# 🐳 Despliegue Docker - Laravel Application

Este proyecto incluye una configuración completa de Docker para desplegar la aplicación Laravel con todas sus dependencias.

## 📋 Requisitos Previos

- Docker Engine 20.10+
- Docker Compose 2.0+
- Al menos 2GB de RAM disponible

## 🚀 Inicio Rápido

### 1. Configurar el archivo .env

Copia el archivo de ejemplo y ajusta las variables de entorno:

```bash
cp bin/.env.example bin/.env
```

Ajusta las siguientes variables en `.env`:

```env
APP_NAME=Laravel
APP_ENV=production
APP_KEY=                          # Se generará automáticamente
APP_DEBUG=false
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=laravel
DB_PASSWORD=secret

CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### 2. Construir e iniciar los contenedores

```bash
# Construir las imágenes
docker-compose build

# Iniciar los servicios
docker-compose up -d
```

### 3. Generar la clave de aplicación

```bash
docker-compose exec app php artisan key:generate
```

### 4. Ejecutar migraciones

```bash
docker-compose exec app php artisan migrate --seed
```

### 5. Acceder a la aplicación

La aplicación estará disponible en: http://localhost:8000

## 🛠️ Comandos Útiles

### Gestión de contenedores

```bash
# Ver logs
docker-compose logs -f

# Ver logs de un servicio específico
docker-compose logs -f app

# Detener contenedores
docker-compose down

# Detener y eliminar volúmenes
docker-compose down -v

# Reiniciar servicios
docker-compose restart
```

### Artisan commands

```bash
# Ejecutar comandos artisan
docker-compose exec app php artisan [comando]

# Limpiar cache
docker-compose exec app php artisan cache:clear

# Ejecutar tinker
docker-compose exec app php artisan tinker
```

### Composer

```bash
# Instalar dependencias
docker-compose exec app composer install

# Actualizar dependencias
docker-compose exec app composer update
```

### NPM (si necesitas recompilar assets)

```bash
# Reconstruir assets
docker-compose exec app npm run build
```

### Base de datos

```bash
# Acceder a MySQL
docker-compose exec db mysql -u laravel -p

# Backup de la base de datos
docker-compose exec db mysqldump -u laravel -p laravel > backup.sql

# Restaurar backup
docker-compose exec -T db mysql -u laravel -p laravel < backup.sql
```

### Volúmenes persistentes

Los datos de MySQL se almacenan en el volumen `db_data` que persiste entre reinicios.

### Variables de entorno

Puedes configurar variables adicionales en el archivo `.env` o directamente en `docker-compose.yml`.

## 🐛 Solución de Problemas

### Error de permisos en storage

```bash
docker-compose exec app chmod -R 775 storage bootstrap/cache
docker-compose exec app chown -R appuser:appuser storage bootstrap/cache
```

### Recrear contenedores desde cero

```bash
docker-compose down -v
docker-compose build --no-cache
docker-compose up -d
```

### Ver errores de PHP

```bash
docker-compose logs -f app
# o ver los logs de Laravel
docker-compose exec app tail -f storage/logs/laravel.log
```

## 🤝 Contribuir

Si encuentras algún problema o tienes sugerencias, por favor abre un issue.

## 📄 Licencia

Este proyecto está bajo la licencia MIT.
