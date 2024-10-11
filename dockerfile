# # Use the official PHP image as a base image
# FROM php:8.3-fpm

# # Set working directory
# WORKDIR /var/www/html

# # Install system dependencies
# RUN apt-get update && apt-get install -y \
#   build-essential \
#   libpng-dev \
#   libjpeg-dev \
#   libfreetype6-dev \
#   locales \
#   zip \
#   jpegoptim optipng pngquant gifsicle \
#   vim \
#   unzip \
#   git \
#   curl \
#   libonig-dev \
#   libxml2-dev \
#   libzip-dev

# # Clear cache
# RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# # Install PHP extensions
# RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# # Install Composer
# COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# # Copy existing application directory contents
# COPY . /var/www/html

# # Copy existing application directory permissions
# COPY --chown=www-data:www-data . /var/www/html

# # Set correct permissions
# RUN chown -R www-data:www-data /var/www/html \
#   && chmod -R 755 /var/www/html

# # Expose port 80
# EXPOSE 80

# # Expose port for Vite (Frontend)
# # EXPOSE 5173


# # Start PHP-FPM and Nginx server
# CMD ["php-fpm"]
FROM richarvey/nginx-php-fpm:latest 

COPY . .

# Image config
ENV SKIP_COMPOSER 1
ENV WEBROOT /var/www/html/public
ENV PHP_ERRORS_STDERR 1
ENV RUN_SCRIPTS 1
ENV REAL_IP_HEADER 1

# Laravel config
ENV APP_ENV staging
ENV APP_DEBUG true
ENV LOG_CHANNEL stderr

# Allow composer to run as root
ENV COMPOSER_ALLOW_SUPERUSER 1
EXPOSE 80

# Start PHP-FPM and Nginx server
CMD ["/start.sh"]