{*
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
<div class="w-100 mb-3">
  <div class="row">
    <div class="col-sm-12">
      <div class="row justify-content-center">
        <div class="col-xl-12 pr-5 pl-5">
          <div class="card">
            <div class="card-header d-flex justify-content-between">
              <div>
                <span class="material-icons">webhook</span>
                  {l s='Hooks' mod='paypal'}
              </div>
                {if empty($hooksOnError)}
                  <div class="badge-success px-2 mb-0">{l s='All hooks are registered' mod='paypal'}</div>
                {else}
                  <div class="badge-danger px-2 mb-0">{l s='Unregistered hooks found' mod='paypal'}</div>
                {/if}
            </div>
            <div class="form-wrapper justify-content-center col-xl-12 mt-3 {if empty($hooksOnError)}d-none{/if}">
              <div class="mt-2 alert alert-info">{l s='Check if hooks of %s module are all plugged.' sprintf=[$module_name|escape:'html':'UTF-8'] mod='paypal'}</div>
              <div class="table-wrapper">
                <table class="table border-bottom">
                  <thead>
                  <tr>
                    <th>#</th>
                    <th>{l s='Hook name' mod='paypal'}</th>
                      {foreach $shopList as $shop}
                        <th>{l s='Shop' mod='paypal'}: {$shop.name|escape:'html':'UTF-8'}</th>
                      {/foreach}
                    <th>{l s='Actions' mod='paypal'}</th>
                  </tr>
                  </thead>
                  <tbody>
                  {foreach $hooks as $index => $hook}
                      {assign var='shouldBeFixed' value=false}
                    <tr>
                      <td>{$index + 1}</td>
                      <td>{$hook.name|escape:'html':'UTF-8'}</td>
                        {foreach $shopList as $shop}
                            {foreach $hook.shops as $hookShop}
                                {if $shop.id_shop == $hookShop.id}
                                  <td>
                                      {if $hookShop.value}
                                        <span class="badge badge-success">{l s='OK' mod='paypal'}</span>
                                      {else}
                                          {assign var='shouldBeFixed' value=true}
                                        <a href="{$actionsLink|cat:'&id_shop='|cat:$hookShop.id|cat:'&hookName='|cat:$hook.name|cat:'&event=fixHook'|escape:'html':'UTF-8'}">
                                          <span class="badge badge-danger">{l s='KO' mod='paypal'}</span>
                                        </a>
                                      {/if}
                                  </td>
                                {/if}
                            {/foreach}
                        {/foreach}
                      <td>
                          {if !$shouldBeFixed}
                            -
                          {else}
                            <a class="btn btn-danger py-0"
                               style="color: #fff;"
                               href="{$actionsLink|cat:'&hookName='|cat:$hook.name|cat:'&event=fixHooks'|escape:'html':'UTF-8'}">
                                {l s='Fix hook' mod='paypal'}
                            </a>
                          {/if}
                      </td>
                    </tr>
                  {/foreach}
                  </tbody>
                </table>
              </div>
                {if !empty($hooksOnError)}
                  <div class="mb-3">
                    <p>{l s='The next hooks should be activated for your module' mod='paypal'}</p>
                    <ul>
                        {foreach $hooksOnError as $hook}
                          <li>{$hook|escape:'html':'UTF-8'}</li>
                        {/foreach}
                    </ul>
                    <a class="btn btn-danger py-0"
                       style="color: #fff;"
                       href="{$actionsLink|cat:'&event=fixAllHooks'|escape:'html':'UTF-8'}">
                        {l s='Fix all hooks' mod='paypal'}
                    </a>
                  </div>
                {/if}
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
