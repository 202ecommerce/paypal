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

namespace PaypalAddons\classes\Shortcut;

class ShortcutConfiguration
{
    const SHOW_ON_SIGNUP_STEP = 'PAYPAL_EXPRESS_CHECKOUT_SHORTCUT_SIGNUP';

    const SHOW_ON_CART_PAGE = 'PAYPAL_EXPRESS_CHECKOUT_SHORTCUT_CART';

    const SHOW_ON_PRODUCT_PAGE = 'PAYPAL_EXPRESS_CHECKOUT_SHORTCUT';

    const CUSTOMIZE_STYLE = 'PAYPAL_EXPRESS_CHECKOUT_CUSTOMIZE_SHORTCUT_STYLE';

    const DISPLAY_MODE_PRODUCT = 'PAYPAL_EXPRESS_CHECKOUT_DISPLAY_MODE_PRODUCT';

    const DISPLAY_MODE_SIGNUP = 'PAYPAL_EXPRESS_CHECKOUT_DISPLAY_MODE_SIGNUP';

    const DISPLAY_MODE_CART = 'PAYPAL_EXPRESS_CHECKOUT_DISPLAY_MODE_CART';

    const DISPLAY_MODE_TYPE_HOOK = 1;

    const DISPLAY_MODE_TYPE_WIDGET = 2;

    const HOOK_PRODUCT_ACTIONS = 'displayProductActions';

    const HOOK_REASSURANCE = 'displayReassurance';

    const HOOK_AFTER_PRODUCT_THUMBS = 'displayAfterProductThumbs';

    const HOOK_AFTER_PRODUCT_ADDITIONAL_INFO = 'displayProductAdditionalInfo';

    const HOOK_FOOTER_PRODUCT = 'displayFooterProduct';

    const HOOK_EXPRESS_CHECKOUT = 'displayExpressCheckout';

    const HOOK_SHOPPING_CART_FOOTER = 'displayShoppingCartFooter';

    const HOOK_PERSONAL_INFORMATION_TOP = 'displayPersonalInformationTop';

    const PRODUCT_PAGE_HOOK = 'PAYPAL_EXPRESS_CHECKOUT_SHORTCUT_HOOK_PRODUCT';

    const CART_PAGE_HOOK = 'PAYPAL_EXPRESS_CHECKOUT_SHORTCUT_HOOK_CART';

    const STYLE_LABEL_PRODUCT = 'PAYPAL_EXPRESS_CHECKOUT_SHORTCUT_STYLE_LABEL_PRODUCT';

    const STYLE_LABEL_CART = 'PAYPAL_EXPRESS_CHECKOUT_SHORTCUT_STYLE_LABEL_CART';

    const STYLE_LABEL_SIGNUP = 'PAYPAL_EXPRESS_CHECKOUT_SHORTCUT_STYLE_LABEL_SIGNUP';

    const STYLE_HEIGHT_PRODUCT = 'PAYPAL_EXPRESS_CHECKOUT_SHORTCUT_STYLE_HEIGHT_PRODUCT';

    const STYLE_HEIGHT_CART = 'PAYPAL_EXPRESS_CHECKOUT_SHORTCUT_STYLE_HEIGHT_CART';

    const STYLE_HEIGHT_SIGNUP = 'PAYPAL_EXPRESS_CHECKOUT_SHORTCUT_STYLE_HEIGHT_SIGNUP';

    const STYLE_WIDTH_PRODUCT = 'PAYPAL_EXPRESS_CHECKOUT_SHORTCUT_STYLE_WIDTH_PRODUCT';

    const STYLE_WIDTH_CART = 'PAYPAL_EXPRESS_CHECKOUT_SHORTCUT_STYLE_WIDTH_CART';

    const STYLE_WIDTH_SIGNUP = 'PAYPAL_EXPRESS_CHECKOUT_SHORTCUT_STYLE_WIDTH_SIGNUP';

    const STYLE_COLOR_PRODUCT = 'PAYPAL_EXPRESS_CHECKOUT_SHORTCUT_STYLE_COLOR_PRODUCT';

    const STYLE_COLOR_CART = 'PAYPAL_EXPRESS_CHECKOUT_SHORTCUT_STYLE_COLOR_CART';

    const STYLE_COLOR_SIGNUP = 'PAYPAL_EXPRESS_CHECKOUT_SHORTCUT_STYLE_COLOR_SIGNUP';

    const STYLE_SHAPE_PRODUCT = 'PAYPAL_EXPRESS_CHECKOUT_SHORTCUT_STYLE_SHAPE_PRODUCT';

    const STYLE_SHAPE_CART = 'PAYPAL_EXPRESS_CHECKOUT_SHORTCUT_STYLE_SHAPE_CART';

    const STYLE_SHAPE_SIGNUP = 'PAYPAL_EXPRESS_CHECKOUT_SHORTCUT_STYLE_SHAPE_SIGNUP';

    const TYPE_STYLE_SELECT = 1;

    const TYPE_STYLE_TEXT = 2;

    const STYLE_COLOR_GOLD = 'gold';

    const STYLE_COLOR_BLUE = 'blue';

    const STYLE_COLOR_SILVER = 'silver';

    const STYLE_COLOR_WHITE = 'white';

    const STYLE_COLOR_BLACK = 'black';

    const STYLE_SHAPE_RECT = 'rect';

    const STYLE_SHAPE_PILL = 'pill';

    const STYLE_LABEL_PAYPAL = 'paypal';

    const STYLE_LABEL_CHECKOUT = 'checkout';

    const STYLE_LABEL_BUYNOW = 'buynow';

    const STYLE_LABEL_PAY = 'pay';

    const CONFIGURATION_TYPE_COLOR = 'color';

    const CONFIGURATION_TYPE_SHAPE = 'shape';

    const CONFIGURATION_TYPE_LABEL = 'label';

    const CONFIGURATION_TYPE_WIDTH = 'width';

    const CONFIGURATION_TYPE_HEIGHT = 'height';

    const SOURCE_PAGE_PRODUCT = 1;

    const SOURCE_PAGE_CART = 2;

    const SOURCE_PAGE_SIGNUP = 3;

    const USE_OLD_HOOK = 'PAYPAL_USE_OLD_HOOK';
}
