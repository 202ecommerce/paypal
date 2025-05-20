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
 {extends file="../forms/form.tpl"}


 {block name='form_content'}
  {assign var="fieldsInstallmentBNPL" value=['PAYPAL_BNPL_PRODUCT_PAGE', 'PAYPAL_BNPL_PAYMENT_STEP_PAGE', 'PAYPAL_BNPL_CART_PAGE', 'PAYPAL_BNPL_CHECKOUT_PAGE']}
  {assign var="dynamicField" value=$form.fields.PAYPAL_ENABLE_BNPL|default:false}
   {if $dynamicField}
     {include file="../form-fields.tpl" field=$form.fields.PAYPAL_ENABLE_BNPL dynamicField=$dynamicField}
   {/if}

   <div class="form-group row {[
     'd-none' => $dynamicField && !$dynamicField.value
   ]|classnames}" {if $dynamicField.name|default:false}group-name="{$dynamicField.name|escape:'htmlall':'UTF-8'}"{/if}>
     <label class="form-control-label form-control-label-check col-2" for="PAYPAL_BNPL">{l s='Active on' mod='paypal'}</label>
     <div class="col-10">
       <div class="row no-gutters">
         {foreach from=$form.fields item=field}
           {if $field.name|in_array:$fieldsInstallmentBNPL}
             {include file="../form-fields.tpl" field=$field}
           {/if}
         {/foreach}
       </div>
     </div>
   </div>
 {/block}
