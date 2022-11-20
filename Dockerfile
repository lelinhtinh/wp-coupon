FROM wordpress:latest

WORKDIR /var/www/html
VOLUME /var/www/html

RUN usermod --non-unique --uid 1000 www-data \
  && groupmod --non-unique --gid 1000 www-data \
  && chown -R www-data:www-data /var/www

CMD ["apache2-foreground"]