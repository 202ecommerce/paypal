#!/bin/bash

#
# 2007-2024 PayPal
#
# NOTICE OF LICENSE
#
# This source file is subject to the Academic Free License (AFL 3.0)
# that is bundled with this package in the file LICENSE.txt.
# It is also available through the world-wide-web at this URL:
# http://opensource.org/licenses/afl-3.0.php
# If you did not receive a copy of the license and are unable to
# obtain it through the world-wide-web, please send an email
# to license@prestashop.com so we can send you a copy immediately.
#
# DISCLAIMER
#
# Do not edit or add to this file if you wish to upgrade PrestaShop to newer
#  versions in the future. If you wish to customize PrestaShop for your
#  needs please refer to http://www.prestashop.com for more information.
#
#  @author 2007-2024 PayPal
#  @author 202 ecommerce <tech@202-ecommerce.com>
#  @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
#  @copyright PayPal
#
#

set -e
set -x

/etc/init.d/mariadb start

php /var/www/html/bin/console prestashop:module install paypal -e prod

mysql -h localhost -u root prestashop -e "
TRUNCATE ps_paypal_webhook;
TRUNCATE ps_paypal_order;
UPDATE ps_configuration SET value = '0' WHERE name = 'PAYPAL_CUSTOMIZE_ORDER_STATUS';
"

cd /var/www/html/modules/paypal/

php -dmemory_limit=512M vendor/bin/phpunit -c 202/phpunit.xml

chown www-data:www-data /var/www/html/var -Rf
