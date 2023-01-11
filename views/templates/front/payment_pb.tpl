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
<div class="row">
    <div class="col-xs-12 col-md-10">
        <div class="paypal-braintree-row-payment">
            <div class="payment_module paypal-braintree">
                <form action="{$braintreeSubmitUrl}" id="paypal-braintree-form" method="post">
                {include file="module:paypal/views/templates/front/payment_infos.tpl"}
                {if isset($init_error)}
                    <div class="alert alert-danger">{$init_error}</div>
                {else}
                    <input type="hidden" name="payment_method_nonce" id="paypal_payment_method_nonce"/>
                    <input type="hidden" name="payment_method_bt" value="{$bt_method|escape:'htmlall':'UTF-8'}"/>
                    <div id="paypal-button"></div>
                    <div id="paypal-vault-info"><p>{l s='You have to finish your payment done with your PayPal account:' mod='paypal'}</p></div>
                    {if isset($active_vaulting) && $active_vaulting}
                        <div class="save-in-vault">
                            <input type="checkbox" name="save_account_in_vault" id="save_account_in_vault"/> <label for="save_account_in_vault"> {l s='Memorize my PayPal account' mod='paypal'}</label>
                        </div>
                    {/if}
                    {if isset($active_vaulting) && isset($payment_methods) && !empty($payment_methods)}
                        <div id="bt-vault-form">
                            <p><b>{l s='Choose your PayPal account' mod='paypal'}:</b></p>
                            <select name="pbt_vaulting_token" class="form-control">
                                <option value="">{l s='Choose your paypal account' mod='paypal'}</option>
                                {foreach from=$payment_methods key=method_key  item=method}
                                    <option value="{$method.token|escape:'htmlall':'UTF-8'}">
                                        {if $method.name}{$method.name|escape:'htmlall':'UTF-8'} - {/if}
                                        {$method.info|escape:'htmlall':'UTF-8'}
                                    </option>
                                {/foreach}
                            </select>
                        </div>
                    {/if}
                {/if}
                </form>
                <div id="bt-paypal-error-msg" class="alert alert-danger"></div>
            </div>
        </div>
    </div>
</div>


<script>
    var paypal_braintree = {
        authorization : '{$braintreeToken|escape:'htmlall':'UTF-8'}',
        amount : {$braintreeAmount|escape:'htmlall':'UTF-8'},
        mode : '{$mode|escape:'htmlall':'UTF-8'}',
        currency : '{$currency|escape:'htmlall':'UTF-8'}'
    };
    paypal_braintree.translations = {
        empty_nonce:"{l s='Click paypal button first' mod='paypal'}"
    };


</script>
