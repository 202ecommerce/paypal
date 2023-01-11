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
                <span class="material-icons">folder</span>
                  {l s='PrestaShop Overrides' mod='paypal'}
              </div>
                {if empty($overrides)}
                  <div class="badge-success px-2 mb-0">{l s='There is no overrides' mod='paypal'}</div>
                {else}
                  <div class="badge-warning px-2 mb-0">{l s='Some overrides found' mod='paypal'}</div>
                {/if}
            </div>
            <div class="form-wrapper justify-content-center col-xl-12 mt-3 d-none">
              <div class="mt-2 alert alert-info">{l s='Overrides may cause conflits with %s modules. As all customizations, overrides get complexity to your PrestaShop.' sprintf=[$module_name|escape:'html':'UTF-8'] mod='paypal'}</div>
              {foreach name="overrides" from=$overrides item=override}
                {if $smarty.foreach.overrides.first}
                  <p>
                    {l s='You\'ll found here the list of overrides.' mod='paypal'}
                  </p>
                  <ul>
                {/if}
                <li>{$override|escape:'html':'UTF-8'}</li>
                {if $smarty.foreach.overrides.last}
                  </ul>
                {/if}
              {foreachelse}
                <p>{l s='No overrides detected.' mod='paypal'}</p>
              {/foreach}
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>



            
