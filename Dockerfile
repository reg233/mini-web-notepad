FROM php:8.3-apache

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" && \
    a2enmod headers rewrite

COPY . ./

RUN mv mini-web-notepad-entrypoint /usr/local/bin/ && \
    chmod +x /usr/local/bin/mini-web-notepad-entrypoint

ENTRYPOINT ["mini-web-notepad-entrypoint"]
CMD ["apache2-foreground"]
