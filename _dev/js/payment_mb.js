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

const PayPalMB = {

    ppp: null,

    config: null,

    paymentId: null,

    setConfig(paymentInfo, selectorId) {
        this.config = {
            "approvalUrl": paymentInfo.approvalUrlPPP,
            "placeholder": selectorId,
            "mode": paymentInfo.paypalMode,
            "payerEmail": paymentInfo.payerInfo.email,
            "payerFirstName": paymentInfo.payerInfo.first_name,
            "payerLastName": paymentInfo.payerInfo.last_name,
            "payerTaxId": paymentInfo.payerInfo.tax_id,
            "payerTaxIdType": paymentInfo.payerInfo.tax_id_type,
            "language": paymentInfo.language,
            "country": paymentInfo.country,
            "disallowRememberedCards": paymentInfo.disallowRememberedCards,
            "rememberedCards": paymentInfo.rememberedCards,
            "onError": this.handleError,
            "merchantInstallmentSelectionOptional": paymentInfo.merchantInstallmentSelectionOptional == 1,
            "merchantInstallmentSelection": 1
        };

        this.paymentId = paymentInfo.paymentId;
    },

    initCheckout() {
        this.setLoader("#ppplus-mb");
        this.getPaymentInfo().then(
            paymentInformation => {
                this.setConfig(paymentInformation, "ppplus-mb");

                if (this.config.country == 'BR' && this.config.payerTaxId == '') {
                    let message = typeof(EMPTY_TAX_ID) != 'undefined' ? EMPTY_TAX_ID : 'Payer tax id is empty';
                    this.showMessage(message, '#ppplus-mb', 'danger');
                    return;
                }

                this.ppp = PAYPAL.apps.PPP(this.config);
            }
        ).catch(error => {
            console.log(error);
        });
    },

    showMessage(message, selector, type) {
        let messageContainer = $(`<div class="alert alert-${type}" />`);
        messageContainer.text(message);
        $(selector).html(messageContainer);
    },

    setLoader(selector) {
        let loader = '<div class="pp__flex pp__justify-content-center"><div class="paypal-loader"></div></div>';
        $(selector).html(loader);
    },

    doPayment() {
        if (this.ppp != null) {
          this.ppp.doContinue();
        }
    },

    getPaymentInfo() {
        let promise = new Promise((resolve, reject) => {
            $.ajax({
                url: ajaxPatch,
                type: "POST",
                dataType: "JSON",
                data: {
                    ajax: true,
                    action: 'getPaymentInfo',
                },
                before () {

                },
                success (response) {
                    if (("success" in response) && (response["success"] == true)) {
                        resolve(response.paymentInfo);
                    }
                }
            });
        });

        return promise;
    },

    messageListener(event) {
        try {
            let data = JSON.parse(event.data);

            if (data.action == "checkout" && data.result.state == "APPROVED") {
                data['paymentId'] = PayPalMB.paymentId;
                PayPalMB.sendData(data, ajaxPatch);
            }
        } catch (exc) {
            console.log(exc);
        }
    },

    handleError(error) {
        if (typeof error.cause !== 'undefined') { //iFrame error handling

            let ppplusError = error.cause.replace (/['"]+/g,""); //log & attach this error into the order if possible

            // <<Insert Code Here>>

            switch (ppplusError)

            {

                case "INTERNAL_SERVICE_ERROR": //javascript fallthrough
                case "SOCKET_HANG_UP": //javascript fallthrough
                case "socket hang up": //javascript fallthrough
                case "connect ECONNREFUSED": //javascript fallthrough
                case "connect ETIMEDOUT": //javascript fallthrough
                case "UNKNOWN_INTERNAL_ERROR": //javascript fallthrough
                case "fiWalletLifecycle_unknown_error": //javascript fallthrough
                case "Failed to decrypt term info": //javascript fallthrough
                case "RESOURCE_NOT_FOUND": //javascript fallthrough
                case "INTERNAL_SERVER_ERROR":
                    alert ("Ocorreu um erro inesperado, por favor tente novamente. (" + ppplusError + ")"); //pt_BR
                    //Generic error, inform the customer to try again; generate a new approval_url and reload the iFrame.
                    // <<Insert Code Here>>
                    break;

                case "RISK_N_DECLINE": //javascript fallthrough
                case "NO_VALID_FUNDING_SOURCE_OR_RISK_REFUSED": //javascript fallthrough
                case "TRY_ANOTHER_CARD": //javascript fallthrough
                case "NO_VALID_FUNDING_INSTRUMENT":
                    alert ("Seu pagamento não foi aprovado. Por favor utilize outro cartão, caso o problema persista entre em contato com o PayPal (0800-047-4482). (" + ppplusError + ")"); //pt_BR
                    //Risk denial, inform the customer to try again; generate a new approval_url and reload the iFrame.
                    // <<Insert Code Here>>
                    break;

                case "CARD_ATTEMPT_INVALID":
                    alert ("Ocorreu um erro inesperado, por favor tente novamente. (" + ppplusError + ")"); //pt_BR
                    //03 maximum payment attempts with error, inform the customer to try again; generate a new approval_url and reload the iFrame.
                    // <<Insert Code Here>>
                    break;

                case "INVALID_OR_EXPIRED_TOKEN":
                    alert ("A sua sessão expirou, por favor tente novamente. (" + ppplusError + ")"); //pt_BR
                    //User session is expired, inform the customer to try again; generate a new approval_url and reload the iFrame.
                    // <<Insert Code Here>>
                    break;

                case "CHECK_ENTRY":
                    alert ("Por favor revise os dados de Cartão de Crédito inseridos. (" + ppplusError + ")"); //pt_BR
                    //Missing or invalid credit card information, inform your customer to check the inputs.
                    // <<Insert Code Here>>
                    break;

                default:  //unknown error & reload payment flow
                    alert ("Ocorreu um erro inesperado, por favor tente novamente. (" + ppplusError + ")"); //pt_BR
                //Generic error, inform the customer to try again; generate a new approval_url and reload the iFrame.
                // <<Insert Code Here>>

            }

        }

        console.log(error, typeof error);
    },

    sendData(data, action) {
        let messageSuccess;

        if (typeof(PAYMENT_SUCCESS) != 'undefined') {
            messageSuccess = PAYMENT_SUCCESS;
        } else {
            messageSuccess = 'Payment successful! You will be redirected to the payment confirmation page in a couple of seconds.';
        }

        PayPalMB.showMessage(messageSuccess, '#ppplus-mb', 'success');
        $('#ppplus-mb').css('height', '100%');
        let form = document.createElement('form');
        let input = document.createElement('input');

        input.name = "paymentData";
        input.value = JSON.stringify(data);

        form.method = "POST";
        form.action = action;

        form.appendChild(input);
        form.style = 'display: none';
        document.body.appendChild(form);
        form.submit();
    }

}


$(document).ready(() => {
    $('.payment-options input[name="payment-option"]').click((event) => {
        let paymentOption = $(event.target);
        if (paymentOption.attr('data-module-name') == "paypal_plus_mb") {
            PayPalMB.initCheckout();
        }

        prestashop.on("updatedCart", () => {
            PayPalMB.initCheckout();
        });
    });

    // Order payment button action for paypal plus
    $('#payment-confirmation button').on('click', (event) => {
        let selectedOption = $('input[name=payment-option]:checked');
        if (selectedOption.attr("data-module-name") == "paypal_plus_mb") {
            event.preventDefault();
            event.stopPropagation();
            PayPalMB.doPayment();
        }
    });

    if (window.addEventListener) {
        window.addEventListener("message", PayPalMB.messageListener, false);
    } else if (window.attachEvent) {
        window.attachEvent("onmessage", PayPalMB.messageListener);
    } else {
        throw new Error("Can't attach message listener");
    }
});
