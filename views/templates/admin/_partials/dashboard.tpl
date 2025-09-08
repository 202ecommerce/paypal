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
<div class="row" data-dashboard>
    {include file=$moduleFullDir|cat:"/views/templates/admin/_partials/welcome-board.tpl"}
  <div class="col col-md-6 mt-4 pp__px-4">
    <div class="card h-100">
      <div class="card-header">
        {l s='Technical checklist' mod='paypal'}
      </div>
      <div class="card-body" technical-checklist-container>
        {include
          file=$moduleFullDir|cat:"/views/templates/admin/_partials/statusBlock.tpl"
          vars=$technicalChecklistForm.fields.technicalChecklist.set
        }
      </div>
    </div>
  </div>
  <div class="col col-md-6 mt-4 pp__px-4">
    <div class="card h-100">
      <div class="card-header">
        {l s='Feature checklist' mod='paypal'}
      </div>
      <div class="card-body" feature-checklist-container>
        {include
          file=$moduleFullDir|cat:"/views/templates/admin/_partials/featureChecklist.tpl"
          vars=$featureChecklistForm.fields.featureChecklist.set
        }
      </div>
    </div>
  </div>
</div>
<button data-btn-section-reset class="btn btn-secondary mb-3 d-none">{l s='Back' mod='paypal'}</button>

