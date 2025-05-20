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
{extends file=$moduleFullDir|cat:"/views/templates/admin/_partials/forms/form.tpl"}
{assign var="fieldsButtonConfiguration" value=['PAYPAL_EXPRESS_CHECKOUT_SHORTCUT_STYLE_COLOR_CART', 'PAYPAL_EXPRESS_CHECKOUT_SHORTCUT_STYLE_SHAPE_CART', 'PAYPAL_EXPRESS_CHECKOUT_SHORTCUT_STYLE_WIDTH_CART', 'PAYPAL_EXPRESS_CHECKOUT_SHORTCUT_STYLE_HEIGHT_CART', 'PAYPAL_EXPRESS_CHECKOUT_SHORTCUT_STYLE_LABEL_CART']}
{assign var="dynamicField" value=$form.fields.PAYPAL_EXPRESS_CHECKOUT_CUSTOMIZE_SHORTCUT_STYLE}

{block name='form_content'}
  {assign var="fieldsButtonConfiguration" value=['PAYPAL_EXPRESS_CHECKOUT_SHORTCUT_STYLE_COLOR_CART', 'PAYPAL_EXPRESS_CHECKOUT_SHORTCUT_STYLE_SHAPE_CART', 'PAYPAL_EXPRESS_CHECKOUT_SHORTCUT_STYLE_WIDTH_CART', 'PAYPAL_EXPRESS_CHECKOUT_SHORTCUT_STYLE_HEIGHT_CART', 'PAYPAL_EXPRESS_CHECKOUT_SHORTCUT_STYLE_LABEL_CART']}
  {assign var="dynamicField" value=$form.fields.PAYPAL_EXPRESS_CHECKOUT_CUSTOMIZE_SHORTCUT_STYLE}
    {if isset($form.fields.PAYPAL_EXPRESS_CHECKOUT_CUSTOMIZE_SHORTCUT_STYLE)}
        {include file="../form-fields.tpl" field=$form.fields.PAYPAL_EXPRESS_CHECKOUT_CUSTOMIZE_SHORTCUT_STYLE dynamicField=$dynamicField}
    {/if}

    <div group-name="{$dynamicField.name|escape:'htmlall':'UTF-8'}" {if !$form.fields.PAYPAL_EXPRESS_CHECKOUT_CUSTOMIZE_SHORTCUT_STYLE.value|default:false}class="d-none"{/if}>
        {if isset($form.fields.PAYPAL_EXPRESS_CHECKOUT_DISPLAY_MODE_CART)}
          <div class="h6">{l s='Cart page' mod='paypal'}</div>
          <hr>
            {include file="../shortcut_configuration_section.tpl"
              displayMode=$form.fields.PAYPAL_EXPRESS_CHECKOUT_DISPLAY_MODE_CART
              hooks=$form.fields.PAYPAL_EXPRESS_CHECKOUT_SHORTCUT_HOOK_CART
              widget_type='cart'
              color=$form.fields.PAYPAL_EXPRESS_CHECKOUT_SHORTCUT_STYLE_COLOR_CART
              shape=$form.fields.PAYPAL_EXPRESS_CHECKOUT_SHORTCUT_STYLE_SHAPE_CART
              height=$form.fields.PAYPAL_EXPRESS_CHECKOUT_SHORTCUT_STYLE_HEIGHT_CART
              width=$form.fields.PAYPAL_EXPRESS_CHECKOUT_SHORTCUT_STYLE_WIDTH_CART
              label=$form.fields.PAYPAL_EXPRESS_CHECKOUT_SHORTCUT_STYLE_LABEL_CART
              preview=$form.fields.cart_shortcut_preview.set.shortcut|default:''
            }
        {/if}

        {if isset($form.fields.PAYPAL_EXPRESS_CHECKOUT_DISPLAY_MODE_PRODUCT)}
          <div class="h6">{l s='Product page' mod='paypal'}</div>
          <hr>
            {include file="../shortcut_configuration_section.tpl"
              displayMode=$form.fields.PAYPAL_EXPRESS_CHECKOUT_DISPLAY_MODE_PRODUCT
              hooks=$form.fields.PAYPAL_EXPRESS_CHECKOUT_SHORTCUT_HOOK_PRODUCT
              widget_type='product'
              color=$form.fields.PAYPAL_EXPRESS_CHECKOUT_SHORTCUT_STYLE_COLOR_PRODUCT
              shape=$form.fields.PAYPAL_EXPRESS_CHECKOUT_SHORTCUT_STYLE_SHAPE_PRODUCT
              height=$form.fields.PAYPAL_EXPRESS_CHECKOUT_SHORTCUT_STYLE_HEIGHT_PRODUCT
              width=$form.fields.PAYPAL_EXPRESS_CHECKOUT_SHORTCUT_STYLE_WIDTH_PRODUCT
              label=$form.fields.PAYPAL_EXPRESS_CHECKOUT_SHORTCUT_STYLE_LABEL_PRODUCT
              preview=$form.fields.product_shortcut_preview.set.shortcut|default:''
            }
        {/if}

        {if isset($form.fields.PAYPAL_EXPRESS_CHECKOUT_DISPLAY_MODE_SIGNUP)}
          <div class="h6">{l s='Signup page' mod='paypal'}</div>
          <hr>
            {include file="../shortcut_configuration_section.tpl"
              displayMode=$form.fields.PAYPAL_EXPRESS_CHECKOUT_DISPLAY_MODE_SIGNUP
              widget_type='signup'
              color=$form.fields.PAYPAL_EXPRESS_CHECKOUT_SHORTCUT_STYLE_COLOR_SIGNUP
              shape=$form.fields.PAYPAL_EXPRESS_CHECKOUT_SHORTCUT_STYLE_SHAPE_SIGNUP
              height=$form.fields.PAYPAL_EXPRESS_CHECKOUT_SHORTCUT_STYLE_HEIGHT_SIGNUP
              width=$form.fields.PAYPAL_EXPRESS_CHECKOUT_SHORTCUT_STYLE_WIDTH_SIGNUP
              label=$form.fields.PAYPAL_EXPRESS_CHECKOUT_SHORTCUT_STYLE_LABEL_SIGNUP
              preview=$form.fields.signup_shortcut_preview.set.shortcut|default:''
            }
        {/if}
    </div>

{/block}


