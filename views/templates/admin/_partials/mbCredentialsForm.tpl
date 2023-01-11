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
    <h4>{l s='API Credentials' mod='paypal'}</h4>
    <div class="row">

        <div class="col-md-12">

            <p>{l s='In order to accept PayPal Plus payments, please fill in your API REST credentials.' mod='paypal'}</p>
            <ul>
                <li>{l s='Access' mod='paypal'} <a target="_blank" href="https://developer.paypal.com/developer/applications/">{l s='https://developer.paypal.com/developer/applications/' mod='paypal'}</a></li>
                <li>{l s='Log in or Create a business account' mod='paypal'}</li>
                <li>{l s='Create a « REST API apps »' mod='paypal'}</li>
                <li>{l s='Click « Show » below « Secret: »' mod='paypal'}</li>
                <li>{l s='Copy/paste your « Client ID » and « Secret » below for each environment' mod='paypal'}</li>
            </ul>
            <hr/>
            <input type="hidden" class="method met" name="method" data-method-paypal/>

            {if $mode == 'SANDBOX'}
                <h4>{l s='Sandbox' mod='paypal'}</h4>
                <p>
                    <label for="sandbox_mb_client_id">{l s='Client ID' mod='paypal'}</label>
                    <input type="text" id="sandbox_mb_client_id" name="paypal_mb_sandbox_clientid" value="{if isset($paypal_mb_sandbox_clientid)}{$paypal_mb_sandbox_clientid|escape:'htmlall':'UTF-8'}{/if}"/>
                </p>
                <p>
                    <label for="sandbox_mb_secret">{l s='Secret' mod='paypal'}</label>
                    <input type="password" id="sandbox_mb_secret" name="paypal_mb_sandbox_secret" value="{if isset($paypal_mb_sandbox_secret)}{$paypal_mb_sandbox_secret|escape:'htmlall':'UTF-8'}{/if}"/>
                </p>
            {else}
                <h4>{l s='Live' mod='paypal'}</h4>
                <p>
                    <label for="live_mb_client_id">{l s='Client ID' mod='paypal'}</label>
                    <input type="text" id="live_mb_client_id" name="paypal_mb_live_clientid" value="{if isset($paypal_mb_live_clientid)}{$paypal_mb_live_clientid|escape:'htmlall':'UTF-8'}{/if}"/>
                </p>
                <p>
                    <label for="live_mb_secret">{l s='Secret' mod='paypal'}</label>
                    <input type="password" id="live_mb_secret" name="paypal_mb_live_secret" value="{if isset($paypal_mb_live_secret)}{$paypal_mb_live_secret|escape:'htmlall':'UTF-8'}{/if}"/>
                </p>
            {/if}

        </div>

    </div>
</div>




