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

namespace PaypalAddons\classes\Shortcut;

if (!defined('_PS_VERSION_')) {
    exit;
}

class ShortcutConfiguration
{
    public const SHOW_ON_SIGNUP_STEP = 'PAYPAL_EXPRESS_CHECKOUT_SHORTCUT_SIGNUP';

    public const SHOW_ON_CART_PAGE = 'PAYPAL_EXPRESS_CHECKOUT_SHORTCUT_CART';

    public const SHOW_ON_PRODUCT_PAGE = 'PAYPAL_EXPRESS_CHECKOUT_SHORTCUT';

    public const CUSTOMIZE_STYLE = 'PAYPAL_EXPRESS_CHECKOUT_CUSTOMIZE_SHORTCUT_STYLE';

    public const DISPLAY_MODE_PRODUCT = 'PAYPAL_EXPRESS_CHECKOUT_DISPLAY_MODE_PRODUCT';

    public const DISPLAY_MODE_SIGNUP = 'PAYPAL_EXPRESS_CHECKOUT_DISPLAY_MODE_SIGNUP';

    public const DISPLAY_MODE_CART = 'PAYPAL_EXPRESS_CHECKOUT_DISPLAY_MODE_CART';

    public const DISPLAY_MODE_TYPE_HOOK = 1;

    public const DISPLAY_MODE_TYPE_WIDGET = 2;

    public const HOOK_PRODUCT_ACTIONS = 'displayProductActions';

    public const HOOK_REASSURANCE = 'displayReassurance';

    public const HOOK_AFTER_PRODUCT_THUMBS = 'displayAfterProductThumbs';

    public const HOOK_AFTER_PRODUCT_ADDITIONAL_INFO = 'displayProductAdditionalInfo';

    public const HOOK_FOOTER_PRODUCT = 'displayFooterProduct';

    public const HOOK_EXPRESS_CHECKOUT = 'displayExpressCheckout';

    public const HOOK_SHOPPING_CART_FOOTER = 'displayShoppingCartFooter';

    public const HOOK_PERSONAL_INFORMATION_TOP = 'displayPersonalInformationTop';

    public const PRODUCT_PAGE_HOOK = 'PAYPAL_EXPRESS_CHECKOUT_SHORTCUT_HOOK_PRODUCT';

    public const CART_PAGE_HOOK = 'PAYPAL_EXPRESS_CHECKOUT_SHORTCUT_HOOK_CART';

    public const STYLE_LABEL_PRODUCT = 'PAYPAL_EXPRESS_CHECKOUT_SHORTCUT_STYLE_LABEL_PRODUCT';

    public const STYLE_LABEL_CART = 'PAYPAL_EXPRESS_CHECKOUT_SHORTCUT_STYLE_LABEL_CART';

    public const STYLE_LABEL_SIGNUP = 'PAYPAL_EXPRESS_CHECKOUT_SHORTCUT_STYLE_LABEL_SIGNUP';

    public const STYLE_HEIGHT_PRODUCT = 'PAYPAL_EXPRESS_CHECKOUT_SHORTCUT_STYLE_HEIGHT_PRODUCT';

    public const STYLE_HEIGHT_CART = 'PAYPAL_EXPRESS_CHECKOUT_SHORTCUT_STYLE_HEIGHT_CART';

    public const STYLE_HEIGHT_SIGNUP = 'PAYPAL_EXPRESS_CHECKOUT_SHORTCUT_STYLE_HEIGHT_SIGNUP';

    public const STYLE_WIDTH_PRODUCT = 'PAYPAL_EXPRESS_CHECKOUT_SHORTCUT_STYLE_WIDTH_PRODUCT';

    public const STYLE_WIDTH_CART = 'PAYPAL_EXPRESS_CHECKOUT_SHORTCUT_STYLE_WIDTH_CART';

    public const STYLE_WIDTH_SIGNUP = 'PAYPAL_EXPRESS_CHECKOUT_SHORTCUT_STYLE_WIDTH_SIGNUP';

    public const STYLE_COLOR_PRODUCT = 'PAYPAL_EXPRESS_CHECKOUT_SHORTCUT_STYLE_COLOR_PRODUCT';

    public const STYLE_COLOR_CART = 'PAYPAL_EXPRESS_CHECKOUT_SHORTCUT_STYLE_COLOR_CART';

    public const STYLE_COLOR_SIGNUP = 'PAYPAL_EXPRESS_CHECKOUT_SHORTCUT_STYLE_COLOR_SIGNUP';

    public const STYLE_SHAPE_PRODUCT = 'PAYPAL_EXPRESS_CHECKOUT_SHORTCUT_STYLE_SHAPE_PRODUCT';

    public const STYLE_SHAPE_CART = 'PAYPAL_EXPRESS_CHECKOUT_SHORTCUT_STYLE_SHAPE_CART';

    public const STYLE_SHAPE_SIGNUP = 'PAYPAL_EXPRESS_CHECKOUT_SHORTCUT_STYLE_SHAPE_SIGNUP';

    public const TYPE_STYLE_SELECT = 1;

    public const TYPE_STYLE_TEXT = 2;

    public const STYLE_COLOR_GOLD = 'gold';

    public const STYLE_COLOR_BLUE = 'blue';

    public const STYLE_COLOR_SILVER = 'silver';

    public const STYLE_COLOR_WHITE = 'white';

    public const STYLE_COLOR_BLACK = 'black';

    public const STYLE_SHAPE_RECT = 'rect';

    public const STYLE_SHAPE_PILL = 'pill';

    public const STYLE_LABEL_PAYPAL = 'paypal';

    public const STYLE_LABEL_CHECKOUT = 'checkout';

    public const STYLE_LABEL_BUYNOW = 'buynow';

    public const STYLE_LABEL_PAY = 'pay';

    public const CONFIGURATION_TYPE_COLOR = 'color';

    public const CONFIGURATION_TYPE_SHAPE = 'shape';

    public const CONFIGURATION_TYPE_LABEL = 'label';

    public const CONFIGURATION_TYPE_WIDTH = 'width';

    public const CONFIGURATION_TYPE_HEIGHT = 'height';

    public const SOURCE_PAGE_PRODUCT = 1;

    public const SOURCE_PAGE_CART = 2;

    public const SOURCE_PAGE_SIGNUP = 3;

    public const USE_OLD_HOOK = 'PAYPAL_USE_OLD_HOOK';
}
