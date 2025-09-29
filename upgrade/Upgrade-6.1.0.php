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

use PaypalAddons\classes\InstallmentBanner\ConfigurationMap;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * @param PayPal $module
 *
 * @return bool
 */
function upgrade_module_6_1_0($module)
{
    foreach (Shop::getShops() as $shop) {
        $defaultConf = [
            'homepage' => [
                'placement' => 'homepage',
                'status' => 'disabled',
                'color' => 'blue',
                'layout' => 'flex',
                'ratio' => '20x1',
            ],
            'cart' => [
                'placement' => 'cart',
                'status' => 'disabled',
                'layout' => 'text',
                'logo-type' => 'primary',
                'logo-position' => 'left',
                'text-color' => 'black',
                'text-size' => '12',
            ],
            'category' => [
                'placement' => 'category',
                'status' => 'disabled',
                'color' => 'blue',
                'layout' => 'flex',
                'ratio' => '20x1',
            ],
            'checkout' => [
                'placement' => 'checkout',
                'status' => 'disabled',
                'layout' => 'text',
                'logo-type' => 'primary',
                'logo-position' => 'left',
                'text-color' => 'black',
                'text-size' => '12',
            ],
            'product' => [
                'placement' => 'product',
                'status' => 'disabled',
                'layout' => 'text',
                'logo-type' => 'primary',
                'logo-position' => 'left',
                'text-color' => 'black',
                'text-size' => '12',
            ],
        ];

        if ('1' === Configuration::get(ConfigurationMap::HOME_PAGE, null, (int) $shop['id_shop_group'], (int) $shop['id_shop'])) {
            $defaultConf['homepage']['status'] = 'enabled';

            if ($color = Configuration::get(ConfigurationMap::COLOR, null, (int) $shop['id_shop_group'], (int) $shop['id_shop'])) {
                $defaultConf['homepage']['color'] = $color;
            }
        }
        if ('1' === Configuration::get(ConfigurationMap::CATEGORY_PAGE, null, (int) $shop['id_shop_group'], (int) $shop['id_shop'])) {
            $defaultConf['category']['status'] = 'enabled';

            if ($color = Configuration::get(ConfigurationMap::COLOR, null, (int) $shop['id_shop_group'], (int) $shop['id_shop'])) {
                $defaultConf['category']['color'] = $color;
            }
        }
        if ('1' === Configuration::get(ConfigurationMap::PRODUCT_PAGE, null, (int) $shop['id_shop_group'], (int) $shop['id_shop'])) {
            $defaultConf['product']['status'] = 'enabled';
        }
        if ('1' === Configuration::get(ConfigurationMap::CART_PAGE, null, (int) $shop['id_shop_group'], (int) $shop['id_shop'])) {
            $defaultConf['cart']['status'] = 'enabled';
        }
        if ('1' === Configuration::get(ConfigurationMap::CHECKOUT_PAGE, null, (int) $shop['id_shop_group'], (int) $shop['id_shop'])) {
            $defaultConf['checkout']['status'] = 'enabled';
        }

        Configuration::updateValue(
            ConfigurationMap::MESSENGING_CONFIG,
            json_encode($defaultConf),
            false,
            (int) $shop['id_shop_group'],
            (int) $shop['id_shop']
        );
    }

    return true;
}
