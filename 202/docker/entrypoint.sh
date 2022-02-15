#!/bin/bash

set -e
set -x

if [ "${RUN_USER}" != "www-data" ]; then 
useradd -m $RUN_USER; 
echo "export APACHE_RUN_USER=$RUN_USER \
export APACHE_RUN_GROUP=$RUN_USER" >> /etc/apache2/envvars 
fi

/etc/init.d/mariadb start

if [ "$PS_DOMAIN" ]; then 
    mysql -h localhost -u root prestashop -e "UPDATE ps_shop_url SET domain='$PS_DOMAIN', domain_ssl='$PS_DOMAIN'"
fi

cd  /var/www/html/modules/paypal

composer update

php /var/www/html/bin/console prestashop:module install paypal -e prod

chown $RUN_USER:$RUN_USER /var/www/html -Rf

exec apache2-foreground
