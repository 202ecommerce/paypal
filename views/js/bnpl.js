/*
 *
 *  2007-2024 PayPal
 *
 *  NOTICE OF LICENSE
 *
 *  This source file is subject to the Academic Free License (AFL 3.0)
 *  that is bundled with this package in the file LICENSE.txt.
 *  It is also available through the world-wide-web at this URL:
 *  http://opensource.org/licenses/afl-3.0.php
 *  If you did not receive a copy of the license and are unable to
 *  obtain it through the world-wide-web, please send an email
 *  to license@prestashop.com so we can send you a copy immediately.
 *
 *  DISCLAIMER
 *
 *  Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 *  versions in the future. If you wish to customize PrestaShop for your
 *  needs please refer to http://www.prestashop.com for more information.
 *
 *  @author 2007-2024 PayPal
 *  @author 202 ecommerce <tech@202-ecommerce.com>
 *  @copyright PayPal
 *  @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 *
 */

var BNPL = function(conf) {

    this.validationController = conf['validationController'] === undefined ? null : conf['validationController'];

    this.paypal = conf['paypal'] === undefined ? null : conf['paypal'];

    this.messages = conf['messages'] === undefined ? [] : conf['messages'];
}

BNPL.prototype.render = function (container, order, onIsNotEligible) {

    if (this.paypal === null) {
        return;
    }

    var paypalButton = this.paypal.Buttons({

        fundingSource: this.paypal.FUNDING.PAYLATER,

        createOrder: function(data, actions) {
            return this.getIdOrder();
        }.bind(this),

        onApprove: function(data, actions) {
            return this.validateOrder(data)
        }.bind(this)
    });

    if (paypalButton.isEligible() == false) {
        if (typeof onIsNotEligible == 'function') {
            onIsNotEligible();
        }

        return;
    }

    paypalButton.render(container);
}

BNPL.prototype.getIdOrder = function() {
    if (this.validationController === null) {
        return;
    }

    var url = new URL(this.validationController);
    url.searchParams.append('ajax', '1');
    url.searchParams.append('action', 'CreateOrder');

    return fetch(url)
        .then(function(response) {
            return response.json();
        })
        .then(function(response) {
            if (response.success) {
                return response.idOrder;
            }
        });
}

BNPL.prototype.validateOrder = function(detail) {
    if (this.validationController === null) {
        return;
    }

    var form = document.createElement('form');
    var input = document.createElement('input');

    input.name = "paymentData";
    input.value = JSON.stringify(detail);

    form.method = "POST";
    form.action = this.validationController;

    form.appendChild(input);
    document.body.appendChild(form);
    form.submit();
}