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

function upgrade_module_4_3_0($module)
{
    if (!$module->registerHook('displayInvoiceLegalFreeText')) {
        return false;
    }

    Configuration::updateValue('PAYPAL_EXPRESS_CHECKOUT_IN_CONTEXT', '');
    Configuration::updateValue('PAYPAL_MERCHANT_ID_SANDBOX', '');
    Configuration::updateValue('PAYPAL_MERCHANT_ID_LIVE', '');

    if (Configuration::get('PAYPAL_METHOD') == 'EC') {
        Configuration::updateValue('PAYPAL_METHOD', '');

        Configuration::updateValue('PAYPAL_USERNAME_SANDBOX', '');
        Configuration::updateValue('PAYPAL_PSWD_SANDBOX', '');
        Configuration::updateValue('PAYPAL_SIGNATURE_SANDBOX', '');
        Configuration::updateValue('PAYPAL_SANDBOX_ACCESS', '');

        Configuration::updateValue('PAYPAL_USERNAME_LIVE', '');
        Configuration::updateValue('PAYPAL_PSWD_LIVE', '');
        Configuration::updateValue('PAYPAL_SIGNATURE_LIVE', '');
        Configuration::updateValue('PAYPAL_LIVE_ACCESS', '');
    }

    return true;
}
