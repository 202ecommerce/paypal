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
                <span class="material-icons">toggle_on</span>
                  {l s='Settings integrity' mod='paypal'}
              </div>
                {if $allConfigurationsAreSame}
                  <div class="badge-success px-2 mb-0">{l s='All configurations are same' mod='paypal'}</div>
                {else}
                  <div class="badge-warning px-2 mb-0">{l s='Some configurations have different values' mod='paypal'}</div>
                {/if}
            </div>
            <div class="form-wrapper justify-content-center col-xl-12 mt-3 {if $allConfigurationsAreSame}d-none{/if}">
              <div class="mt-2 alert alert-info">{l s='Verify your %s module setting in a quick view on all shop.' sprintf=[$module_name|escape:'html':'UTF-8'] mod='paypal'}</div>
              <table class="table border-bottom">
                <thead>
                <tr>
                  <th>{l s='Configuration' mod='paypal'}</th>
                  <th>{l s='All shops value' mod='paypal'}</th>
                    {foreach $shopList as $shop}
                      <th>{l s='Shop' mod='paypal'}: {$shop.name|escape:'html':'UTF-8'}</th>
                    {/foreach}
                </tr>
                </thead>
                <tbody>
                {foreach $configurations as $configName => $configValue}
                  <tr>
                    <td>
                      <span>{$configName|escape:'html':'UTF-8'}</span>
                      <span>&nbsp</span>
                        {if !$configValue.is_same}
                          <span class="badge-warning px-1">{l s='Config has different values' mod='paypal'}</span>
                        {else}
                          <span class="badge-success px-1">{l s='All config values are same' mod='paypal'}</span>
                        {/if}
                    </td>
                    <td>{$configValue.all_shop_value|escape:'html':'UTF-8'}</td>
                      {foreach $shopList as $shop}
                          {foreach $configValue.shops_value as $shopValue}
                              {if $shop.id_shop == $shopValue.id_shop}
                                <td>{$shopValue.value|escape:'html':'UTF-8'}</td>
                              {/if}
                          {/foreach}
                      {/foreach}
                  </tr>
                {/foreach}
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
