{*
* 2007-2022 PrestaShop
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
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
* @author    202-ecommerce <tech@202-ecommerce.com>
* @copyright Copyright (c) Stripe
* @license   Commercial license
*}

<div class="w-100 mb-3">
    <div class="row">
        <div class="col-sm-12">
            <div class="row justify-content-center">
                <div class="col-xl-12 pr-5 pl-5">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between">
                            <div>
                                <span class="material-icons">list_alt</span>
                                {l s='Order states' mod='paypal'}
                            </div>
                            {if empty($checkOrderStates['are_wrong']) === true}
                                <div class="badge-success px-2 mb-0">{l s='All order states are valid' mod='paypal'}</div>
                            {else}
                                <div class="badge-danger px-2 mb-0">{l s='Invalid order states found' mod='paypal'}</div>
                            {/if}
                        </div>
                        <div class="form-wrapper justify-content-center col-xl-12 mt-3
                            {if empty($checkOrderStates['are_wrong']) === true}
                                d-none
                            {/if}">
                            <div class="mt-2 alert alert-info">
                                {l s='Compare your %s order states with the official package of the same release.'
                                   sprintf=$module_name mod='paypal'}
                            </div>
                            <form class="card my-3" method="post" action="{$actionsLink|escape:'html':'UTF-8'}">
                                <div class="card-body">
                                    <table class="table table-striped mb-2">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>{l s='Configuration name' mod='paypal'}</th>
                                                <th>{l s='Order state associated' mod='paypal'}</th>
                                                <th>{l s='Is valid' mod='paypal'}</th>
                                                {if $checkOrderStates['are_wrong']}
                                                    <th>{l s='Error details' mod='paypal'}</th>
                                                {/if}
                                            </tr>
                                        </thead>
                                        <tbody>
                                        {foreach from=$checkOrderStates['order_states'] key=index item=orderStateConfig}
                                            <tr>
                                                <td>{$index+1}</td>
                                                <td>{$orderStateConfig.name|escape:'html':'UTF-8'}</td>
                                                <td>
                                                    <label>
                                                        <select class="form-control-select" name="{$orderStateConfig.name|escape:'html':'UTF-8'}">
                                                            {if isset($checkOrderStates['unassociate']) === true}
                                                                <option class="text-danger">{l s='Not associated' mod='paypal'}</option>
                                                            {/if}
                                                            {foreach $orderStateList as $orderState}
                                                                <option value="{$orderState.id_order_state|escape:'html':'UTF-8'}"
                                                                        {if $orderStateConfig.value == $orderState.id_order_state}
                                                                            {l s='selected' mod='paypal'}
                                                                        {/if}>
                                                                    {$orderState.name|escape:'html':'UTF-8'}
                                                                </option>
                                                            {/foreach}
                                                        </select>
                                                    </label>
                                                </td>
                                                <td>
                                                    {if empty($orderStateConfig.error)}
                                                        <span class="label label-success">{l s='Yes' mod='paypal'}</span>
                                                      </td>
                                                      <td>
                                                      &nbsp;
                                                    {else}
                                                        <span class="label label-danger">{l s='No' mod='paypal'}</span>
                                                      </td>
                                                      <td>
                                                        <span>{l s='Order state have' mod='paypal'} :</span>
                                                        <ul>
                                                          {foreach $orderStateConfig.error as $error}
                                                            <li>{$error|escape:'html':'UTF-8'}</li>
                                                          {/foreach}
                                                        </ul>
                                                    {/if}
                                                </td>
                                            </tr>
                                        {/foreach}
                                        </tbody>
                                    </table>
                                    <div class="text-center">
                                        <button type="submit" class="btn btn-sm btn-primary" name="event" value="associateOrderState">
                                            {l s='Associate order states' mod='paypal'}
                                        </button>
                                        {if empty($checkOrderStates['are_wrong']) === false}
                                            <button type="submit" class="btn btn-sm btn-primary" name="event" value="fixOrderState">
                                                {l s='Fix order states' mod='paypal'}
                                            </button>
                                        {/if}
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
