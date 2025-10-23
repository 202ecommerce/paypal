{*
 * since 2007 PayPal
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
 *  @author since 2007 PayPal
 *  @author 202 ecommerce <tech@202-ecommerce.com>
 *  @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 *  @copyright PayPal
 *
 *}
<div welcome-board class="col col-12 pp__px-4">
  <div class="card shadow">
    <div class="card-body">
      <div class="row">
        <div class="col col-md-6">
          <div class="card-body h-100 d-flex flex-column justify-content-between">
            <div>
              <div class="h5">{l s='Welcome on PayPal Dashboard' mod='paypal'}</div>
              <div>
                <ul class="list-unstyled mb-0">
                  <li class="d-flex align-items-center mb-1">
                      {include
                      file=$moduleFullDir|cat:"/views/templates/admin/_partials/icon-status.tpl"

                      isSuccess=(false == $isSandbox|default:false)
                      }
                      {l s='Mode production enabled' mod='paypal'}
                  </li>
                  <li class="d-flex align-items-center">
                      {include
                      file=$moduleFullDir|cat:"/views/templates/admin/_partials/icon-status.tpl"

                      isSuccess=$isConfigured|default:false
                      }
                      {l s='Account connected' mod='paypal'}
                  </li>
                </ul>
              </div>
            </div>
            <div class="d-flex justify-content-between align-items-center mt-4">
              <span class="btn btn-secondary" id="logoutAccount" data-section-toggle="account">
                <span class="icon mr-2">
                  <i class="material-icons-outlined">account_circle</i>
                </span>
                {l s='Logout / Switch mode' mod='paypal'}
              </span>
            </div>
          </div>
        </div>
        <div class="col col-md-6">
          <div class="card-body h-100 d-flex flex-column justify-content-between">
            <div>
              <div class="h5">{l s='More Actions' mod='paypal'}</div>
              <div class="row">
                <div class="col col-md-6 d-flex align-items-center">
                  <span class="icon mr-1">
                    <i class="material-icons-outlined">edit_location_alt</i>
                  </span>
                    {l s='Configure' mod='paypal'}&nbsp;
                  <a href="#" data-section-toggle="tracking">
                      {l s='tracking' mod='paypal'}
                  </a>
                </div>
                <div class="col col-md-6 d-flex align-items-center mt-3">
                  <span class="icon mr-1">
                    <i class="material-icons-outlined">manage_search</i>
                  </span>
                    {l s='Make a' mod='paypal'}&nbsp;
                  <a href="{$diagnosticPage|escape:'htmlall':'UTF-8'}" target="_blank" class="">
                      {l s='diagnostic' mod='paypal'}
                  </a>
                </div>
                <div class="col col-md-6 d-flex align-items-center mt-3">
                  <span class="icon mr-1">
                    <i class="material-icons-outlined">toggle_on</i>
                  </span>
                    {l s='Configure the' mod='paypal'}&nbsp;
                  <a href="#" data-section-toggle="configuration">
                      {l s='module' mod='paypal'}
                  </a>
                </div>
                <div class="col col-md-6 d-flex align-items-center mt-3">
                  <span class="icon mr-1">
                    <i class="material-icons-outlined">feed</i>
                  </span>
                    {l s='Check the' mod='paypal'}&nbsp;
                  <a href="{$loggerPage|escape:'htmlall':'UTF-8'}" target="_blank" class="">
                      {l s='logs' mod='paypal'}
                  </a>
                </div>
              </div>
            </div>

              {if $merchantId|default:false}
                <div class="alert alert-warning d-flex align-items-center mt-5 mb-0">
                    {l s='You are connected with the account:' mod='paypal'} <b>{$merchantId|escape:'htmlall':'UTF-8'}</b>
                </div>
              {/if}

          </div>
        </div>
      </div>
    </div>
  </div>
</div>

