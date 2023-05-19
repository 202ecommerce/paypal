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
<div class="row" data-dashboard>
  <div class="col col-12">
    <div class="card shadow">
      <div class="card-body">
      <div class="row">
        <div class="col col-md-6">
          <div class="card-body h-100 d-flex flex-column justify-content-between">
            <div>
              <div class="h5">{l s='Welcome on PayPal Dashboard' mod='paypal'}</div>
              <p>{l s='This is the first item\'s accordion body. It is hidden by default, until the collapse plugin adds the appropriate classes that we use to style each element. These classes control the overall appearance, as well as the showing and hiding via CSS transitions.' mod='paypal'}</p>
            </div>
            <div class="d-flex justify-content-between align-items-center mt-4">
              <ul class="list-unstyled mb-0">
                <li class="d-flex align-items-center mb-1">
                  {include
                    file="module:paypal/views/templates/admin/_partials/icon-status.tpl"
                    isSuccess=$isSandbox|default:false
                  }
                  {l s='Mode production enabled' mod='paypal'}
                </li>
                <li class="d-flex align-items-center">
                  {include
                    file="module:paypal/views/templates/admin/_partials/icon-status.tpl"
                    isSuccess=$isConfigured|default:false
                  }
                  {l s='Account connected' mod='paypal'}
                </li>
              </ul>
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
                  <a href="{$diagnosticPage|escape:'htmlall':'utf-8'}" target="_blank" class="">
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
                  <a href="{$loggerPage|escape:'htmlall':'utf-8'}" target="_blank" class="">
                    {l s='logs' mod='paypal'}
                  </a>
                </div>
              </div>
            </div>
            <div class="alert alert-warning d-flex align-items-center mt-5 mb-0">
            {strip}
              <span class="icon mr-1">
                <i class="material-icons">info</i>
              </span>
              {l s='Email address not confirmed, please' mod='paypal'}&nbsp;
              <a href="#">{l s='resend' mod='paypal'}</a>&nbsp;
              {l s='email' mod='paypal'}&nbsp;{/strip}
            </div>
          </div>
        </div>
      </div>
      </div>
    </div>
  </div>
  <div class="col col-md-6 mt-4">
    <div class="card h-100">
      <div class="card-header">
        {l s='Technical checklist' mod='paypal'}
      </div>
      <div class="card-body" technical-checklist-container>
        {include
          file="module:paypal/views/templates/admin/_partials/statusBlock.tpl"
          vars=$technicalChecklistForm.fields.technicalChecklist.set
        }
      </div>
    </div>
  </div>
  <div class="col col-md-6 mt-4">
    <div class="card h-100">
      <div class="card-header">
        {l s='Feature checklist' mod='paypal'}
      </div>
      <div class="card-body" feature-checklist-container>
        {include
          file="module:paypal/views/templates/admin/_partials/featureChecklist.tpl"
          vars=$featureChecklistForm.fields.featureChecklist.set
        }
      </div>
    </div>
  </div>
</div>
<button data-btn-section-reset class="btn btn-secondary mb-3 d-none">{l s='Back' mod='paypal'}</button>

