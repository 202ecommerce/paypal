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

<div class="pp__flex" style="max-width: 400px">
  <input name = "PAYPAL_WHITE_LIST_IP" type="text" value="{$PAYPAL_WHITE_LIST_IP|escape:'html':'UTF-8'}">
  <button style="white-space: nowrap;" current-ip="{$paypal_current_ip}" type="button" class="btn btn-primary" add-ip-btn>{l s='Add my IP' mod='paypal'}</button>
</div>

<script>
  document.querySelector('[add-ip-btn]').addEventListener('click', function(event) {
      var input = document.querySelector('[name="PAYPAL_WHITE_LIST_IP"]');
      var list = input.value.split(';');

      list.push(event.target.getAttribute('current-ip'));
      list = list.filter(function(item) {
          return item.length > 0;
      });
      input.value = list.join(';');
  });
</script>
