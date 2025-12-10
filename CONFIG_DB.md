# Configuración para conectar a MySQL externo

Edita tu archivo `bin/.env` y asegúrate de tener estas variables configuradas:

```env
APP_NAME="CRM WhatsApp API"
APP_ENV=production
APP_KEY=                          # Genera esto después con: docker-compose exec app php artisan key:generate
APP_DEBUG=false
APP_URL=http://localhost:8000

# Conexión a MySQL en el host (fuera del contenedor)
DB_CONNECTION=mysql
DB_HOST=host.docker.internal      # Esto apunta al host de Windows
DB_PORT=3306
DB_DATABASE=crm_sefil
DB_USERNAME=root
DB_PASSWORD=                      # Pon la contraseña de tu MySQL aquí

# Redis
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379
```

## Pasos para configurar:

1. Copia el archivo de ejemplo si no existe:
   ```bash
   cp bin/.env.example bin/.env
   ```

2. Edita `bin/.env` con las variables de arriba

3. Levanta los contenedores:
   ```bash
   docker-compose up -d
   ```

4. Genera la APP_KEY:
   ```bash
   docker-compose exec app php artisan key:generate
   ```

5. Ejecuta las migraciones:
   ```bash
   docker-compose exec app php artisan migrate
   ```

## Notas importantes:

- Se eliminó el contenedor de MySQL del docker-compose.yml
- Se usa `host.docker.internal` para conectar desde el contenedor al MySQL de tu máquina
- El puerto 3306 ya no está en conflicto porque no se crea un contenedor MySQL nuevo
- Solo se usan los contenedores: app, redis y websocket
