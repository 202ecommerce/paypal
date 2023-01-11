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


{*Displaying a button or the iframe*}
<div class="row">
    <div class="col-xs-12 col-md-10">
        <div class="braintree-row-payment">
            <div class="payment_module braintree-card">
                <form action="{$braintreeSubmitUrl}" id="braintree-form" method="post">
                {if isset($init_error)}
                    <div class="alert alert-danger">{$init_error|escape:'htmlall':'UTF-8'}</div>
                    <div id="logo_braintree_by_paypal"><img src="https://s3-us-west-1.amazonaws.com/bt-partner-assets/paypal-braintree.png" height="20px"></div>
                {else}

                        {if isset($active_vaulting) && isset($payment_methods) && !empty($payment_methods)}
                            <div id="bt-vault-form">
                                <p><b>{l s='Choose your card' mod='paypal'}:</b></p>
                                <select name="bt_vaulting_token" class="form-control">
                                    <option value="">{l s='Choose your card' mod='paypal'}</option>
                                    {foreach from=$payment_methods key=method_key  item=method}
                                        <option value="{$method.token|escape:'htmlall':'UTF-8'}" {if $check3Dsecure} data-nonce="{$method.nonce}"{/if}>
                                            {if $method.name}{$method.name|escape:'htmlall':'UTF-8'} - {/if}
                                            {$method.info|escape:'htmlall':'UTF-8'}
                                        </option>
                                    {/foreach}
                                </select>
                            </div>
                        {/if}

                        <div id="block-card-number" class="block_field">
                            <div id="card-number" class="hosted_field"><div id="card-image"></div></div>

                        </div>

                        <div id="block-expiration-date" class="block_field half_block_field">
                            <div id="expiration-date" class="hosted_field"></div>
                        </div>

                        <div id="block-cvv" class="block_field half_block_field">
                            <div id="cvv" class="hosted_field"></div>
                        </div>

                        <input type="hidden" name="deviceData" id="deviceData"/>
                        <input type="hidden" name="client_token" value="{$braintreeToken|escape:'htmlall':'UTF-8'}">
                        <input type="hidden" name="liabilityShifted" id="liabilityShifted"/>
                        <input type="hidden" name="liabilityShiftPossible" id="liabilityShiftPossible"/>
                        <input type="hidden" name="payment_method_nonce" id="payment_method_nonce"/>
                        <input type="hidden" name="card_type" id="braintree_card_type"/>
                        <input type="hidden" name="payment_method_bt" value="{$method_bt|escape:'htmlall':'UTF-8'}"/>
                        <div class="paypal_clear"></div>
                        <div id="bt-card-error-msg" class="alert alert-danger"></div>
                        {if isset($active_vaulting) && $active_vaulting}
                            <div class="save-in-vault">
                                <input type="checkbox" name="save_card_in_vault" id="save_card_in_vault"/> <label for="save_card_in_vault"> {l s='Memorize my card' mod='paypal'}</label>
                            </div>
                        {/if}
                        <div id="logo_braintree_by_paypal"><img src="https://s3-us-west-1.amazonaws.com/bt-partner-assets/paypal-braintree.png" height="20px"></div>

                {/if}
                </form>
            </div>
        </div>
    </div>
</div>


<script>

    var authorization = '{$braintreeToken|escape:'htmlall':'UTF-8'}';
    var bt_amount = {$braintreeAmount|escape:'htmlall':'UTF-8'};
    var check3DS = {$check3Dsecure|escape:'htmlall':'UTF-8'};
    var bt_translations = {
        client:"{l s='Error creating Client Token' mod='paypal'}",
        card_nmb:"{l s='Card number' mod='paypal'}",
        cvc:"{l s='CVC' mod='paypal'}",
        date:"{l s='MM/YY' mod='paypal'}",
        hosted:"{l s='Error creating Hosted Fields' mod='paypal'}",
        empty:"{l s='All fields are empty! Please fill out the form.' mod='paypal'}",
        invalid:"{l s='Some fields are invalid :' mod='paypal'}",
        token:"{l s='Tokenization failed server side. Is the card valid?' mod='paypal'}",
        network:"{l s='Network error occurred when tokenizing.' mod='paypal'}",
        tkn_failed:"{l s='Tokenize failed' mod='paypal'}",
        https:"{l s='3D Secure requires HTTPS.' mod='paypal'}",
        load_3d:"{l s='Loading 3D Secure failed' mod='paypal'}",
        request_problem:"{l s='There was a problem with your request.' mod='paypal'}",
        failed_3d:"{l s='3D Secure Failed' mod='paypal'}",
        empty_field:"{l s='is empty.' mod='paypal'}",
        expirationDate:"{l s='Expiration Date' mod='paypal'}",
        number:"{l s='card number' mod='paypal'}",
        cvv:"{l s='CVV' mod='paypal'}",
    };

</script>


