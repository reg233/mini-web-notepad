FROM php:7.4-apache

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

RUN a2enmod rewrite

COPY . ./

RUN mv mini-web-notepad-entrypoint /usr/local/bin/
RUN chmod +x /usr/local/bin/mini-web-notepad-entrypoint
ENTRYPOINT ["mini-web-notepad-entrypoint"]
CMD ["apache2-foreground"]
