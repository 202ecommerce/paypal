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

<!-- Start views/templates/acdc/payment-option.tpl. Module Paypal -->

<style>
  .pp-input {
    border: solid;
    border-width: thin;
    height: 30px;
    padding: 0 10px;
  }

  .pp-flex {
    display: flex;
  }

  .pp-space-between {
    justify-content: space-between;
  }

  .pp-center {
    justify-content: center;
  }

  .pp-field-wrapper {
    padding: 10px;
  }

  .pp-field-wrapper label {
    padding: 0 0 10px 0;
    font-weight: bold;
  }

  .pp-flex-direction-column {
    flex-direction: column;
  }

  [paypal-acdc-wrapper] {
    max-width: 300px;
  }

  .pp-padding-1 {
    padding: 10px
  }
</style>

{assign var=scInitController value=Context::getContext()->link->getModuleLink('paypal', 'ScInit')}
{assign var=validationController value=Context::getContext()->link->getModuleLink('paypal', 'pppValidation')}

{include file = "{$psPaypalDir}/views/templates/_partials/javascript.tpl" assign=javascriptBlock}
{block name='head'}
    {$javascriptBlock nofilter}
{/block}

<div paypal-acdc-wrapper class="pp-flex pp-flex-direction-column">

  <!-- Advanced credit and debit card payments form -->
  <div paypal-acdc-card-wrapper class="pp-flex pp-center">
    <form id="card-form" class="pp-flex pp-flex-direction-column">

      <div class="pp-field-wrapper">
        <label for="card-number">{l s='Card Number' mod='paypal'}</label>
        <div id="card-number" class="pp-input"></div>
      </div>

      <div class="pp-flex pp-space-between">
        <div class="pp-field-wrapper">
          <label for="expiration-date">{l s='Expiration Date' mod='paypal'}</label>
          <div id="expiration-date" class="pp-input"></div>
        </div>

        <div class="pp-field-wrapper">
          <label for="cvv">{l s='CVV' mod='paypal'}</label>
          <div id="cvv" class="pp-input"></div>
        </div>
      </div>

      <div class="pp-padding-1">
        <button paypal-acdc-form-button class="btn btn-primary" style="width: 300px">{l s='Pay' mod='paypal'}</button>
      </div>

      <div paypal-acdc-card-error>

      </div>
    </form>
  </div>

</div>



<script>
    function waitPaypalAcdcSDKIsLoaded() {
        if (typeof totPaypalAcdcSdk === 'undefined' || typeof ACDC === 'undefined') {
            setTimeout(waitPaypalAcdcSDKIsLoaded, 200);

            return;
        }

        var messages = new Object();
        messages['INVALID_REQUEST'] = '{l s='There was a problem with your request' mod='paypal'}';
        messages['CVV_IS_EMPTY'] = '{l s='Please enter a valid cvv' mod='paypal'}';
        messages['NUMBER_IS_EMPTY'] = '{l s='Please enter a valid number' mod='paypal'}';
        messages['DATE_IS_EMPTY'] = '{l s='Please enter a valid date' mod='paypal'}';
        messages['3DS_FAILED'] = '{l s='3DS verification is failed' mod='paypal'}';
        acdcObj = new ACDC({
            controller: '{$scInitController nofilter}',
            validationController: '{$validationController nofilter}',
            messages: messages,
            buttonForm: document.querySelector('[paypal-acdc-form-button]'),
            isMoveButtonAtEnd: PAYPAL_MOVE_BUTTON_AT_END
        });
        acdcObj.initHostedFields();
        acdcObj.hideElementTillPaymentOptionChecked(
            '[data-module-name="paypal_acdc"]',
            '#payment-confirmation'
        );
        acdcObj.showElementIfPaymentOptionChecked(
          '[data-module-name="paypal_acdc"]',
          '[paypal-acdc-form-button]'
        );
        acdcObj.addMarkTo(
          document.querySelector('[data-module-name="paypal_acdc"]').closest('.payment-option'),
          {
            display: "table-cell"
          }
        );
    }

    waitPaypalAcdcSDKIsLoaded();
</script>

<!-- End views/templates/acdc/payment-option.tpl. Module Paypal -->
