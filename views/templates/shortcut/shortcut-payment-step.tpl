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

{extends file = "module:paypal/views/templates/shortcut/shortcut-layout.tpl"}

{block name='content'}

  <style>
    [data-container-express-checkout] {
      margin: 10px 0;
      width: 200px;
    }

    @media (max-width: 480px) {
      [paypal-mark-container] {
        display: none !important;
      }
    }

  </style>

  <div data-container-express-checkout data-paypal-source-page="payment-step">
    <form data-paypal-payment-form-cart class="paypal_payment_form" action="{$action_url|escape:'htmlall':'UTF-8'}" method="post" data-ajax="false">
      <input type="hidden" name="express_checkout" value="{$PayPal_payment_type|escape:'htmlall':'UTF-8'}"/>
      <input type="hidden" name="current_shop_url" data-paypal-url-page value="" />
      <input type="hidden" id="source_page" name="source_page" value="cart">
      <input type="hidden" name="isAddAddress" value="1">
    </form>
    <div paypal-button-container paypal-payment-step-button></div>

    <div style="display: none" class="alert alert-danger" paypal-ec-wrong-button-message>
      <div>{l s='Please click on the \'Pay with PayPal\' button' mod='paypal'}</div>
    </div>
  </div>
  <div class="clearfix"></div>
{/block}

{block name='js'}
  <script>
      (function () {
          // Also supports the one-page checkout: its own confirmation button, an express checkout
          // shortcut above the form and a payment list re-rendered by ajax (this script runs again)
          var paymentConfirmationSelector = '#payment-confirmation, #opc-payment-confirmation';

          if (typeof window.paypalPaymentStepGuard === 'undefined') {
              window.paypalPaymentStepGuard = true;
              document.addEventListener('click', function (event) {
                  if (event.target instanceof Element === false
                      || event.target.closest('#payment-confirmation button, #opc-payment-confirmation button') === null) {
                      return;
                  }

                  var selectedOption = document.querySelector('input[name=payment-option]:checked');

                  if (selectedOption && selectedOption.getAttribute('data-module-name') == 'paypal') {
                      event.preventDefault();
                      event.stopPropagation();
                      var message = document.querySelector('[paypal-ec-wrong-button-message]');

                      if (message) {
                          message.style.display = 'block';
                      }
                  }
              }, true);
          }

          document.addEventListener('paypal-after-init-shortcut-button', function () {
              var paypalOption = document.querySelector('[data-module-name="paypal"]');

              if (paypalOption === null) {
                  return;
              }

              Shortcut.addMarkTo(
                paypalOption.closest('.payment-option, .payment__option label'),
                {
                  display: "table-cell"
                }
              );
              Shortcut.disableTillConsenting(document.querySelector('[paypal-payment-step-button]'));
              Shortcut.hideElementTillPaymentOptionChecked(
                  '[data-module-name="paypal"]',
                  paymentConfirmationSelector
              );
              Shortcut.showElementIfPaymentOptionChecked(
                '[data-module-name="paypal"]',
                '[paypal-payment-step-button]'
              );
          }, { once: true });
      })();
  </script>
{/block}

