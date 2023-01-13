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

use PaypalPPBTlib\Install\ModuleInstaller;

/**
 * @param $module PayPal
 *
 * @return bool
 */
function upgrade_module_5_5_0($module)
{
    try {
        $installer = new ModuleInstaller($module);
        $installer->installObjectModels();
        Configuration::updateGlobalValue(PayPal::NEED_INSTALL_MODELS, 0);
    } catch (Exception $e) {
        Configuration::updateGlobalValue(PayPal::NEED_INSTALL_MODELS, 1);
    }

    $count = Db::getInstance()->getValue('SELECT count(*) 
	    FROM INFORMATION_SCHEMA.COLUMNS
		WHERE `TABLE_NAME` = "' . _DB_PREFIX_ . 'paypal_order"
		AND `TABLE_SCHEMA` = "' . _DB_NAME_ . '"
		AND `COLUMN_NAME` = "intent"');
    if ($count == 0) {
        Db::getInstance()->Execute('ALTER TABLE `' . _DB_PREFIX_ . 'paypal_order` ADD COLUMN `intent` VARCHAR(250)');
    }

    return true;
}
