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

{extends 'module:paypal/views/templates/bnpl/bnpl-layout.tpl'}

{block name='content'}

  <style>
    [data-container-bnpl] {
      margin: 10px 0;
      display: flex;
      flex-direction: column;
      align-items: flex-start;
    }

  </style>

  <div data-container-bnpl data-paypal-bnpl-source-page="payment-step">
    <form data-paypal-bnpl-payment-form-cart class="paypal_payment_form" action="{$action_url|escape:'htmlall':'UTF-8'}" method="post" data-ajax="false">
      <input type="hidden" name="express_checkout" value="{$PayPal_payment_type|escape:'htmlall':'UTF-8'}"/>
      <input type="hidden" name="current_shop_url" data-paypal-url-page value="" />
      <input type="hidden" id="source_page" name="source_page" value="cart">
      <input type="hidden" name="isAddAddress" value="1">
    </form>
    <div paypal-bnpl-button-container></div>

    <div style="display: none" class="alert alert-danger" paypal-wrong-button-message>
      <div>{l s='Please click on the \'Pay in X times\' button' mod='paypal'}</div>
    </div>
  </div>
  <div class="clearfix"></div>
{/block}

{block name='js'}
  <script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelector('#payment-confirmation button').addEventListener('click', function(event) {
            let selectedOption = $('input[name=payment-option]:checked');
            if (selectedOption.attr("data-module-name") == "paypal_bnpl") {
                event.preventDefault();
                event.stopPropagation();
                document.querySelector('[paypal-wrong-button-message]').style.display = 'block';
            }
        });
    });

    if (typeof BNPL != "undefined") {
        paypalInitBNPLButtons();
    } else {
        document.addEventListener('paypal-after-init-bnpl-button', function (event) {
          paypalInitBNPLButtons();
        })
    }

    function paypalInitBNPLButtons() {
        BNPL.addMarkTo(
          document.querySelector('[data-module-name="paypal_bnpl"]').closest('.payment-option'),
          {
            display: "table-cell"
          }
        );
        BNPL.disableTillConsenting();
        BNPL.hideElementTillPaymentOptionChecked(
            '[data-module-name="paypal_bnpl"]',
            '#payment-confirmation'
        );
        BNPL.showElementIfPaymentOptionChecked(
          '[data-module-name="paypal_bnpl"]',
          '[paypal-bnpl-button-container]'
        );
    }
  </script>
{/block}

