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


<div class="col-lg-9">
    <p class="h3">
        {l s='Environment:' mod='paypal'}
        {if isset($sandbox) && $sandbox}
            <b>{l s='Sandbox' mod='paypal'}</b>
        {else}
            <b>{l s='Production' mod='paypal'}</b>
        {/if}
    </p>

    <p>{l s='Production mode is the Live environment where you\'ll be able to collect your real payments' mod='paypal'}</p>

    <p>
        <button class="btn btn-default" id="switchEnvironmentMode">
            {l s='Switch to' mod='paypal'}
            {if isset($sandbox) && $sandbox}
                {l s='Production mode' mod='paypal'}
            {else}
                {l s='Sandbox mode' mod='paypal'}
            {/if}
        </button>
    </p>
</div>


