FROM php:8.3.36-apache

# curl extension is built into the official php image
COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
