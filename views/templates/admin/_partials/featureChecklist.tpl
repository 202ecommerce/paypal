{**
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
<style>
  [vaulting-status-message] b {
    cursor: pointer;
  }
</style>
<div class="row pb-3 h-100">
  <div class="col-12 col-lg-9 col-xl-8 pb-4">
    <ul class="list-unstyled mb-0">
      {if isset($vars.isBnplEnabled)}
        <li class="d-flex align-items-center mb-1">
          {include
            file=$moduleFullDir|cat:"/views/templates/admin/_partials/icon-status.tpl"
            isSuccess=$vars.isBnplEnabled|default:false
          }
          {if $vars.isBnplEnabled}
            {l s='Buy now pay later enabled' mod='paypal'}
          {else}
            {l s='Buy now pay later disabled' mod='paypal'}
          {/if}
        </li>
      {/if}

      {if $vars.isShortcutCustomized|default:false}
        <li class="d-flex align-items-center mb-1">
            {include
            file=$moduleFullDir|cat:"/views/templates/admin/_partials/icon-status.tpl"
            isSuccess=true
            }
            {l s='Customized shortcut button enabled' mod='paypal'}
        </li>
      {/if}

      {if isset($vars.isCreditCardEnabled)}
        <li class="d-flex align-items-center mb-1">
            {if $vars.isCreditCardEnabled}
                {include
                file=$moduleFullDir|cat:"/views/templates/admin/_partials/icon-status.tpl"
                isSuccess=true
                }
                {l s='Credit card enabled' mod='paypal'}
            {else}
                {include
                file=$moduleFullDir|cat:"/views/templates/admin/_partials/icon-status.tpl"
                isSuccess=false
                }
                {l s='Credit card disabled' mod='paypal'}
            {/if}
        </li>
      {/if}

      {if isset($vars.isPuiEnabled)}
        <li class="d-flex align-items-center mb-1">
          {include
            file=$moduleFullDir|cat:"/views/templates/admin/_partials/icon-status.tpl"
            isSuccess=$vars.isPuiEnabled|default:false
          }
          {if $vars.isPuiEnabled}
            {l s='PUI enabled' mod='paypal'}
          {else}
            {l s='PUI disabled' mod='paypal'}
          {/if}
        </li>
      {/if}

      <li class="d-flex align-items-center mb-1">
        {include
          file=$moduleFullDir|cat:"/views/templates/admin/_partials/icon-status.tpl"
          isSuccess=true
        }
        {if $vars.isOrderStatusCustomized|default:false}
          {l s='Customized order status enabled' mod='paypal'}
        {else}
          {l s='Standard order status' mod='paypal'}
        {/if}
      </li>

      <li class="d-flex align-items-center">
        {include
          file=$moduleFullDir|cat:"/views/templates/admin/_partials/icon-status.tpl"
          isSuccess=$vars.isShowPaypalBenefits|default:false
        }
        {if $vars.isShowPaypalBenefits|default:false}
          {l s='PayPal benefits enabled' mod='paypal'}
        {else}
          {l s='PayPal benefits disabled' mod='paypal'}
        {/if}
      </li>

      <li class="d-flex align-items-center">
          {include
          file=$moduleFullDir|cat:"/views/templates/admin/_partials/icon-status.tpl"
          isSuccess=true
          }
          {l s='Tracking enabled' mod='paypal'}
      </li>

      {if isset($vars.vaultingStatusMessage)}
        <li class="d-flex align-items-center">
            {include
            file=$moduleFullDir|cat:"/views/templates/admin/_partials/icon-status.tpl"
            isSuccess=$vars.isVaultingCapabilityAvailable|default:false
            }
            <div vaulting-status-message>{{$vars.vaultingStatusMessage|escape:'htmlall':'utf-8'}|paypalreplace:['<b>' => '<b refresh-feature-checklist>']}</div>
        </li>
      {/if}

        {if isset($vars.isInstallmentMbEnabled)}
          <li class="d-flex align-items-center mb-1">
              {include
              file=$moduleFullDir|cat:"/views/templates/admin/_partials/icon-status.tpl"
              isSuccess=$vars.isInstallmentMbEnabled
              }
              {if $vars.isInstallmentMbEnabled}
                  {l s='Payment with installement is enabled' mod='paypal'}
              {else}
                  {l s='Payment with installement is disabled' mod='paypal'}
              {/if}
          </li>
        {/if}

    </ul>
  </div>

  <div class="col-12 col-lg-3 col-xl-4 align-items-end d-flex justify-content-end">
    <button class="btn btn-secondary ml-auto" refresh-feature-checklist>{l s='Refresh' mod='paypal'}</button>
  </div>

</div>
