<?php

/*
 * Since 2007 PayPal
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
 *  @author Since 2007 PayPal
 *  @author 202 ecommerce <tech@202-ecommerce.com>
 *  @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 *  @copyright PayPal
 *
 */

namespace PaypalAddons\services;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Builds and parses the `custom_id` value sent to PayPal on `purchase_units[0].custom_id`
 * at order-create time. PayPal echoes this value back unchanged on webhook events and on
 * the shortcut shipping callback, which is how id_cart is recovered without keeping a
 * separate cart/payment mapping table.
 */
class CustomId
{
    /**
     * @param \Cart $cart
     *
     * @return string
     */
    public function build(\Cart $cart)
    {
        $module = \Module::getInstanceByName('paypal');
        $return = (string) _PS_VERSION_ . '_' . (string) $module->version . '_' . \phpversion() . '_';

        if (\Tools::getValue('sc') !== false) {
            $return .= 'ESC_';
        }

        $return .= $module->l('Cart ID: ', 'CustomId') . $cart->id . '_';
        $return .= $module->l('Shop name: ', 'CustomId') . \Configuration::get('PS_SHOP_NAME', null, $cart->id_shop);

        return \substr($return, 0, 137);
    }

    /**
     * @param string|null $customId
     *
     * @return int|null
     */
    public function getCartId($customId)
    {
        foreach (explode('_', (string) $customId) as $part) {
            if (strpos($part, 'Cart') === 0) {
                return (int) trim(explode(':', $part)[1]);
            }
        }

        return null;
    }
}
