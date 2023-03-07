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

<!-- Start apm. Module Paypal -->
{include file = "{$psPaypalDir}/views/templates/_partials/javascript.tpl" assign=javascriptBlock}
{block name='head'}
    {$javascriptBlock nofilter}
{/block}

<div>
  <div id="paypal-sepa"></div>
</div>

{assign var=scInitController value=Context::getContext()->link->getModuleLink('paypal', 'ScInit')}
{assign var=validationController value=Context::getContext()->link->getModuleLink('paypal', 'pppValidation')}

<script>
  setTimeout(
      function() {
          var skdNameSpace = '{$sdkNameSpace|escape:'htmlall':'UTF-8'}';

          function waitPaypalSepaSDKIsLoaded() {
              if (window[skdNameSpace] === undefined || typeof SepaButton === 'undefined') {
                  setTimeout(waitPaypalSepaSDKIsLoaded, 200);
                  return;
              }

              var sepaObj = new SepaButton({
                  method: 'sepa',
                  button: '#paypal-sepa',
                  controller: '{$scInitController nofilter}',
                  validationController: '{$validationController nofilter}',
                  paypal: window[skdNameSpace],
                  isMoveButtonAtEnd: PAYPAL_MOVE_BUTTON_AT_END
              });
              sepaObj.initButton();
              sepaObj.hideElementTillPaymentOptionChecked(
                  '[data-module-name="paypal_sepa"]',
                  '#payment-confirmation'
              );
              sepaObj.showElementIfPaymentOptionChecked(
                '[data-module-name="paypal_sepa"]',
                '#paypal-sepa'
              );
              sepaObj.addMarkTo(
                  document.querySelector('input[data-module-name="paypal_sepa"]').closest('.payment-option'),
                  {
                      display: "table-cell"
                  }
              );
          }

          waitPaypalSepaSDKIsLoaded();
      },
      0
  );
</script>

<!-- End apm. Module Paypal -->
