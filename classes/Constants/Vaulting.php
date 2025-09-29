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

namespace PaypalAddons\classes\Constants;

if (!defined('_PS_VERSION_')) {
    exit;
}

class Vaulting
{
    public const ACCOUNT_VAULTING_STATE = 'PAYPAL_ACCOUNT_VAULTING_STATE';

    public const IS_AVAILABLE = 1;

    public const IS_UNAVAILABLE = 2;

    public const CAPABILITY = 'PAYPAL_WALLET_VAULTING_ADVANCED';

    public const STORE_IN_VAULT_ON_SUCCESS = 'ON_SUCCESS';

    public const USAGE_TYPE_MERCHANT = 'MERCHANT';

    public const CUSTOMER_TYPE_CONSUMER = 'CONSUMER';

    public const ENABLED = 1;

    public const DISABLED = 0;

    public const PAYMENT_SOURCE_PAYPAL = 'paypal';

    public const STATUS_VAULTED = 'VAULTED';

    public const STATUS_APPROVED = 'APPROVED';

    public const PRODUCT = 'PAYPAL_ADVANCED_VAULTING';

    public const PRODUCT_STATUS_ACTIVE = 'ACTIVE';

    public const PRODUCT_STATUS_APPROVED = 'APPROVED';

    public const PRODUCT_STATUS_SUBSCRIBED = 'SUBSCRIBED';

    public const PRODUCT_STATUS_PENDING = 'PENDING';

    public const PRODUCT_STATUS_IN_REVIEW = 'IN_REVIEW';

    public const PRODUCT_STATUS_NEED_MORE_DATA = 'NEED_MORE_DATA';

    public const PRODUCT_STATUS_NEED_DATA = 'NEED_DATA';

    public const PRODUCT_STATUS_DECLINED = 'DECLINED';

    public const PRODUCT_STATUS_DENIED = 'DENIED';

    public const PRODUCT_STATUS_REFUSED = 'REFUSED';
}
