# Multi-stage build para Laravel 11

# Etapa 1: Build de assets con Node.js
FROM node:20-alpine AS node-builder

WORKDIR /app

COPY bin/package*.json ./
RUN npm install

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

# Copiar archivos de configuración Docker desde el contexto raíz
COPY --chown=root:root ./docker/php.ini /usr/local/etc/php/conf.d/custom.ini
COPY --chown=root:root ./docker/nginx.conf /etc/nginx/nginx.conf
COPY --chown=root:root ./docker/default.conf /etc/nginx/http.d/default.conf
COPY --chown=root:root ./docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Crear directorios necesarios
RUN mkdir -p /var/log/supervisor \
    && mkdir -p /run/nginx \
    && chown -R appuser:appuser /var/log/supervisor

# Copiar script de entrypoint
COPY --chown=root:root ./docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80

# Usar entrypoint para inicialización
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
