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
{if isset($error_msg)}
<div class="alert alert-danger">
    {$error_msg|escape:'htmlall':'UTF-8'}
</div>
{/if}
<li data-paypal-transaction-id>
    {if $method == 'BT'}
        {l s='Braintree transaction id:' mod='paypal'}
    {else}
        {l s='PayPal transaction id:' mod='paypal'}
    {/if}
    {$transaction_id|escape:'htmlall':'UTF-8'}
</li>
{if isset($ppp_information)}
    <dl>
        <dd>
            {l s='Bank name' mod='paypal'} : {$ppp_information->recipient_banking_instruction->bank_name|escape:'htmlall':'UTF-8'}
        </dd>
        <dd>
            {l s='Account holder name' mod='paypal'} : {$ppp_information->recipient_banking_instruction->account_holder_name|escape:'htmlall':'UTF-8'}
        </dd>
        <dd>
            {l s='IBAN' mod='paypal'} : {$ppp_information->recipient_banking_instruction->international_bank_account_number|escape:'htmlall':'UTF-8'}
        </dd>
        <dd>
            {l s='BIC' mod='paypal'} : {$ppp_information->recipient_banking_instruction->bank_identifier_code|escape:'htmlall':'UTF-8'}
        </dd>
        <dd>
            {l s='Amount due / currency' mod='paypal'} : {$ppp_information->amount->value|escape:'htmlall':'UTF-8'} {$ppp_information->amount->currency|escape:'htmlall':'UTF-8'}
        </dd>
        <dd>
            {l s='Payment due date' mod='paypal'} : {$ppp_information->payment_due_date|escape:'htmlall':'UTF-8'}
        </dd>
        <dd>
            {l s='Reference' mod='paypal'} : {$ppp_information->reference_number|escape:'htmlall':'UTF-8'}
        </dd>
    </dl>
{/if}
