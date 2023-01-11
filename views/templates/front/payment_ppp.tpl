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

<style>
  #popup-ppp-waiting p {
    font-size: 16px;
    margin: 10px;
    line-height: 1.5em;
    color: #373a3c;
  }
</style>
<div class="row">
  <div class="col-xs-12 col-md-10">
    <div class="paypal-plus-row-payment">
      <div class="payment_module paypal-plus">
        {if isset($showAdvantage) && $showAdvantage}
            {include file="module:paypal/views/templates/front/payment_infos.tpl"}
        {/if}

        <div id="ppplus" style="width: 100%;"></div>
      </div>
    </div>
  </div>
</div>

