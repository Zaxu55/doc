FROM php:8.1-apache

COPY doc.php /var/www/html/doc.php
RUN chmod 644 /var/www/html/doc.php

EXPOSE 80
