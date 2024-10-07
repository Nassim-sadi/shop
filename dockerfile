# Use the official PHP image as the base image
FROM php:8.3-fpm

# Set the working directory
WORKDIR /var/www/html

# Install system dependencies
RUN apt-get update && apt-get install -y \
  build-essential \
  libpng-dev \
  libjpeg62-turbo-dev \
  libfreetype6-dev \
  locales \
  zip \
  jpegoptim optipng pngquant gifsicle \
  vim \
  unzip \
  git \
  curl \
  libonig-dev \
  libxml2-dev

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy the application code
COPY . /var/www/html

# Set file permissions
RUN chown -R www-data:www-data /var/www/html \
  && chmod -R 755 /var/www/html

# Expose the port Laravel will run on
EXPOSE 80

# Start PHP-FPM
CMD ["php-fpm"]
