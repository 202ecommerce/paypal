{**
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


<div class="pp-info" data-pp-info>
  <div class="row">
    <div class="col-md-6 item pp__mb-3">
      <div><img src="{$urls.base_url}modules/paypal/views/img/advantage-logos/verified_user.png" alt=""></div>
      <div class="header pp__pt-1">{l s='Safer and more protected' mod='paypal'}</div>
      <div class="desc pp__pt-1">{l s='Buyer protection and free return shipping covers eligible purchase.' mod='paypal'}</div>
    </div>
    <div class="col-md-6 item pp__mb-3">
      <div><img src="{$urls.base_url}modules/paypal/views/img/advantage-logos/language.png" alt=""></div>
      <div class="header pp__pt-1">{l s='Simple and convenient' mod='paypal'}</div>
      <div class="desc pp__pt-1">{l s='Use your account from wherever in the world you shop.' mod='paypal'}</div>
    </div>
  </div>
  <div class="row">
    <div class="col-md-6 item pp__mb-3">
      <div><img src="{$urls.base_url}modules/paypal/views/img/advantage-logos/offline_bolt.png" alt=""></div>
      <div class="header pp__pt-1">{l s='Wherever you are' mod='paypal'}</div>
      <div class="desc pp__pt-1">{l s='Pay fast from any device without entering your financial details.' mod='paypal'}</div>
    </div>
    <div class="col-md-6 item pp__mb-3">
      <div><img src="{$urls.base_url}modules/paypal/views/img/advantage-logos/monetization.png" alt=""></div>
      <div class="header pp__pt-1">{l s='No additional fees' mod='paypal'}</div>
      <div class="desc pp__pt-1">{l s='Skip entering your financial info, PayPal keeps your data secure.' mod='paypal'}</div>
    </div>
  </div>
</div>

<div data-paypal-info class="pp__pl-2 pp__d-table-cell" style="display: none">
  <a href="#"
     class="pp__text-primary"
     data-paypal-info-popover
     data-html="true"
     data-container="body"
     data-content=""
  >
    <i class="material-icons">info</i>
  </a>
</div>
