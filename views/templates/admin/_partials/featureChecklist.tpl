{**
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

<div>
  {if isset($vars.isBnplEnabled)}
    <div>
        {if $vars.isBnplEnabled}
          <i class="icon-check text-success"></i>
            {l s='Buy now pay later enabled' mod='paypal'}
        {else}
          <i class="icon-remove text-danger"></i>
            {l s='Buy now pay later disabled' mod='paypal'}
        {/if}
    </div>
  {/if}

  <div>
      {if $vars.isShortcutCustomized|default:false}
        <i class="icon-check text-success"></i>
          {l s='Customized shortcut button enabled' mod='paypal'}
      {else}
        <i class="icon-remove text-danger"></i>
          {l s='Customized shortcut button disabled' mod='paypal'}
      {/if}
  </div>

    {if isset($vars.isPuiEnabled)}
      <div>
          {if $vars.isPuiEnabled}
            <i class="icon-check text-success"></i>
              {l s='PUI enabled' mod='paypal'}
          {else}
            <i class="icon-remove text-danger"></i>
              {l s='PUI disabled' mod='paypal'}
          {/if}
      </div>
    {/if}

  <div>
      {if $vars.isOrderStatusCustomized|default:false}
        <i class="icon-check text-success"></i>
          {l s='Customized order status enabled' mod='paypal'}
      {else}
        <i class="icon-remove text-danger"></i>
          {l s='Customized order status disabled' mod='paypal'}
      {/if}
  </div>

  <div>
      {if $vars.isShowPaypalBenefits|default:false}
        <i class="icon-check text-success"></i>
          {l s='Paypal benefits enabled' mod='paypal'}
      {else}
        <i class="icon-remove text-danger"></i>
          {l s='Paypal benefits disabled' mod='paypal'}
      {/if}
  </div>

</div>




