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
let ppp = {},
    exec_ppp_payment = true;
$(document).ready( () => {
  function initPaymentWall() {
    let showPui = false;
    if (modePPP == 'sandbox') {
      showPui = true;
    }

    // Add parameters for paypal plus method
    ppp = PAYPAL.apps.PPP({
      "approvalUrl": approvalUrlPPP,
      "placeholder": "ppplus",
      "mode": modePPP,
      "language": languageIsoCodePPP,
      "country": countryIsoCodePPP,
      "buttonLocation": "outside",
      "useraction": "continue",
      "showPuiOnSandbox": showPui
    });
  }

  function waitPaypalLoaded() {
    if (typeof PAYPAL == "undefined") {
      setTimeout(waitPaypalLoaded, 200);
      return;
    }

    initPaymentWall();
  }

  if ($('#checkout-payment-step').hasClass('js-current-step')) {
    waitPaypalLoaded();
  }

  // Order payment button action for paypal plus
  $('#payment-confirmation button').on('click', (e) => {
    let selectedOption = $('input[name=payment-option]:checked').attr('id');
    if ($(`#${selectedOption}-additional-information .payment_module`).hasClass('paypal-plus')) {
      e.preventDefault();
      e.stopPropagation();
      doPatchPPP();
    }
  });
});


// Show popup and call doCheckout() function from API
const doPatchPPP = () => {
  if (exec_ppp_payment) {
    exec_ppp_payment = false;

    try {
      $.fancybox.open({
        content: `<div id="popup-ppp-waiting"><p>${waitingRedirectionMsg}</p></div>`,
        closeClick: false,
        height: 'auto',
        helpers: {
          overlay: {
            closeClick: false
          }
        },
      });
    } catch (e) {
      document.querySelector('#payment-confirmation button').disabled = true;
    }

    $.ajax({
      type: 'POST',
      url: ajaxPatchUrl,
      dataType: 'json',
      data: {
        idPayment: idPaymentPPP,
      },
      success: (json) => {
        if (json.success) {
          ppp.doCheckout();
        } else {
          window.location.replace(json.redirect_link);
        }
      }
    });
  }
}

