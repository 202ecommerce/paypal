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
{assign var='variant' value=$variant|default:'normal'}

<ul>
  <li>
    {if $variant == 'help'}<p class='h4'>{/if}
      {l s='Round mode: "Round up away from zero, when it is half way there (recommended) "' mod='paypal'}
    {if $variant == 'help'}</p>{/if}
  </li>
  <li>
    {if $variant == 'help'}<p class='h4'>{/if}
      {l s='Round type: "Round on each item"' mod='paypal'}
    {if $variant == 'help'}</p>{/if}
  </li>
  <li>
    {if $variant == 'help'}<p class='h4'>{/if}
      {l s='Number of decimals' d='Admin.Shopparameters.Feature'}: "2"
    {if $variant == 'help'}
      <p>
      <button class="btn btn-default" data-show-rounding-alert>
        {l s='Check requirements' mod='paypal'}
      </button>
      </p>
    {/if}
    {if $variant == 'help'}</p>{/if}
  </li>
</ul>

{if $variant != 'help'}
</br>
  <button class="btn btn-default" data-update-rounding-settings>
    {l s='Change rounding settings automatically' mod='paypal'}
  </button>
{/if}

