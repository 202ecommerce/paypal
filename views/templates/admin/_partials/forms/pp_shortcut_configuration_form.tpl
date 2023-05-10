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
{assign var="fieldsButtonConfiguration" value=['PAYPAL_EXPRESS_CHECKOUT_SHORTCUT_STYLE_COLOR_CART', 'PAYPAL_EXPRESS_CHECKOUT_SHORTCUT_STYLE_SHAPE_CART', 'PAYPAL_EXPRESS_CHECKOUT_SHORTCUT_STYLE_WIDTH_CART', 'PAYPAL_EXPRESS_CHECKOUT_SHORTCUT_STYLE_HEIGHT_CART', 'PAYPAL_EXPRESS_CHECKOUT_SHORTCUT_STYLE_LABEL_CART']}
{assign var="dynamicField" value=$form.fields.PAYPAL_EXPRESS_CHECKOUT_CUSTOMIZE_SHORTCUT_STYLE}

{block name='form_content'}
  {foreach from=$form.fields item=field}
    {if $field.name|default:false}
      {if !$field.name|in_array:$fieldsButtonConfiguration}
        {include file="module:paypal/views/templates/admin/_partials/form-fields.tpl" field=$field dynamicField=$dynamicField}
      {/if}
    {/if}
  {/foreach}

  <div customize-style-shortcut-container>
    <div setting-section>

      {if isset($errors) && false === empty($errors)}
        {foreach from=$errors item=error}
          <div class="alert alert-danger">
            {$error nofilter}
          </div>
        {/foreach}
      {/if}

      <div class="form-group row ">
        <div class="col-7 offset-3">
          <div preview-section class="invisible" style="position: relative">
            <div button-container></div>
            <div class="overlay"></div>
          </div>
        </div>
      </div>

      <div configuration-section class="hidden">
        {include file="module:paypal/views/templates/admin/_partials/form-fields.tpl" field=$form.fields.PAYPAL_EXPRESS_CHECKOUT_SHORTCUT_STYLE_COLOR_CART dynamicField=$dynamicField}
        {include file="module:paypal/views/templates/admin/_partials/form-fields.tpl" field=$form.fields.PAYPAL_EXPRESS_CHECKOUT_SHORTCUT_STYLE_SHAPE_CART dynamicField=$dynamicField}

        <div class="form-group row {[
          'd-none' => $dynamicField && !$dynamicField.value|default:false
        ]|classnames}">
          <label class="form-control-label col-3" for="PAYPAL_EXPRESS_CHECKOUT_SHORTCUT_STYLE_HEIGHT_CART">{$form.fields.PAYPAL_EXPRESS_CHECKOUT_SHORTCUT_STYLE_HEIGHT_CART.label}</label>
          <div class="{[
            'col-7' => !$isModal,
            'col-9' => $isModal
          ]|classnames}">
            <div class="row" chain-input-container>
              <div class="col col-6 pr-2">
                <input
                  type="text"
                  name="{$form.fields.PAYPAL_EXPRESS_CHECKOUT_SHORTCUT_STYLE_WIDTH_CART.name}"
                  id="{$form.fields.PAYPAL_EXPRESS_CHECKOUT_SHORTCUT_STYLE_WIDTH_CART.name}"
                  class="form-control"
                  placeholder="{l s='Placeholder' mod='paypal'}"
                  value="{$form.fields.PAYPAL_EXPRESS_CHECKOUT_SHORTCUT_STYLE_WIDTH_CART.value|default:''}"
                  data-type="width"
                  data-msg-error="{$form.fields.PAYPAL_EXPRESS_CHECKOUT_SHORTCUT_STYLE_WIDTH_CART.message_error}"
                >
                <div class="text-muted small mt-1">Placeholder</div>
              </div>
              <div class="col col-6 pl-2">
                <input
                  type="text"
                  name="{$form.fields.PAYPAL_EXPRESS_CHECKOUT_SHORTCUT_STYLE_HEIGHT_CART.name}"
                  id="{$form.fields.PAYPAL_EXPRESS_CHECKOUT_SHORTCUT_STYLE_HEIGHT_CART.name}"
                  class="form-control"
                  placeholder="{l s='Placeholder' mod='paypal'}"
                  value="{$form.fields.PAYPAL_EXPRESS_CHECKOUT_SHORTCUT_STYLE_HEIGHT_CART.value|default:''}"
                  data-type="height"
                  data-msg-error="{$form.fields.PAYPAL_EXPRESS_CHECKOUT_SHORTCUT_STYLE_HEIGHT_CART.message_error}"
                >
                <div class="text-muted small mt-1">Placeholder</div>
              </div>
              <div class="col col-12" msg-container></div>
            </div>
          </div>
        </div>

        {include file="module:paypal/views/templates/admin/_partials/form-fields.tpl" field=$form.fields.PAYPAL_EXPRESS_CHECKOUT_SHORTCUT_STYLE_LABEL_CART dynamicField=$dynamicField}
      </div>
    </div>
  </div>
{/block}


