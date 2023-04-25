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
{assign var="fieldsInstallmentBNPL" value=['PAYPAL_BNPL_PRODUCT_PAGE', 'PAYPAL_BNPL_PAYMENT_STEP_PAGE', 'PAYPAL_BNPL_CART_PAGE', 'PAYPAL_BNPL_CHECKOUT_PAGE']}
{assign var="fieldsInstallment" value=['PAYPAL_INSTALLMENT_PRODUCT_PAGE', 'PAYPAL_INSTALLMENT_HOME_PAGE', 'PAYPAL_INSTALLMENT_CATEGORY_PAGE', 'PAYPAL_INSTALLMENT_CART_PAGE', 'PAYPAL_INSTALLMENT_CHECKOUT_PAGE']}

<form id="{$form.id_form}" class="mt-4">
  {include file="module:paypal/views/templates/admin/_partials/form-fields.tpl" field=$form.fields.PAYPAL_ENABLE_BNPL}

  <div class="form-group">
    <label class="form-control-label form-control-label-check col-lg-3" for="PAYPAL_BNPL">{l s='Active on' mod='paypal'}</label>
    <div class="col-lg-10">
      <div class="row no-gutters">
        {foreach from=$form.fields item=field}
          {if $field.name|in_array:$fieldsInstallmentBNPL}
            {include file="module:paypal/views/templates/admin/_partials/form-fields.tpl" field=$field }
          {/if}
        {/foreach}
      </div>
    </div>
  </div>

  {include file="module:paypal/views/templates/admin/_partials/form-fields.tpl" field=$form.fields.PAYPAL_ENABLE_INSTALLMENT}

  <div class="form-group">
    <label class="form-control-label form-control-label-check col-lg-3" for="PAYPAL_INSTALLMENT">{l s='Active on' mod='paypal'}</label>
    <div class="col-lg-10">
      <div class="row no-gutters">
        {foreach from=$form.fields item=field}
          {if $field.name|in_array:$fieldsInstallment}
            {include file="module:paypal/views/templates/admin/_partials/form-fields.tpl" field=$field }
          {/if}
        {/foreach}
      </div>
    </div>
  </div>

  {include file="module:paypal/views/templates/admin/_partials/form-fields.tpl" field=$form.fields.PAYPAL_ADVANCED_OPTIONS_INSTALLMENT}

  {include file="module:paypal/views/templates/admin/_partials/form-fields.tpl" field=$form.fields.PAYPAL_INSTALLMENT_COLOR}

  <div class="form-group pt-5 mb-0">
    <button class="btn btn-secondary ml-auto" name={$form.submit.name}>{$form.submit.title}</button>
  </div>
</form>
