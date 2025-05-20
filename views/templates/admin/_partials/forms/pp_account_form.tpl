{*
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
{extends file="./form.tpl"}

{block name='form_content'}
    {assign var="isModal" value=$isModal|default:false}
    {foreach from=$form.fields key=fieldKey item=field}
        {if $fieldKey == 'account_form'}
            {assign var="isShowCredentials" value=in_array($field.set.country_iso, ['MX', 'BR', 'JP', 'IN'])}
            {assign var='selectMode' value=[
            'type' => 'select',
            'name' => 'mode',
            'value' => $field.set.mode|default:'',
            'options' => [
            [
            'value' => 'LIVE',
            'title' => "{l s='Production' mod='paypal'}"
            ],
            [
            'value' => 'SANDBOX',
            'title' => "{l s='Sandbox' mod='paypal'}"
            ]
            ],
            'label' => "{l s='Mode' mod='paypal'}",
            'variant' => 'primary'
            ]}
            {assign var='clientIdLive' value=[
            'type' => 'text',
            'name' => 'paypal_clientid_live',
            'label' => "{l s='Client\’s ID' mod='paypal'}",
            'variant' => 'primary',
            'value' => $field.set.paypal_clientid_live|default:''
            ]}
            {assign var='clientSecretLive' value=[
            'type' => 'text',
            'name' => 'paypal_secret_live',
            'label' => "{l s='Client\’s secret' mod='paypal'}",
            'variant' => 'primary',
            'value' => $field.set.paypal_secret_live|default:''
            ]}
            {assign var='merchantIdLive' value=[
            'type' => 'text',
            'name' => 'merchant_id_live',
            'value' => $field.set.merchant_id_live|default:'',
            'label' => "{l s='Merchant ID' mod='paypal'}",
            'variant' => 'primary'
            ]}
            {assign var='clientIdSandbox' value=[
            'type' => 'text',
            'name' => 'paypal_clientid_sandbox',
            'label' => "{l s='Client\’s ID' mod='paypal'}",
            'variant' => 'primary',
            'value' => $field.set.paypal_clientid_sandbox|default:''
            ]}
            {assign var='clientSecretSandbox' value=[
            'type' => 'text',
            'name' => 'paypal_secret_sandbox',
            'label' => "{l s='Client\’s secret' mod='paypal'}",
            'variant' => 'primary',
            'value' => $field.set.paypal_secret_sandbox|default:''
            ]}
            {assign var='merchantIdSandbox' value=[
            'type' => 'text',
            'name' => 'merchant_id_sandbox',
            'value' => $field.set.merchant_id_sandbox|default:'',
            'label' => "{l s='Merchant ID' mod='paypal'}",
            'variant' => 'primary'
            ]}

          <input type="hidden" name="is_configured_live" value="{$field.set.is_configured_live}">
          <input type="hidden" name="is_configured_sandbox" value="{$field.set.is_configured_sandbox}">

            {include file="../form-fields.tpl" field=$selectMode}

          <div credential-section>
            <div live-section style="display: none">
                {include file="../form-fields.tpl" field=$clientIdLive}
                {include file="../form-fields.tpl" field=$clientSecretLive}
                {include file="../form-fields.tpl" field=$merchantIdLive}
            </div>

            <div sandbox-section style="display: none">
                {include file="../form-fields.tpl" field=$clientIdSandbox}
                {include file="../form-fields.tpl" field=$clientSecretSandbox}
                {include file="../form-fields.tpl" field=$merchantIdSandbox}
            </div>
          </div>


          <div onboarding-button-section>

            <div live-section style="display: none">

              <div class="form-group row">
                <div class="offset-3 {[
                'col-7' => !$isModal,
                'col-9' => $isModal
                ]|classnames}">
                  <a
                    href="{$field.set.urlOnboarding_live}"
                    class="btn btn-secondary btn-block"
                    target="_blank"
                    data-paypal-button
                    data-paypal-onboard-complete="onboardCallback"
                  >
              <span class="icon mr-2">
                <i class="material-icons-outlined">account_circle</i>
              </span>
                    <span>
                {l s='Connect or create your PayPal account' mod='paypal'}
              </span>
                  </a>
                </div>
              </div>
            </div>

            <div sandbox-section style="display: none">
              <div class="form-group row">
                <div class="offset-3 {[
                'col-7' => !$isModal,
                'col-9' => $isModal
                ]|classnames}">
                  <a
                    href="{$field.set.urlOnboarding_sandbox}"
                    class="btn btn-secondary btn-block"
                    target="_blank"
                    data-paypal-button
                    data-paypal-onboard-complete="onboardCallback"
                  >
              <span class="icon mr-2">
                <i class="material-icons-outlined">account_circle</i>
              </span>
                    <span>
                {l s='Connect or create your PayPal account' mod='paypal'}
              </span>
                  </a>
                </div>
              </div>
            </div>

            <div logout-section style="display: none">

              <div class="form-group row">
                {if !$isModal}
                  <label class="form-control-label col-3">
                      {include
                      file="../icon-status.tpl"
                      isSuccess=true
                      }
                  </label>
                {/if}

                <div class="{[
                'col-7' => !$isModal,
                'col-9' => $isModal,
                'offset-3' => $isModal
                ]|classnames}">
              <span class="btn btn-secondary btn-block" logout-button>
              <span class="icon mr-2">
                <i class="material-icons-outlined">account_circle</i>
              </span>
                <span>
                {l s='Logout' mod='paypal'}
              </span>
              </span>
                </div>
              </div>

                {if $isModal}
                  <div class="form-group row">
                    <div class="offset-3 {[
                    'col-7' => !$isModal,
                    'col-9' => $isModal
                    ]|classnames}">
                      <div merchant-label-sandbox class="alert alert-warning mt-0" style="display: none">
                          {l s='You are connected with the account:' mod='paypal'}
                        <b merchant-id></b>
                      </div>
                      <div merchant-label-live class="alert alert-warning mt-0" style="display: none">
                          {l s='You are connected with the account:' mod='paypal'}
                        <b merchant-id></b>
                      </div>
                    </div>
                  </div>
                {/if}

            </div>

          </div>

        {/if}
    {/foreach}

  <script>
    function onboardCallback(authCode, sharedId) {
      document.dispatchEvent(
        (new CustomEvent(
          'generateCredentials',
          {
            bubbles: true,
            detail: {
              authCode: authCode,
              sharedId: sharedId,
            }
          }
        ))
      );
    }

    window.addEventListener('load', function() {

      var event = new CustomEvent(
        '{if $isShowCredentials}updateCredentials{else}updateButtonSection{/if}',
        {
          bubbles: true
        }
      );
      var mode = document.querySelector('#pp_account_form [name="mode"]');

      mode.addEventListener('change', function() {
        document.dispatchEvent(event);
      });
      document.dispatchEvent(event);

    });



  </script>
  <script src="https://www.paypal.com/webapps/merchantboarding/js/lib/lightbox/partner.js"></script>

{/block}

{block name='form_footer_buttons'}
  <button save-form class="btn btn-secondary ml-auto" name={$form.submit.name|escape:'htmlall':'UTF-8'}>{$form.submit.title|escape:'htmlall':'UTF-8'}</button>
{/block}
