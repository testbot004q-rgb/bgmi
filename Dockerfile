# Apache ke sath PHP image use karein
FROM php:8.2-apache

# MySQLi extension install karein database connection ke liye
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Project files ko container ke web root mein copy karein
COPY . /var/www/html/

# Port 80 expose karein
EXPOSE 80
