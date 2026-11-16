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

namespace PaypalAddons\classes\Security;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Shop-specific token for the module's self-check endpoints
 * (webhook availability, TLS test), which are only meant to be called by the shop itself.
 */
class InternalCheckToken
{
    const SCOPE_WEBHOOK_AVAILABILITY = 'webhook_availability';

    const SCOPE_TLS_CHECK = 'tls_check';

    /**
     * @param string $scope
     *
     * @return string
     */
    public static function get($scope)
    {
        return hash_hmac('sha256', 'paypal|' . $scope, _COOKIE_KEY_);
    }

    /**
     * @param string $scope
     * @param mixed $token
     *
     * @return bool
     */
    public static function isValid($scope, $token)
    {
        if (!is_string($token) || $token === '') {
            return false;
        }

        return hash_equals(self::get($scope), $token);
    }
}
