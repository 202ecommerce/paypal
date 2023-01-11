{**
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
 *
 *}

<div data-container-express-checkout data-paypal-source-page='cart' style="float:right; margin: 10px 40px 0 0">
    <form data-paypal-payment-form-cart class="paypal_payment_form" action="{$action_url|escape:'htmlall':'UTF-8'}" title="{l s='Pay with PayPal' mod='paypal'}" method="post" data-ajax="false">
        <input type="hidden" name="method" value="{$PayPal_payment_type|escape:'htmlall':'UTF-8'}"/>
        <input type="hidden" name="current_shop_url" id="paypal_url_page" value="" />
        <input type="hidden" id="in_context_checkout_enabled" value="0">
        <input type="hidden" id="source_page" value="cart">
        <img src="{$PayPal_img_esc|escape:'htmlall':'UTF-8'}" data-paypal-shortcut-btn alt="{l s='PayPal' mod='paypal'}" style="cursor:pointer;"/>
    </form>
</div>
<div class="clearfix"></div>
