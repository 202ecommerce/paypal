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

use PaypalAddons\classes\Shortcut\ShortcutConfiguration;
use PaypalPPBTlib\Install\ModuleInstaller;

/**
 * @param $module PayPal
 *
 * @return bool
 */
function upgrade_module_5_3_0($module)
{
    $installer = new ModuleInstaller($module);
    $shops = Shop::getShops();
    Configuration::updateGlobalValue(ShortcutConfiguration::USE_OLD_HOOK, 1);

    if (Shop::isFeatureActive()) {
        foreach ($shops as $shop) {
            Configuration::updateValue(
                ShortcutConfiguration::PRODUCT_PAGE_HOOK,
                ShortcutConfiguration::HOOK_REASSURANCE,
                false,
                null,
                (int) $shop['id_shop']
            );

            Configuration::updateValue(
                ShortcutConfiguration::CART_PAGE_HOOK,
                ShortcutConfiguration::HOOK_SHOPPING_CART_FOOTER,
                false,
                null,
                (int) $shop['id_shop']
            );
        }
    } else {
        Configuration::updateValue(ShortcutConfiguration::PRODUCT_PAGE_HOOK, ShortcutConfiguration::HOOK_REASSURANCE);
        Configuration::updateValue(ShortcutConfiguration::CART_PAGE_HOOK, ShortcutConfiguration::HOOK_SHOPPING_CART_FOOTER);
    }
    // Registre the new hooks
    return $installer->registerHooks();
}
