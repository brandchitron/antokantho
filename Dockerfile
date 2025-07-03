# Use official PHP image with Apache
FROM php:8.2-apache

# Install dependencies
RUN apt-get update && \
    apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd

# Enable Apache modules
RUN a2enmod rewrite headers

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . .

# Create data directories
RUN mkdir -p /var/www/html/data/posts && \
    mkdir -p /var/www/html/data/users && \
    mkdir -p /var/www/html/assets/images

# Set permissions
RUN chown -R www-data:www-data /var/www/html/data && \
    chmod -R 775 /var/www/html/data

# Set environment variables
ENV APACHE_DOCUMENT_ROOT /var/www/html
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Expose port
EXPOSE 8080

# Start Apache
CMD ["apache2-foreground"]
