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

require_once _PS_MODULE_DIR_ . 'paypal/classes/AbstractMethodPaypal.php';

use PaypalPPBTlib\Install\ModuleInstaller;

/**
 * @param $module PayPal
 *
 * @return bool
 */
function upgrade_module_5_1_0($module)
{
    $configs = [
        'PAYPAL_CUSTOMIZE_ORDER_STATUS' => 0,
        'PAYPAL_OS_REFUNDED' => (int) Configuration::get('PS_OS_REFUND'),
        'PAYPAL_OS_CANCELED' => (int) Configuration::get('PS_OS_CANCELED'),
        'PAYPAL_OS_ACCEPTED' => (int) Configuration::get('PS_OS_PAYMENT'),
        'PAYPAL_OS_CAPTURE_CANCELED' => (int) Configuration::get('PS_OS_CANCELED'),
        'PAYPAL_OS_ACCEPTED_TWO' => (int) Configuration::get('PS_OS_PAYMENT'),
        'PAYPAL_OS_WAITING_VALIDATION' => (int) Configuration::get('PAYPAL_OS_WAITING'),
        'PAYPAL_OS_PROCESSING' => (int) Configuration::get('PAYPAL_OS_WAITING'),
        'PAYPAL_OS_VALIDATION_ERROR' => (int) Configuration::get('PS_OS_ERROR'),
        'PAYPAL_OS_REFUNDED_PAYPAL' => (int) Configuration::get('PS_OS_REFUND'),
    ];
    $shops = Shop::getShops();
    $tab = Tab::getInstanceFromClassName('AdminParentPaypalConfiguration');
    $return = true;
    $installer = new ModuleInstaller($module);

    if (Validate::isLoadedObject($tab)) {
        $tab->active = false;
        $return &= $tab->save();
    }

    $return &= $installer->uninstallObjectModel('PaypalVaulting');
    $return &= $installer->installObjectModel('PaypalVaulting');

    foreach ($configs as $config => $value) {
        if (Shop::isFeatureActive()) {
            foreach ($shops as $shop) {
                $return &= Configuration::updateValue($config, $value, false, null, (int) $shop['id_shop']);
            }
        } else {
            $return &= Configuration::updateValue($config, $value);
        }
    }

    return $return;
}
