<?php
/**
 * 2007-2023 PayPal
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 *  versions in the future. If you wish to customize PrestaShop for your
 *  needs please refer to http://www.prestashop.com for more information.
 *
 *  @author 2007-2023 PayPal
 *  @author 202 ecommerce <tech@202-ecommerce.com>
 *  @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 *  @copyright PayPal
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_4_4_0($module)
{
    Configuration::updateValue('PAYPAL_VAULTING', 0);
    Configuration::updateValue('PAYPAL_CONFIG_BRAND', '');
    Configuration::updateValue('PAYPAL_CONFIG_LOGO', '');

    if (!$module->registerHook('displayMyAccountBlock')
        || !$module->registerHook('displayCustomerAccount')) {
        return false;
    }

    $sql = [];

    $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'paypal_customer` (
              `id_paypal_customer` INT(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
              `id_customer` INT(11),
              `reference` VARCHAR(55),
              `method` VARCHAR(55),
              `date_add` DATETIME,
              `date_upd` DATETIME
        ) ENGINE = ' . _MYSQL_ENGINE_;

    $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'paypal_vaulting` (
              `id_paypal_vaulting` INT(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
              `id_paypal_customer` INT(11),
              `token` VARCHAR(255),
              `name` VARCHAR(255),
              `info` VARCHAR(255),
              `payment_tool` VARCHAR(255),
              `date_add` DATETIME,
              `date_upd` DATETIME
        ) ENGINE = ' . _MYSQL_ENGINE_;

    foreach ($sql as $q) {
        if (!Db::getInstance()->execute($q)) {
            return false;
        }
    }

    if (Configuration::get('PAYPAL_BRAINTREE_OS_AWAITING')
        && Validate::isLoadedObject(new OrderState(Configuration::get('PAYPAL_BRAINTREE_OS_AWAITING')))) {
        $order_state = new OrderState(Configuration::get('PAYPAL_BRAINTREE_OS_AWAITING'));
        $source = _PS_MODULE_DIR_ . 'paypal/views/img/os_braintree.png';
        $destination = _PS_ROOT_DIR_ . '/img/os/' . (int) $order_state->id . '.gif';
        copy($source, $destination);
    }
    if (Configuration::get('PAYPAL_BRAINTREE_OS_AWAITING_VALIDATION')
        && Validate::isLoadedObject(new OrderState(Configuration::get('PAYPAL_BRAINTREE_OS_AWAITING_VALIDATION')))) {
        $order_state = new OrderState(Configuration::get('PAYPAL_BRAINTREE_OS_AWAITING_VALIDATION'));
        $source = _PS_MODULE_DIR_ . 'paypal/views/img/os_braintree.png';
        $destination = _PS_ROOT_DIR_ . '/img/os/' . (int) $order_state->id . '.gif';
        copy($source, $destination);
    }

    return true;
}
