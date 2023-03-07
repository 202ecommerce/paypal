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

{if isset($methodCollection)}
    {foreach from=$methodCollection item=method}
        <div>
          <div id="paypal-apm-{$method|escape:'htmlall':'UTF-8'}"></div>
        </div>
    {/foreach}
{/if}

{assign var=scInitController value=Context::getContext()->link->getModuleLink('paypal', 'ScInit')}
{assign var=validationController value=Context::getContext()->link->getModuleLink('paypal', 'pppValidation')}

<script>
  setTimeout(
      function() {
          var apmMethodCollection = {$methodCollection|json_encode nofilter};
          var skdNameSpace = '{$sdkNameSpace|escape:'htmlall':'UTF-8'}';

          function waitPaypalApmSDKIsLoaded() {
              if (window[skdNameSpace] === undefined || typeof ApmButton === 'undefined') {
                  setTimeout(waitPaypalApmSDKIsLoaded, 200);
                  return;
              }

              for (var key in apmMethodCollection) {
                  var method = apmMethodCollection[key];

                  var apmObj = new ApmButton({
                      method: method,
                      button: '#paypal-apm-'+method,
                      controller: '{$scInitController nofilter}',
                      validationController: '{$validationController nofilter}',
                      paypal: window[skdNameSpace],
                      isMoveButtonAtEnd: PAYPAL_MOVE_BUTTON_AT_END
                  });
                  apmObj.initButton();
                  apmObj.hideElementTillPaymentOptionChecked(
                      '[data-module-name="paypal_' + method + '"]',
                      '#payment-confirmation'
                  );
                  apmObj.showElementIfPaymentOptionChecked(
                    '[data-module-name="paypal_' + method + '"]',
                    '#paypal-apm-'+method
                  );
                  apmObj.addMarkTo(
                      document.querySelector('input[data-module-name="paypal_'+method+'"]').closest('.payment-option'),
                      {
                          display: "table-cell"
                      }
                  );
              }
          }

          waitPaypalApmSDKIsLoaded();
      },
      0
  );
</script>

<!-- End apm. Module Paypal -->
