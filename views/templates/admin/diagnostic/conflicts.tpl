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
{if !empty($conflicts.data)}
  <div class="w-100 mb-3">
    <div class="row">
      <div class="col-sm-12">
        <div class="row justify-content-center">
          <div class="col-xl-12 pr-5 pl-5">
            <div class="card">
              <div class="card-header d-flex justify-content-between">
                <div>
                  <span class="material-icons">block</span>
                    {l s='Known conflicts (modules, configuration)' mod='paypal'}
                </div>
                {if empty($conflicts.data)}
                  <div class="badge-success px-2 mb-0">{l s='There is no conflicts' mod='paypal'}</div>
                {else}
                  <div class="badge-warning px-2 mb-0">{l s='Conflicts found' mod='paypal'}</div>
                {/if}
              </div>
              <div class="form-wrapper justify-content-center col-xl-12 mt-3 {if empty($conflicts.data)}d-none{/if}">
                <ul>
                  {foreach $conflicts.data as $conflict}
                      <li>{$conflict|escape:'html':'UTF-8'}</li>
                  {/foreach}
                </ul>
              </div>
             {if $conflicts.action|escape:'html':'UTF-8'}
               <div class="card-footer {if empty($conflicts.data)}d-none{/if}">
                 <div class="d-flex justify-content-end">
                   <a href="{$conflicts.action|escape:'html':'UTF-8'}" class="btn btn-lg btn-primary badge-info" type="submit">
                       {l s='Fix conflicts' mod='paypal'}
                   </a>
                 </div>
               </div>
             {/if}
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
{/if}
