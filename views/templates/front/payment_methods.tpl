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

{extends file='customer/page.tpl'}

{block name='page_title'}
    {l s='My payment methods' mod='paypal'}
{/block}

{block name='page_content'}
    <!-- Page content -->
    {if $payment_methods}
        <form action="{$link->getModuleLink('paypal', 'account', ['process' => 'save'])}" method="post">
        {foreach from=$payment_methods key=method_key  item=payment_method}
            {if $method_key == 'card-braintree'}<h3>{l s='Your cards' mod='paypal'}</h3>{/if}
            {if $method_key == 'paypal-braintree'}<h3>{l s='Your paypal accounts' mod='paypal'}</h3>{/if}
            {foreach from=$payment_method key=key  item=method}
                <p class="method">
                    {if $method.name}<b>{$method.name|escape:'htmlall':'UTF-8'} : </b>{/if}
                    {$method.info|escape:'htmlall':'UTF-8'}
                    <a href="{$link->getModuleLink('paypal', 'account', ['process' => 'delete', 'method' => {$method.method|escape:'htmlall':'UTF-8'}, 'id_method' => {$method.id_paypal_vaulting|escape:'htmlall':'UTF-8'}])}"><i class="material-icons">delete</i></a>
                    <br />
                    {if !$method.name}{l s='Add name' mod='paypal'}{else}{l s='Edit name' mod='paypal'}{/if}
                    <span class="edit_name" data-method_id="{$method.id_paypal_vaulting|escape:'htmlall':'UTF-8'}"><i class="material-icons">mode_edit</i></span>
                    <input type="text" value="{$method.name|escape:'htmlall':'UTF-8'}" name="name_{$method.id_paypal_vaulting|escape:'htmlall':'UTF-8'}" class="form-control" style="display: none"/>
                </p>
            {/foreach}
        {/foreach}
            <p><button class="btn btn-default" type="submit">{l s='Save' mod='paypal'} <i class="material-icons">save</i></button></p>
        </form>
    {else}
        {l s='You don\'t have saved payment methods from Paypal' mod='paypal'}
    {/if}

    <script type="text/javascript">
        document.addEventListener("DOMContentLoaded", function(){
            $(document).on('click', '.edit_name', function(){
               var methodId = $(this).data('method_id');
               $('input[name=name_'+methodId+']').show();
            });
        });
    </script>
{/block}
