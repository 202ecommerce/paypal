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
                <span class="material-icons">wifi</span>
                  {l s='Connection' mod='paypal'}
              </div>
            </div>
            <form action="{$actionsLink|cat:'&event=saveConnectStub'|escape:'html':'UTF-8'}"
                  class="d-flex flex-column mb-0"
                  method="post">
              <div class="form-wrapper justify-content-center col-xl-12 my-3">

                <div class="form-group row">
                  <label class="form-control-label col-lg-4 justify-content-end align-items-center">
                      {l s='SSO available' mod='__mod__'}
                  </label>
                  <div class="col-lg-4 align-items-center">
                    <span class="ps-switch ps-switch-lg">
                    <input
                            type="radio"
                            name="sso"
                            value="0"
                            {if !$connection.sso}checked{/if}
                    />
                    <label class="ml-4 pl-4">{l s='Off' mod='paypal'}</label>
                    <input
                            type="radio"
                            name="sso"
                            value="1"
                            {if $connection.sso}checked{/if}
                    />
                    <label class="ml-4 pl-4">{l s='On' mod='paypal'}</label>
                    <span class="slide-button"></span>
                  </span>
                  </div>
                </div>

                <div class="form-group row">
                  <label class="form-control-label col-lg-4 justify-content-end align-items-center">{l s='Employee' mod='paypal'}</label>
                  <div class="col-lg-4 align-items-center">
                    <select class="form-control"
                            name="employee"
                            data-toggle="select2"
                            data-minimumresultsforsearch="1"
                            aria-hidden="true">
                        {foreach $connection.employees as $employee}
                          <option value="{$employee.id|escape:'html':'UTF-8'}" {if $employee.selected}selected{/if}>{$employee.name|escape:'html':'UTF-8'}</option>
                        {/foreach}
                    </select>
                  </div>
                </div>
                <div class="form-group row">
                  <label class="form-control-label col-lg-4 justify-content-end align-items-center">{l s='Restricted IPs' mod='paypal'}</label>
                  <div class="col-lg-4 align-items-center">
                    <input type="text" class="form-control" name="ips" value="{$connection.ips|escape:'html':'UTF-8'}"/>
                  </div>
                </div>
              </div>
              <div class="card-footer">
                <div class="d-flex justify-content-end">
                  <button type="submit"
                          class="btn btn-default">{l s='Save and share' mod='paypal'}</button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>