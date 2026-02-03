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

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * @param PayPal $module
 *
 * @return bool
 */
function upgrade_module_6_5_2(PayPal $module)
{
    $methods = [
        new MethodEC(),
        new MethodPPP(),
        new MethodMB(),
    ];
    $sandboxModeList = [true, false];

    foreach ($sandboxModeList as $mode) {
        /** @var PaypalAddons\classes\AbstractMethodPaypal $method */
        foreach ($methods as $method) {
            $method->setSandbox($mode);

            if (!$method->isConfigured()) {
                continue;
            }

            $config = [
                'clientId' => $method->getClientId(),
                'secret' => $method->getSecret(),
                'merchantId' => $method->getMerchantId(),
                'isSandbox' => $method->isSandbox(),
            ];
            $method->setConfig($config);
        }
    }

    return true;
}
