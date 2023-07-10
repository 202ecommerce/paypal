/**
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
 */

// init in-context
$(document).ready(() => {

  // Make partial order refund in Order page in BO
  $(document).on('click', '#desc-order-partial_refund', () => {

    // Create checkbox and insert for Paypal refund
    if ($('#doPartialRefundPaypal').length == 0) {
			let newCheckBox = `<p class="checkbox"><label for="doPartialRefundPaypal">
			<input type="checkbox" id="doPartialRefundPaypal" name="doPartialRefundPaypal">
        ${chb_paypal_refund}</label></p>`;
      $('button[name=partialRefund]').parent('.partial_refund_fields').prepend(newCheckBox);
    }
  });
})
