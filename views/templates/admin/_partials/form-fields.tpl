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
{assign var="variant" value=$field.variant|default:false}
{assign var="withColor" value=$withColor|default:false}
{assign var="isModal" value=$isModal|default:false}
{assign var="dynamicField" value=$dynamicField|default:false}

{if $field.type !== 'checkbox' && $field.label}
  <div class="form-group row {[
    'd-none' => $dynamicField && !$dynamicField.value|default:false && ($field.name != $dynamicField.name|default:false),
    'form-group-dynamic' => $dynamicField && ($field.name == $dynamicField.name|default:false)
  ]|classnames}"
       {if $dynamicField.name|default:false}
         group-name="{$dynamicField.name|escape:'htmlall':'UTF-8'}"
       {/if}
  >
    <label class="form-control-label {[
      'form-control-label-check' => $field.type == 'switch',
      'col-3' => strpos($form.id_form, 'pp_installment_') === false,
      'col-2' => strpos($form.id_form, 'pp_installment_') !== false
    ]|classnames}" for="{$field.name|escape:'htmlall':'UTF-8'}">{$field.label|escape:'htmlall':'UTF-8'}</label>

    <div class="{[
      'col-9' => $withColor || $isModal,
      'col-7' => strpos($form.id_form, 'pp_installment_') === false,
      'col-10' => strpos($form.id_form, 'pp_installment_') !== false
    ]|classnames}">
      <div>
{/if}

      {if $field.type === 'text'}
        {* Type text *}
        <input
          type="text"
          name="{$field.name|escape:'htmlall':'UTF-8'}"
          id="{$field.name|escape:'htmlall':'UTF-8'}"
          class="form-control {[
            'form-control-primary' => $variant == 'primary'
          ]|classnames}"
          {if $field.placeholder|default:false}
            placeholder="{$field.placeholder|escape:'htmlall':'UTF-8'}"
          {/if}
          value="{$field.value|default:''|escape:'htmlall':'UTF-8'}"
          {if $field.data_type|default:false}
            data-type="{$field.data_type|escape:'htmlall':'UTF-8'}"
          {/if}
        >
      {elseif $field.type === 'widget-code'}
        <div class="input-group d-flex">
          <input type="text" style="width: initial" readonly class="form-control"  aria-describedby="basic-addon2" id="{$field.name|escape:'htmlall':'UTF-8'}" value="{$field.code|default:''|escape:'htmlall':'UTF-8'}">
          <div class="input-group-append" style="cursor: pointer" onclick="document.getElementById('{if isset($field.name)}{$field.name|escape:'htmlall':'UTF-8'}{/if}').select(); document.execCommand('copy')">
            <span class="input-group-text" id="basic-addon2"><i class="icon-copy"></i></span>
          </div>
        </div>
      {elseif $field.type === 'hidden'}
        <input type="hidden" id="{$field.name|escape:'htmlall':'UTF-8'}" name="{$field.name|escape:'htmlall':'UTF-8'}" value="{$field.value|escape:'htmlall':'UTF-8'}" />
      {elseif $field.type === 'select'}
        {* Type select *}
        <select
          class="form-control custom-select {[
            'custom-select-primary' => $variant == 'primary'
          ]|classnames}"
          name="{$field.name|escape:'htmlall':'UTF-8'}"
          id="{$field.name|escape:'htmlall':'UTF-8'}"
          {if $field.data_type|default:false}
            data-type="{$field.data_type|escape:'htmlall':'UTF-8'}"
          {/if}
          >
          {foreach from=$field.options item=option}
            <option
              value="{$option.value|default:''|escape:'htmlall':'UTF-8'}"
              {if isset($option.value) && isset($field.value) && $option.value == $field.value} selected {/if}
              {if $option.color|default:false}data-color="{$option.color|escape:'htmlall':'UTF-8'}"{/if}
            >{$option.title|default:''|escape:'htmlall':'UTF-8'}</option>
          {/foreach}
        </select>

        {if $withColor}
          {assign var="selectedColor" value=$field.options.0.color|default:'gray'}
          {foreach from=$field.options item=option}
            {if isset($option.value) && isset($field.value) && $option.value == $field.value}
              {$selectedColor = $option.color}
            {/if}
          {/foreach}

          <span class="color-swatch ml-1 {[
            'border' => $selectedColor == '#fff'
          ]|classnames}" style="background:{$selectedColor};"></span>
        {/if}

      {elseif $field.type === 'switch'}

        {* Type switch *}
        <div class="custom-control custom-switch {[
          'custom-switch-secondary' => $variant == 'secondary'
        ]|classnames}">
          <input type="checkbox" class="custom-control-input" id="{$field.name|escape:'htmlall':'UTF-8'}" name="{$field.name|escape:'htmlall':'UTF-8'}" value="1" {if $field.value|default:false}checked{/if} {if $field.disabled|default:false}disabled{/if}>
            {if $field.disabled|default:false}<input type="hidden" name="{$field.name|escape:'htmlall':'UTF-8'}" value="{$field.value|default:''|escape:'htmlall':'UTF-8'}">{/if}
          <label class="custom-control-label form-control-label-check" for="{$field.name|escape:'htmlall':'UTF-8'}">{l s='Enabled' mod='paypal'}</label>
        </div>
      {elseif $field.type === 'checkbox'}

        {* Type checkbox *}
        <div class="col custom-checkbox-wrap">
          <div class="custom-control custom-checkbox form-check-inline">
            <input
              class="custom-control-input"
              type="checkbox"
              id="{$field.name|escape:'htmlall':'UTF-8'}"
              name="{$field.name|escape:'htmlall':'UTF-8'}"
              value="{$field.value|default:''|escape:'htmlall':'UTF-8'}"
              {if $field.checked}checked{/if}>
            <label class="custom-control-label" for="{$field.name|escape:'htmlall':'UTF-8'}">
              <span class="label">
                {$field.label|escape:'htmlall':'UTF-8'}
              </span>
              {if isset($field.image)}
                <img src="{$field.image|escape:'htmlall':'UTF-8'}"  alt="location">
              {/if}

            </label>
          </div>
        </div>

      {/if}

{if $field.type !== 'checkbox' && $field.label}
      </div>
    </div>
  </div>
{/if}
