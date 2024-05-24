#!/bin/bash

#
# Since 2007 PayPal
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
#  @author Since 2007 PayPal
#  @author 202 ecommerce <tech@202-ecommerce.com>
#  @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
#  @copyright PayPal
#
#

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
