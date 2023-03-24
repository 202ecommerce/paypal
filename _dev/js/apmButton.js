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
// init incontext

import {Tools} from './tools.js';

export const ApmButton = function(conf) {

    this.method = typeof conf['method'] != 'undefined' ? conf['method'] : null;

    if (typeof conf['button'] != 'undefined') {
      if (conf['button'] instanceof Element) {
        this.button = conf['button'];
      } else {
        this.button = document.querySelector(conf['button']);
      }
    }

    this.controller = typeof conf['controller'] != 'undefined' ? conf['controller'] : null;

    this.validationController = typeof conf['validationController'] != 'undefined' ? conf['validationController'] : null;

    this.paypal = conf['paypal'] === undefined ? null : conf['paypal'];

    this.messages = conf['messages'] === undefined ? [] : conf['messages'];

    this.isMoveButtonAtEnd = conf['isMoveButtonAtEnd'] === undefined ? [] : conf['isMoveButtonAtEnd'];
};

ApmButton.prototype.initButton = function() {
  if (this.paypal == null) {
    return;
  }

  if (this.isMoveButtonAtEnd) {
    let paypalButtonsContainer = this.getPaypalButtonsContainer();
    paypalButtonsContainer.append(this.button);
    this.button.style.display = 'none';
  }

  let paypalButton = this.paypal.Buttons({
    fundingSource: this.method,

    createOrder: function(data, actions) {
      return this.getIdOrder();
    }.bind(this),

    onApprove: function(data, actions) {
      this.sendData(data);
    }.bind(this),

  });

  if (paypalButton.isEligible() == false) {
    let buttonContainer = this.button instanceof Element ? this.button : document.querySelector(this.button);

    if (buttonContainer instanceof Element) {
      buttonContainer.appendChild(
        Tools.getAlert(
          this.messages['NOT_ELIGIBLE'] === undefined ? 'Payment method is not eligible' : this.messages['NOT_ELIGIBLE'],
          'danger')
      );
    }

    return;
  }

  paypalButton.render(this.button);

  Tools.disableTillConsenting(
    this.button,
    document.getElementById('conditions_to_approve[terms-and-conditions]')
  );
};

ApmButton.prototype.getIdOrder = function() {

  let url = new URL(this.controller);
  url.searchParams.append('ajax', '1');
  url.searchParams.append('action', 'CreateOrder');

  return fetch(url.toString(), {
    method: 'post',
    headers: {
      'content-type': 'application/json;charset=utf-8'
    },
    body: JSON.stringify({page: 'cart', apmMethod: this.method, addAddress: true})
  }).then(function(res) {
    return res.json();
  }).then(function(data) {
    if (data.success) {
      return data.idOrder;
    }
  });
};

ApmButton.prototype.sendData = function(data) {
  let form = document.createElement('form');
  let input = document.createElement('input');

  input.name = "paymentData";
  input.value = JSON.stringify(data);

  form.method = "POST";
  form.action = this.validationController;

  form.appendChild(input);
  document.body.appendChild(form);
  form.submit();
};

ApmButton.prototype.hideElementTillPaymentOptionChecked = function(paymentOptionSelector, hideElementSelector) {
  Tools.hideElementTillPaymentOptionChecked(paymentOptionSelector, hideElementSelector);
};

ApmButton.prototype.showElementIfPaymentOptionChecked = function(checkElementSelector, showElementSelector) {
  Tools.showElementIfPaymentOptionChecked(checkElementSelector, showElementSelector);
};

ApmButton.prototype.addMarkTo = function(element, styles = {}) {
  if (element instanceof Element == false) {
    return;
  }

  const markContainer = document.createElement('span');

  for (let key in styles) {
    markContainer.style[key] = styles[key];
  }

  markContainer.setAttribute('paypal-mark-container', '');
  element.appendChild(markContainer);

  const mark = this.paypal.Marks({
    fundingSource: this.method
  });

  if (mark.isEligible()) {
    mark.render(markContainer);
  }
};

ApmButton.prototype.getPaypalButtonsContainer = function() {
  if (document.querySelector('#paypal-buttons')) {
    return document.querySelector('#paypal-buttons');
  }

  var container = document.createElement('div');
  container.id = 'paypal-buttons';
  container.style = 'width: 300px';

  document.querySelector('#payment-confirmation').after(container);

  return container;
};

window.ApmButton = ApmButton;


