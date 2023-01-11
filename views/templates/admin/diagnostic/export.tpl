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
            <div class="card-header">
              <div>
                <span class="material-icons">file_download</span>
                  {l s='Export' mod='paypal'}
              </div>
            </div>
            <div class="form-wrapper justify-content-center col-xl-12 my-3">
              {l s='You can export all informations using the button `Export`' mod='paypal'}
            </div>
            <div class="card-footer">
              <div class="d-flex justify-content-end">
                <a href="{$exportStubLink|escape:'html':'UTF-8'}" class="btn btn-default">{l s='Export' mod='paypal'}</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>