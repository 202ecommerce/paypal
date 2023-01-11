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
    {if $mode == 'SANDBOX'}
        <p>
          <label for="paypal_ec_clientid">{l s='Client ID' mod='paypal'}</label>
          <input
                  type="text"
                  id="paypal_ec_clientid"
                  name="paypal_ec_clientid_sandbox"
                  value="{if isset($paypal_ec_clientid)}{$paypal_ec_clientid|escape:'htmlall':'UTF-8'}{/if}"/>
        </p>
        <p>
          <label for="paypal_ec_secret">{l s='Secret' mod='paypal'}</label>
          <input
                  type="password"
                  id="paypal_ec_secret"
                  name="paypal_ec_secret_sandbox"
                  value="{if isset($paypal_ec_secret)}{$paypal_ec_secret|escape:'htmlall':'UTF-8'}{/if}"/>
        </p>
    {else}
        <p>
          <label for="paypal_ec_clientid">{l s='Client ID' mod='paypal'}</label>
          <input
                  type="text"
                  id="paypal_ec_clientid"
                  name="paypal_ec_clientid_live"
                  value="{if isset($paypal_ec_clientid)}{$paypal_ec_clientid|escape:'htmlall':'UTF-8'}{/if}"/>
        </p>
        <p>
          <label for="paypal_ec_secret">{l s='Secret' mod='paypal'}</label>
          <input
                  type="password"
                  id="paypal_ec_secret"
                  name="paypal_ec_secret_live"
                  value="{if isset($paypal_ec_secret)}{$paypal_ec_secret|escape:'htmlall':'UTF-8'}{/if}"/>
        </p>

    {/if}
</div>
