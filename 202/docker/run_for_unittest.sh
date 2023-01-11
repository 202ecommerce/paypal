#!/bin/bash

set -e
set -x

/etc/init.d/mariadb start

php /var/www/html/bin/console prestashop:module install paypal -e prod

mysql -h localhost -u root prestashop -e "
TRUNCATE ps_paypal_webhook;
TRUNCATE ps_paypal_order;
"

cd /var/www/html/modules/paypal/

php -dmemory_limit=512M vendor/bin/phpunit -c 202/phpunit.xml

chown www-data:www-data /var/www/html/var -Rf
