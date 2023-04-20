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
<div>
  {if $field.type === 'text'}
    {* Type text *}
    <input type="text" name="{$field.name}" id="{$field.name}" class="form-control" placeholder="{l s='Placeholder' mod='paypal'}">

  {elseif $field.type === 'select'}
    {* Type select *}
    <select class="form-control custom-select {[
      'custom-select-primary' => $field.name|in_array:['PAYPAL_API_INTENT', 'PAYPAL_EXPRESS_CHECKOUT_IN_CONTEXT']
    ]|classnames}" name="{$field.name}" id="{$field.name}">
      {foreach from=$field.options item=option}
        <option {if $option.value|default:false}value="{$option.value}"{/if}>{$option.title}</option>
      {/foreach}
    </select>

  {elseif $field.type === 'switch'}

    {* Type switch *}
    <div class="custom-control custom-switch {[
      'custom-switch-secondary' => $field.name|in_array:['PAYPAL_API_ADVANTAGES', 'PAYPAL_ENABLE_WEBHOOK']
    ]|classnames}">
      <input type="checkbox" class="custom-control-input" id="{$field.name}" name="{$field.name}" value="1" {if $field.value|default:false}checked{/if}>
      <label class="custom-control-label form-control-label-check" for="{$field.name}">{l s='Enabled' mod='paypal'}</label>
    </div>

  {/if}
</div>

<div class="text-muted small mt-1">{l s='Placeholder' mod='paypal'}</div>

{* to do: show hint *}

{* {if $field.hint|default:false}
  <div class="small">{$field.hint}</div>
{/if} *}
