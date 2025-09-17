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

class PaypalConfigurations
{
    const CUSTOMIZE_ORDER_STATUS = 'PAYPAL_CUSTOMIZE_ORDER_STATUS';

    const INTENT = 'PAYPAL_API_INTENT';

    const API_CARD = 'PAYPAL_API_CARD';

    const VAULTING = 'PAYPAL_VAULTING';

    const ACCOUNT_VAULTING = 'PAYPAL_ACCOUNT_VAULTING';

    const MERCHANT_INSTALLMENT = 'PAYPAL_MERCHANT_INSTALLMENT';

    const MB_EC_ENABLED = 'PAYPAL_MB_EC_ENABLED';

    const EXPRESS_CHECKOUT_IN_CONTEXT = 'PAYPAL_EXPRESS_CHECKOUT_IN_CONTEXT';

    const BRAND_NAME = 'PAYPAL_CONFIG_BRAND';

    const API_ADVANTAGES = 'PAYPAL_API_ADVANTAGES';

    const PUI_ENABLED = 'PAYPAL_PUI_ENABLED';

    const SEPA_ENABLED = 'PAYPAL_SEPA_ENABLED';

    const SOFORT_ENABLED = 'PAYPAL_SOFORT_ENABLED';

    const VENMO_OPTION = 'PAYPAL_VENMO_ENABLED';

    const PUI_CUSTOMER_SERVICE_INSTRUCTIONS = 'PAYPAL_PUI_CUSTOMER_SERVICE_INSTRUCTIONS';

    const ACDC_OPTION = 'PAYPAL_ACDC_OPTION';

    const OS_REFUNDED = 'PAYPAL_OS_REFUNDED';

    const OS_CANCELED = 'PAYPAL_OS_CANCELED';

    const OS_ACCEPTED = 'PAYPAL_OS_ACCEPTED';

    const OS_CAPTURE_CANCELED = 'PAYPAL_OS_CAPTURE_CANCELED';

    const OS_ACCEPTED_TWO = 'PAYPAL_OS_ACCEPTED_TWO';

    const OS_WAITING_VALIDATION = 'PAYPAL_OS_WAITING_VALIDATION';

    const OS_PROCESSING = 'PAYPAL_OS_PROCESSING';

    const OS_VALIDATION_ERROR = 'PAYPAL_OS_VALIDATION_ERROR';

    const OS_REFUNDED_PAYPAL = 'PAYPAL_OS_REFUNDED_PAYPAL';

    const MOVE_BUTTON_AT_END = 'PAYPAL_MOVE_BUTTON_AT_END';

    const SHOW_MODAL_CONFIGURATION = 'PAYPAL_SHOW_MODAL_CONFIGURATION';

    const CLOUDSYNC_ENABLED = 'PAYPAL_CLOUDSYNC_ENABLED';
}
