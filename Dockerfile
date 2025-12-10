# Multi-stage build para Laravel 11

# Etapa 1: Build de assets con Node.js
FROM node:20-alpine AS node-builder

WORKDIR /app

COPY bin/package*.json ./
RUN npm ci

COPY bin/ .
RUN npm run build

# Etapa 2: Imagen principal de PHP
FROM php:8.2-fpm-alpine

# Instalar dependencias del sistema
RUN apk add --no-cache \
    nginx \
    supervisor \
    git \
    curl \
    libpng-dev \
    libjpeg-turbo-dev \
    libwebp-dev \
    freetype-dev \
    oniguruma-dev \
    libxml2-dev \
    zip \
    unzip \
    postgresql-dev \
    mysql-client \
    ffmpeg \
    bash

# Instalar extensiones de PHP
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) \
    pdo \
    pdo_mysql \
    pdo_pgsql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    opcache

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Crear usuario para la aplicación
RUN addgroup -g 1000 appuser && \
    adduser -D -u 1000 -G appuser appuser

WORKDIR /var/www/html

# Copiar archivos de la aplicación desde la carpeta bin
COPY --chown=appuser:appuser bin/ .

# Copiar assets compilados desde la etapa de Node
COPY --from=node-builder --chown=appuser:appuser /app/public/build ./public/build

# Instalar dependencias de PHP
RUN composer install --optimize-autoloader --no-dev --no-interaction

# Configurar permisos
RUN chown -R appuser:appuser /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Configurar PHP
COPY docker/php.ini /usr/local/etc/php/conf.d/custom.ini

# Configurar Nginx
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/default.conf /etc/nginx/http.d/default.conf

# Configurar Supervisor
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Crear directorios necesarios
RUN mkdir -p /var/log/supervisor \
    && mkdir -p /run/nginx \
    && chown -R appuser:appuser /var/log/supervisor

# Optimizar Laravel
RUN php artisan config:cache || true \
    && php artisan route:cache || true \
    && php artisan view:cache || true

EXPOSE 80

# Usar supervisor para ejecutar nginx, php-fpm y websocket
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
