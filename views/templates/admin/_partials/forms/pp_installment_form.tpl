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

{assign var="fieldsInstallmentBNPL" value=['PAYPAL_BNPL_PRODUCT_PAGE', 'PAYPAL_BNPL_PAYMENT_STEP_PAGE', 'PAYPAL_BNPL_CART_PAGE', 'PAYPAL_BNPL_CHECKOUT_PAGE']}
{assign var="fieldsInstallment" value=['PAYPAL_INSTALLMENT_PRODUCT_PAGE', 'PAYPAL_INSTALLMENT_HOME_PAGE', 'PAYPAL_INSTALLMENT_CATEGORY_PAGE', 'PAYPAL_INSTALLMENT_CART_PAGE', 'PAYPAL_INSTALLMENT_CHECKOUT_PAGE']}
{assign var="dynamicField" value=$form.fields.PAYPAL_ENABLE_BNPL|default:false}
{assign var="dynamicFieldBanner" value=$form.fields.PAYPAL_ENABLE_INSTALLMENT|default:false}

{block name='form_content'}
  {if $dynamicField}
    {include file="module:paypal/views/templates/admin/_partials/form-fields.tpl" field=$form.fields.PAYPAL_ENABLE_BNPL dynamicField=$dynamicField}
  {/if}

  <div class="form-group row {[
    'd-none' => $dynamicField && !$dynamicField.value
  ]|classnames}" {if $dynamicField.name|default:false}group-name="{$dynamicField.name}"{/if}>
    <label class="form-control-label form-control-label-check col-2" for="PAYPAL_BNPL">{l s='Active on' mod='paypal'}</label>
    <div class="col-10">
      <div class="row no-gutters">
        {foreach from=$form.fields item=field}
          {if $field.name|in_array:$fieldsInstallmentBNPL}
            {include file="module:paypal/views/templates/admin/_partials/form-fields.tpl" field=$field}
          {/if}
        {/foreach}
      </div>
    </div>
  </div>

  {include file="module:paypal/views/templates/admin/_partials/form-fields.tpl" field=$form.fields.PAYPAL_ENABLE_INSTALLMENT dynamicField=$dynamicFieldBanner}

  <div class="form-group row {[
    'd-none' => $dynamicFieldBanner && !$dynamicFieldBanner.value
  ]|classnames}" {if $dynamicFieldBanner.name|default:false}group-name="{$dynamicFieldBanner.name}"{/if}>
    <label class="form-control-label form-control-label-check col-2" for="PAYPAL_INSTALLMENT">{l s='Active on' mod='paypal'}</label>
    <div class="col-10 pr-0">
      <div class="row no-gutters">
        {foreach from=$form.fields item=field}
          {if $field.name|in_array:$fieldsInstallment}
            {include file="module:paypal/views/templates/admin/_partials/form-fields.tpl" field=$field}
          {/if}
        {/foreach}
      </div>
    </div>
  </div>

    <div class="{[
    'd-none' => $dynamicFieldBanner && !$dynamicFieldBanner.value
    ]|classnames}" {if $dynamicFieldBanner.name|default:false}group-name="{$dynamicFieldBanner.name}"{/if}>
        {include file="module:paypal/views/templates/admin/_partials/form-fields.tpl" field=$form.fields.PAYPAL_ADVANCED_OPTIONS_INSTALLMENT dynamicField=$form.fields.PAYPAL_ADVANCED_OPTIONS_INSTALLMENT}

        {include file="module:paypal/views/templates/admin/_partials/form-fields.tpl" field=$form.fields.PAYPAL_INSTALLMENT_COLOR withColor=true dynamicField=$form.fields.PAYPAL_ADVANCED_OPTIONS_INSTALLMENT}

        {if isset($form.fields.widget_code)}
            {include file="module:paypal/views/templates/admin/_partials/form-fields.tpl" field=$form.fields.widget_code dynamicField=$form.fields.PAYPAL_ADVANCED_OPTIONS_INSTALLMENT}
        {/if}
    </div>

{/block}
