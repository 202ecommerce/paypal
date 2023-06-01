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
         group-name="{$dynamicField.name}"
       {/if}
  >
    <label class="form-control-label {[
      'form-control-label-check' => $field.type == 'switch',
      'col-3' => $form.id_form !== 'pp_installment_form',
      'col-2' => $form.id_form === 'pp_installment_form'
    ]|classnames}" for="{$field.name}">{$field.label}</label>

    <div class="{[
      'col-9' => $withColor || $isModal,
      'col-7' => $form.id_form !== 'pp_installment_form',
      'col-10' => $form.id_form === 'pp_installment_form'
    ]|classnames}">
      <div>
{/if}

      {if $field.type === 'text'}
        {* Type text *}
        <input
          type="text"
          name="{$field.name}"
          id="{$field.name}"
          class="form-control {[
            'form-control-primary' => $variant == 'primary'
          ]|classnames}"
          {if $field.placeholder|default:false}
            placeholder="{$field.placeholder}"
          {/if}
          value="{$field.value|default:''}"
          {if $field.data_type|default:false}
            data-type="{$field.data_type}"
          {/if}
        >
      {elseif $field.type === 'widget-code'}
        <div class="input-group">
          <input type="text" readonly class="form-control"  aria-describedby="basic-addon2" id="{$field.name}" value="{$field.code|default:''}">
          <div class="input-group-append" style="cursor: pointer" onclick="document.getElementById('{if isset($field.name)}{$field.name}{/if}').select(); document.execCommand('copy')">
            <span class="input-group-text" id="basic-addon2"><i class="icon-copy"></i></span>
          </div>
        </div>
      {elseif $field.type === 'select'}
        {* Type select *}
        <select
          class="form-control custom-select {[
            'custom-select-primary' => $variant == 'primary'
          ]|classnames}"
          name="{$field.name}"
          id="{$field.name}"
          {if $field.data_type|default:false}
            data-type="{$field.data_type}"
          {/if}
          >
          {foreach from=$field.options item=option}
            <option
              value="{$option.value|default:''}"
              {if isset($option.value) && isset($field.value) && $option.value == $field.value} selected {/if}
              {if $option.color|default:false}data-color="{$option.color}"{/if}
            >{$option.title|default:''}</option>
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
          <input type="checkbox" class="custom-control-input" id="{$field.name}" name="{$field.name}" value="1" {if $field.value|default:false}checked{/if}>
          <label class="custom-control-label form-control-label-check" for="{$field.name}">{l s='Enabled' mod='paypal'}</label>
        </div>
      {elseif $field.type === 'checkbox'}

        {* Type checkbox *}
        <div class="col custom-checkbox-wrap">
          <div class="custom-control custom-checkbox form-check-inline">
            <input
              class="custom-control-input"
              type="checkbox"
              id="{$field.name}"
              name="{$field.name}"
              value="{$field.value|default:''}"
              {if $field.checked}checked{/if}>
            <label class="custom-control-label" for="{$field.name}">
              <span class="label">
                {$field.label}
              </span>
              {if isset($field.image)}
                <img src="{$field.image}"  alt="location">
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
