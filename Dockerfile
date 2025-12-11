FROM ubuntu:22.04

ENV DEBIAN_FRONTEND=noninteractive

RUN apt-get update && apt-get install -y \
    software-properties-common \
    ca-certificates \
    lsb-release \
    apt-transport-https \
    && add-apt-repository ppa:ondrej/php \
    && apt-get update && apt-get install -y \
    php8.2-fpm \
    php8.2-cli \
    php8.2-common \
    php8.2-mysql \
    php8.2-pgsql \
    php8.2-xml \
    php8.2-curl \
    php8.2-gd \
    php8.2-mbstring \
    php8.2-zip \
    php8.2-bcmath \
    php8.2-intl \
    php8.2-opcache \
    nginx \
    supervisor \
    git \
    curl \
    wget \
    unzip \
    ffmpeg \
    mysql-client \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN groupadd -g 1000 appuser && \
    useradd -u 1000 -g appuser -m -s /bin/bash appuser

WORKDIR /var/www/html


COPY --chown=appuser:appuser bin/ .

# Instalar dependencias de PHP
RUN composer install --optimize-autoloader --no-dev --no-interaction

# Copiar archivos de configuración Docker desde el contexto raíz
COPY --chown=root:root ./docker/php.ini /etc/php/8.2/fpm/conf.d/99-custom.ini
COPY --chown=root:root ./docker/nginx.conf /etc/nginx/nginx.conf
COPY --chown=root:root ./docker/default.conf /etc/nginx/sites-available/default
COPY --chown=root:root ./docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Configurar PHP-FPM para usar TCP en lugar de socket
RUN sed -i 's/listen = \/run\/php\/php8.2-fpm.sock/listen = 127.0.0.1:9000/' /etc/php/8.2/fpm/pool.d/www.conf \
    && sed -i 's/^user = .*/user = www-data/' /etc/php/8.2/fpm/pool.d/www.conf \
    && sed -i 's/^group = .*/group = www-data/' /etc/php/8.2/fpm/pool.d/www.conf

# Configurar permisos correctos para www-data
RUN chown -R www-data:www-data /var/www/html/storage \
    && chown -R www-data:www-data /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache

# Crear directorios necesarios
RUN mkdir -p /var/log/supervisor \
    && mkdir -p /run/php \
    && chown -R www-data:www-data /var/log/supervisor

# Copiar script de entrypoint
COPY --chown=root:root ./docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80

# Usar entrypoint para inicialización
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
