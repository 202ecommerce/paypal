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

{assign var="fieldsExpressCheckoutShortcut" value=['PAYPAL_EXPRESS_CHECKOUT_SHORTCUT', 'PAYPAL_EXPRESS_CHECKOUT_SHORTCUT_CART', 'PAYPAL_EXPRESS_CHECKOUT_SHORTCUT_SIGNUP']}

{block name='form_content'}

    {foreach from=$form.fields item=field}
        {if $field.name|in_array:['PAYPAL_API_INTENT', 'PAYPAL_EXPRESS_CHECKOUT_IN_CONTEXT']}
            {include file="module:paypal/views/templates/admin/_partials/form-fields.tpl" field=$field }
        {/if}
    {/foreach}

  <div class="form-group row">
    <label class="form-control-label form-control-label-check col-3" for="PAYPAL_EXPRESS_CHECKOUT_SHORTCUT_test">{l s='Active on' mod='paypal'}</label>
    <div class="col-9">
      <div class="row no-gutters">
          {foreach from=$form.fields item=field}
              {if $field.name|in_array:$fieldsExpressCheckoutShortcut}
                  {include file="module:paypal/views/templates/admin/_partials/form-fields.tpl" field=$field }
              {/if}
          {/foreach}
      </div>
    </div>
  </div>

    {foreach from=$form.fields item=field}
        {if $field.name|in_array:['PAYPAL_CONFIG_BRAND', 'PAYPAL_PUI_CUSTOMER_SERVICE_INSTRUCTIONS', 'PAYPAL_API_ADVANTAGES', 'PAYPAL_MOVE_BUTTON_AT_END']}
            {if $field.name == 'PAYPAL_MOVE_BUTTON_AT_END' && $isShowModalConfiguration|default:false}
                {continue}
            {/if}
            {include file="module:paypal/views/templates/admin/_partials/form-fields.tpl" field=$field }
        {/if}
    {/foreach}

{/block}
