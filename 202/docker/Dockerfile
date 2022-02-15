FROM 202ecommerce/prestashop:1.7.8.3

RUN rm -Rf var/www/html/modules/paypal/

WORKDIR /var/www/html/modules/paypal/

ENTRYPOINT ["sh", "202/docker/entrypoint.sh"]
