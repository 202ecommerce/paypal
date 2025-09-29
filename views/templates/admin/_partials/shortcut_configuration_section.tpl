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

{if isset($displayMode) && $displayMode}
    {include file="./form-fields.tpl" field=$displayMode dynamicField=false}
{/if}

{if isset($hooks) && $hooks}
    {include file="./form-fields.tpl" field=$hooks dynamicField=false}
{/if}

{if isset($widget_type) && $widget_type}
    {include file="./form-fields.tpl"
      field=[
          'label' => 'Widget code',
          'type' => 'widget-code',
          'code' => '{widget name=\'paypal\' action=\'paymentshortcut\'}',
          'name' => 'widget-code-'|cat:$widget_type
      ]
      dynamicField=false
    }
{/if}

<div customize-style-shortcut-container>
  <div class="form-group row ">
    <div class="col-7 offset-3">
      <div preview-section class="invisible" style="position: relative; pointer-events: none">
        <div button-container>{$preview|default:'' nofilter}</div>
        <div class="overlay"></div>
      </div>
    </div>
  </div>

    {if isset($color) && $color}
        {include file="./form-fields.tpl" field=$color dynamicField=false}
    {/if}

    {if isset($shape) && $shape}
        {include file="./form-fields.tpl" field=$shape dynamicField=false}
    {/if}

    {if isset($height) && isset($width) && $height && $width}
      <div class="form-group row">
        <label class="form-control-label col-3" for="{$height.name|escape:'htmlall':'UTF-8'}">{$height.label|escape:'htmlall':'UTF-8'}</label>
        <div class="{[
        'col-7' => !$isModal,
        'col-9' => $isModal
        ]|classnames}">
          <div class="row" chain-input-container>
            <div class="col col-6 pr-2">
              <input
                type="text"
                name="{$width.name|escape:'htmlall':'UTF-8'}"
                id="{$width.name|escape:'htmlall':'UTF-8'}"
                class="form-control"
                {if $width.placeholder|default:false}
                  placeholder="{$width.placeholder|escape:'htmlall':'UTF-8'}"
                {/if}
                value="{$width.value|default:''|escape:'htmlall':'UTF-8'}"
                data-type="width"
                data-msg-error="{l s='Minimum 150 px is required' mod='paypal'}"
              >
              <div class="text-muted small mt-1">{l s='Width' mod='paypal'}</div>
            </div>
            <div class="col col-6 pl-2">
              <input
                type="text"
                name="{$height.name|escape:'htmlall':'UTF-8'}"
                id="{$height.name|escape:'htmlall':'UTF-8'}"
                class="form-control"
                {if $height.placeholder|default:false}
                  placeholder="{$height.placeholder|escape:'htmlall':'UTF-8'}"
                {/if}
                value="{$height.value|default:''|escape:'htmlall':'UTF-8'}"
                data-type="height"
                data-msg-error="{l s='Value from 25 to 55 is required' mod='paypal'}"
              >
              <div class="text-muted small mt-1">{l s='Height' mod='paypal'}</div>
            </div>
            <div class="col col-12" msg-container></div>
          </div>
        </div>
      </div>
    {/if}

    {if isset($label) && $label}
        {include file="./form-fields.tpl" field=$label dynamicField=false}
    {/if}

</div>
