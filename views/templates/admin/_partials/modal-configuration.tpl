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
<div class="modal" id="modal-configuration" tabindex="-1" aria-hidden="true">
 <div class="modal-dialog modal-lg modal-dialog-centered" data-modal-dialog-configuration>
   <div class="modal-content">

     <div class="modal-body" id="modal-configuration-steps">
      <div class="card-body pb-0">
        <div class="row">
          <div class="col-6">
            <img src="{$moduleDir|addslashes}/views/img/paypal.svg"  alt="paypal">
          </div>
          <div class="col-6 d-flex flex-column">
            <div class="progress">
              <div class="progress-bar" data-steps-progress role="progressbar" style="width: 0;" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
            <span class="badge bg-warning text-primary ml-auto mt-4">{l s='Step :' mod='paypal'} <span data-badge-current-step>1</span> {l s='out of' mod='paypal'} <span data-badge-max-step></span></span>
          </div>
        </div>
      </div>
      <div>
          {if isset($accountForm)}
            <div data-step-content>
                {include
                file=$moduleFullDir|cat:"/views/templates/admin/_partials/section.tpl"
                form=$accountForm
                isModal=true
                }
            </div>
          {/if}
          {if isset($checkoutForm)}
            <div class="d-none" data-step-content>
                {include
                file=$moduleFullDir|cat:"/views/templates/admin/_partials/section.tpl"
                form=$checkoutForm
                isModal=true
                }
            </div>
          {/if}
          {if isset($formInstallment)}
            <div class="d-none" data-step-content>
                {include
                file=$moduleFullDir|cat:"/views/templates/admin/_partials/section.tpl"
                form=$formInstallment
                sectionColFormClasses=' '
                sectionColInfoClasses=' '
                isModal=true
                }
            </div>
          {/if}
          {if isset($shortcutConfigurationForm)}
            <div class="d-none" data-step-content>
                {include
                file=$moduleFullDir|cat:"/views/templates/admin/_partials/section.tpl"
                form=$shortcutConfigurationForm
                isModal=true
                }
            </div>
          {/if}
          {if isset($orderStatusForm)}
            <div class="d-none" data-step-content>
                {include
                file=$moduleFullDir|cat:"/views/templates/admin/_partials/section.tpl"
                form=$orderStatusForm
                isModal=true
                }
            </div>
          {/if}
          {if isset($whiteListForm)}
            <div class="d-none" data-step-content>
                {include
                file=$moduleFullDir|cat:"/views/templates/admin/_partials/section.tpl"
                form=$whiteListForm
                isModal=true
                }
            </div>
          {/if}
        {if isset($cloudSyncForm)}
          <div class="d-none" data-step-content>
            {include
            file=$moduleFullDir|cat:"/views/templates/admin/_partials/section.tpl"
            form=$cloudSyncForm
            isModal=true
            }
          </div>
        {/if}
      </div>
     </div>
   </div>
 </div>
</div>
