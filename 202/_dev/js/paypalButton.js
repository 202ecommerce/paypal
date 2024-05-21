/*
 * Since 2007 PayPal
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
 *  @author Since 2007 PayPal
 *  @author 202 ecommerce <tech@202-ecommerce.com>
 *  @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 *  @copyright PayPal
 *
 */
// init incontext

import {Tools} from './tools.js';

export const PaypalButton = function(conf) {

  if (conf['paypal'] === undefined) {
    throw new Error('Missing parameter "paypal"');
  }
  if (conf['controller'] === undefined) {
    throw new Error('Missing parameter "controller"');
  }
  if (conf['validationController'] === undefined) {
    throw new Error('Missing parameter "validationController"');
  }
  if (conf['button'] !== undefined) {
    if (conf['button'] instanceof Element) {
      this.button = conf['button'];
    } else {
      this.button = document.querySelector(conf['button']);
    }
  }

  this.paypal = conf['paypal'];
  this.controller = conf['controller'];
  this.validationController = conf['validationController'];
  this.method = conf['method'] === undefined ? this.paypal.FUNDING.PAYPAL : conf['method'];
  this.page = conf['page'] === undefined ? 'cart' : conf['page'];
  this.messages = conf['messages'] === undefined ? [] : conf['messages'];
  this.style = conf['style'] === undefined ? [] : conf['style'];
  this.disableTillConsenting = conf['disableTillConsenting'] === undefined ? true : conf['disableTillConsenting'];
  this.isMoveButtonAtEnd = conf['isMoveButtonAtEnd'] === undefined ? false : conf['isMoveButtonAtEnd'] === '1';
  this.isAddAddress = conf['isAddAddress'] === undefined ? false : conf['isAddAddress'];
};

PaypalButton.prototype.initButton = function () {

  const paypalButton = this.paypal.Buttons({
    fundingSource: this.method,

    style: this.style,

    createOrder: (data, actions) => {
      return this.getIdOrder();
    },

    onApprove: (data, actions) => {
      this.sendData(data);
    },

  });

  if (paypalButton.isEligible() === false) {
    this.button.appendChild(
      Tools.getAlert(
        this.messages['NOT_ELIGIBLE'] === undefined ? 'Payment method is not eligible' : this.messages['NOT_ELIGIBLE'],
        'danger')
    );

    return;
  }

  paypalButton.render(this.button);

  if (this.disableTillConsenting) {
    Tools.disableTillConsenting(
      this.button,
      document.getElementById('conditions_to_approve[terms-and-conditions]')
    );
  }
  if (this.isMoveButtonAtEnd) {
    const paypalButtonsContainer = this.getPaypalButtonsContainer();
    paypalButtonsContainer.append(this.button);
    this.button.style.display = 'none';
  }
};

PaypalButton.prototype.getIdOrder = function () {
  const url = new URL(this.controller);
  const postData = {
    page: this.page
  };
  url.searchParams.append('ajax', '1');
  url.searchParams.append('action', 'CreateOrder');

  if (this.needSaveAccount()) {
    postData['savePaypalAccount'] = '1';
  }
  if (this.isAddAddress) {
    postData['addAddress'] = true;
  }

  return fetch(url.toString(), {
    method: 'post',
    headers: {
      'content-type': 'application/json;charset=utf-8'
    },
    body: JSON.stringify(postData)
  }).then(resonse => resonse.json())
    .then((data) => {
    if (data.success) {
      return data.idOrder;
    }
  });
};

PaypalButton.prototype.sendData = function(data) {
  const form = document.createElement('form');
  const input = document.createElement('input');

  input.name = "paymentData";
  input.value = JSON.stringify(data);

  form.method = "POST";
  form.action = this.validationController;

  form.appendChild(input);
  document.body.appendChild(form);
  form.submit();
};

PaypalButton.prototype.getPaypalButtonsContainer = function() {
   if (document.querySelector('#paypal-buttons')) {
    return document.querySelector('#paypal-buttons');
   }

   var container = document.createElement('div');
   container.id = 'paypal-buttons';
   container.style = 'width: 300px';

   document.querySelector('#payment-confirmation').after(container);

   return container;
};

PaypalButton.prototype.hideElementTillPaymentOptionChecked = function(paymentOptionSelector, hideElementSelector) {
  Tools.hideElementTillPaymentOptionChecked(paymentOptionSelector, hideElementSelector);
};

PaypalButton.prototype.showElementIfPaymentOptionChecked = function(checkElementSelector, showElementSelector) {
  Tools.showElementIfPaymentOptionChecked(checkElementSelector, showElementSelector);
};

PaypalButton.prototype.needSaveAccount = function() {
  const vaultCheckbox = document.querySelector('[save-paypal-account]');

  if (vaultCheckbox instanceof HTMLInputElement) {
    return vaultCheckbox.checked;
  } else {
    return false;
  }
}

PaypalButton.prototype.addMarkTo = function(element, styles = {}) {
        if (element instanceof Element == false) {
          return;
        }

        let markContainer = element.querySelector('[paypal-mark-container]');

        if (markContainer instanceof Element) {
          markContainer.innerHTML = '';
        } else {
          markContainer = document.createElement('span');
          markContainer.setAttribute('paypal-mark-container', '');
        }

        for (let key in styles) {
          markContainer.style[key] = styles[key];
        }

        element.appendChild(markContainer);

        const mark = this.paypal.Marks({
          fundingSource: this.paypal.FUNDING.PAYPAL
        });

        if (mark.isEligible()) {
          mark.render(markContainer);
        }
}

window.PaypalButton = PaypalButton;
