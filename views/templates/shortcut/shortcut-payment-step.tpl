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

{extends file = "module:paypal/views/templates/shortcut/shortcut-layout.tpl"}

{block name='content'}

  <style>
    [data-container-express-checkout] {
      margin: 10px 0;
      display: flex;
      flex-direction: column;
      align-items: flex-start;
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
    <div paypal-button-container></div>

    <div style="display: none" class="alert alert-danger" paypal-ec-wrong-button-message>
      <div>{l s='Please click on the \'Pay with PayPal\' button' mod='paypal'}</div>
    </div>
  </div>
  <div class="clearfix"></div>
{/block}

{block name='js'}
  <script>
      document.addEventListener('DOMContentLoaded', function() {
          document.querySelector('#payment-confirmation button').addEventListener('click', function(event) {
              let selectedOption = $('input[name=payment-option]:checked');
              if (selectedOption.attr("data-module-name") == "paypal") {
                  event.preventDefault();
                  event.stopPropagation();
                  document.querySelector('[paypal-ec-wrong-button-message]').style.display = 'block';
              }
          });
      });

      if (typeof Shortcut != "undefined") {
          Shortcut.addMarkTo(
            document.querySelector('[data-module-name="paypal"]').closest('.payment-option'),
            {
              display: "table-cell"
            }
          );
          Shortcut.disableTillConsenting();
          Shortcut.hideElementTillPaymentOptionChecked(
              '[data-module-name="paypal"]',
              '#payment-confirmation'
          );
          Shortcut.showElementIfPaymentOptionChecked(
            '[data-module-name="paypal"]',
            '[paypal-button-container]'
          );
      } else {
          document.addEventListener('paypal-after-init-shortcut-button', function (event) {
              Shortcut.addMarkTo(
                document.querySelector('[data-module-name="paypal"]').closest('.payment-option'),
                {
                  display: "table-cell"
                }
              );
              Shortcut.disableTillConsenting();
              Shortcut.hideElementTillPaymentOptionChecked(
                  '[data-module-name="paypal"]',
                  '#payment-confirmation'
              );
              Shortcut.showElementIfPaymentOptionChecked(
                '[data-module-name="paypal"]',
                '[paypal-button-container]'
              );
          })
      }
  </script>
{/block}

