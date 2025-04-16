{**
 * since 2007 PayPal
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
 *  @author since 2007 PayPal
 *  @author 202 ecommerce <tech@202-ecommerce.com>
 *  @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 *  @copyright PayPal
 *
 *}

{include file="module:paypal/views/templates/_partials/javascript.tpl"}

<div style="display: flex">
  <div paypal-venmo-button-container></div>
</div>

{literal}
<script>
    function waitPaypalVenmoSDKIsLoaded() {
        if (typeof totVenmoPaypalSdkButtons === 'undefined' || typeof Venmo === 'undefined') {
            setTimeout(waitPaypalVenmoSDKIsLoaded, 200);
            return;
        }

        var venmoButton = new Venmo({
            container: '[paypal-venmo-button-container]',
            controller: '{/literal}{Context::getContext()->link->getModuleLink('paypal', 'ScInit') nofilter}{literal}',
            validationController: '{/literal}{Context::getContext()->link->getModuleLink('paypal', 'ecValidation') nofilter}{literal}'
        });
        window.venmoObj = venmoButton;

        venmoButton.initButton();
        venmoButton.hideElementTillPaymentOptionChecked(
          '[data-module-name="paypal_venmo"]',
          '#payment-confirmation'
        );
        venmoButton.showElementIfPaymentOptionChecked(
          '[data-module-name="paypal_venmo"]',
          '[paypal-venmo-button-container]'
        );
    }

    waitPaypalVenmoSDKIsLoaded();
</script>
{/literal}
