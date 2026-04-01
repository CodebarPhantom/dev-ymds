FROM php:8.3-fpm

# Set working directory
WORKDIR /var/www

# Copy composer files first for dependency caching
COPY composer.lock composer.json /var/www/

# Install system dependencies including Nginx, Supervisor, and libraries for MySQL and PostgreSQL
RUN apt-get update && apt-get install -y \
    nginx \
    supervisor \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libpq-dev \
    zip unzip \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install pdo pdo_mysql pdo_pgsql gd \
 && apt-get clean

# Remove default Nginx configuration files
RUN rm -f /etc/nginx/conf.d/default.conf && rm -f /etc/nginx/sites-enabled/default

# Copy custom Nginx configuration
COPY nginx.conf /etc/nginx/conf.d/default.conf

# Remove any default Nginx HTML files
RUN rm -rf /usr/share/nginx/html/*

# Copy Supervisor configuration
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Copy your Laravel application into the container
COPY . /var/www
# Copy the production env file as .env
COPY .env.production .env

# Change ownership of storage and bootstrap/cache to ensure they’re writable
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
#for development
#RUN chmod -R 777 /var/www/storage /var/www/bootstrap/cache

# Expose port 80 for HTTP traffic
EXPOSE 80

# Start Supervisor (which in turn starts PHP-FPM and Nginx)
CMD ["/usr/bin/supervisord", "-n"]
