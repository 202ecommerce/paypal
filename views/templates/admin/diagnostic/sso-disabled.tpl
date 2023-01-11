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
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">


<div class="row w-100 h-100 align-items-center justify-content-center">
  <div class="col-6">
    <div class="card text-center">
      <div class="card-header">
          {l s='Authentication not available for the moment' mod='paypal'}
      </div>
      <div class="card-body">
        <img id="logo" src="{$logo|escape:'html':'UTF-8'}"/>
        <div class="text-center">{$ps_version|escape:'html':'UTF-8'}</div>
      </div>
      <div class="card-footer text-muted">
        <a href="http://www.prestashop.com/" onclick="return !window.open(this.href);">
          &copy; PrestaShop&#8482; 2007-{$smarty.now|date_format:"%Y"} - All rights reserved
        </a>
      </div>
    </div>
  </div>
</div>