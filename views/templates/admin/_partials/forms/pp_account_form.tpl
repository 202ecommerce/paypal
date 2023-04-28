{*
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
{extends file="module:paypal/views/templates/admin/_partials/forms/form.tpl"}
{assign var="isModal" value=$isModal|default:false}

{block name='form_content'}
  {foreach from=$form.fields item=field}
    {if $field.type == 'variable-set'}
      {include file="module:paypal/views/templates/admin/_partials/form-fields.tpl" field=[
        'type' => 'select',
        'name' => 'mode',
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
      {if !$field.set.accountConfigured }
        <div class="form-group row">
          <div class="offset-3 {[
            'col-7' => !$isModal,
            'col-9' => $isModal
          ]|classnames}">
            <a href="{$field.set.urlOnboarding}" class="btn btn-secondary btn-block" target="_blank">
              <span class="icon mr-2">
                <i class="material-icons-outlined">account_circle</i>
              </span>
              <span>
                {l s='Connect your PayPal account' mod='paypal'}
              </span>
            </a>
          </div>
        </div>
      {else}
        {include file="module:paypal/views/templates/admin/_partials/form-fields.tpl" field=[
          'type' => 'text',
          'name' => 'paypal_ec_clientid',
          'label' => "{l s='Client\’s ID' mod='paypal'}",
          'variant' => 'primary'
        ]}
        {include file="module:paypal/views/templates/admin/_partials/form-fields.tpl" field=[
          'type' => 'text',
          'name' => 'paypal_ec_secret',
          'label' => "{l s='Client\’s secret' mod='paypal'}",
          'variant' => 'primary'
        ]}
      {/if}
    {/if}
  {/foreach}
{/block}

{block name='form_footer_buttons'}
  {if $isModal}
    <button data-btn-action="prev" class="btn btn-secondary d-none">{l s='Back' mod='paypal'}</button>
  {/if}
  <button data-btn-action="next" class="btn btn-secondary ml-auto" name={$form.submit.name}>{$form.submit.title}</button>
{/block}
