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
import intlTelInput from 'intl-tel-input';

export const Tools = {
  // Show error message
  getAlert(message, typeAlert) {
    const alert = document.createElement('div');
    let messageNode = document.createElement('div');
    messageNode.innerText = message;
    alert.className = `alert alert-${typeAlert}`;
    alert.appendChild(messageNode);
    return alert;
  },

  hideConfiguration(name) {
    let selector = `[name="${name}"]`;
    let configuration = $(selector);
    let formGroup = configuration.closest('.form-group');

    formGroup.hide();
  },

  showConfiguration(name) {
    let selector = `[name="${name}"]`;
    let configuration = $(selector);
    let formGroup = configuration.closest('.form-group');

    formGroup.show();
  },

  isVisible(el) {
    const style = window.getComputedStyle(el);
    return  style.width !== "0" &&
      style.height !== "0" &&
      style.opacity !== "0" &&
      style.display!=='none' &&
      style.visibility!== 'hidden';
  },

  disableTillConsenting(disabledElement, checkBox) {
    if (disabledElement instanceof Element == false) {
      return;
    }

    if (checkBox instanceof Element == false) {
      return;
    }

    if (checkBox.checked) {
      Tools.enable(disabledElement);
    } else {
      Tools.disable(disabledElement);
    }

    checkBox.addEventListener('click', function() {
      Tools.hideElementTillChecked();
      Tools.showElementsIfChecked();
      if (checkBox.checked) {
        Tools.enable(disabledElement);
      } else {
        Tools.disable(disabledElement);
      }
    });

    $('.payment-option, .payment__option').click(function() {
      if (checkBox.checked) {
        Tools.enable(disabledElement);
      } else {
        Tools.disable(disabledElement);
      }
    });
  },

  // Order confirmation button of the default checkout and of the one-page checkout
  paymentConfirmationSelector: '#payment-confirmation, #opc-payment-confirmation',

  getPaypalButtonsContainer() {
    if (document.querySelector('#paypal-buttons')) {
      return document.querySelector('#paypal-buttons');
    }

    const paymentConfirmation = document.querySelector(Tools.paymentConfirmationSelector);

    if (paymentConfirmation instanceof Element == false) {
      return null;
    }

    const container = document.createElement('div');
    container.id = 'paypal-buttons';
    container.style = 'width: 300px';

    paymentConfirmation.after(container);

    return container;
  },

  // Moves a payment step button after the order confirmation button. The payment options can be
  // re-rendered by ajax (e.g. one-page checkout): the button of a previous render is dropped.
  moveButtonAtEnd(button, previousButtonSelector) {
    const paypalButtonsContainer = Tools.getPaypalButtonsContainer();

    if (paypalButtonsContainer instanceof Element == false) {
      return false;
    }

    if (previousButtonSelector) {
      paypalButtonsContainer.querySelectorAll(previousButtonSelector).forEach(function(previousButton) {
        if (previousButton !== button) {
          previousButton.remove();
        }
      });
    }
    paypalButtonsContainer.append(button);
    button.style.display = 'none';

    return true;
  },

  disable(element) {
    if (element instanceof Element == false) {
      return;
    }

    element.style.pointerEvents = 'none';
    element.style.opacity = '0.5';
  },

  enable(element) {
    if (element instanceof Element == false) {
      return;
    }

    element.style.pointerEvents = '';
    element.style.opacity = '1';
  },

  hideElementTillPaymentOptionChecked(checkElementSelector, hideElementSelector) {
    const checkElement = document.querySelector(checkElementSelector);
    const hideElement = document.querySelector(hideElementSelector);

    if (checkElement instanceof Element == false) {
      return;
    }

    if (hideElement instanceof Element == false) {
      return;
    }

    if ('paypalToolsHiddenElemenList' in window == false) {
      window.paypalToolsHiddenElemenList = {};
    }

    window.paypalToolsHiddenElemenList[hideElementSelector] = Tools.registerCheckElement(
      window.paypalToolsHiddenElemenList[hideElementSelector],
      checkElement
    );

    if (Tools.listenPaymentOptions(checkElement, Tools.hideElementTillChecked)) {
      Tools.hideElementTillChecked();
    }
  },

  showElementIfPaymentOptionChecked(checkElementSelector, showElementSelector) {
    const checkElement = document.querySelector(checkElementSelector);
    const showElement = document.querySelector(showElementSelector);

    if (checkElement instanceof Element == false) {
      return;
    }

    if (showElement instanceof Element == false) {
      return;
    }

    if ('paypalToolsShowElemenList' in window == false) {
      window.paypalToolsShowElemenList = {};
    }

    window.paypalToolsShowElemenList[showElementSelector] = Tools.registerCheckElement(
      window.paypalToolsShowElemenList[showElementSelector],
      checkElement
    );

    if (Tools.listenPaymentOptions(checkElement, Tools.showElementsIfChecked)) {
      Tools.showElementsIfChecked();
    }
  },

  // Payment options can be re-rendered by ajax (e.g. one-page checkout): forget the removed ones
  registerCheckElement(checkElements, checkElement) {
    const list = (checkElements || []).filter(function(elem) {
      return elem.isConnected && elem !== checkElement;
    });
    list.push(checkElement);

    return list;
  },

  listenPaymentOptions(checkElement, listener) {
    const options = checkElement.closest('.payment-options, .payment__list, .payment-options__list, #opc-payment-methods');

    if (options instanceof Element == false) {
      return false;
    }

    // Registering the same listener twice on the same element is a no-op
    options.addEventListener('input', listener);

    return true;
  },

  showElementsIfChecked() {
    if (window.paypalToolsShowElemenList === undefined) {
      return;
    }
    Object.keys(window.paypalToolsShowElemenList).forEach(function(showElementSelector) {
      let isShow = false;
      window.paypalToolsShowElemenList[showElementSelector].forEach(function(elem) {
        if (elem.checked) {
          isShow = true;
        }
      });
      const showElement = document.querySelector(showElementSelector);

      if (showElement instanceof Element == false) {
        return;
      }

      if (isShow) {
        showElement.style.display = 'block';
      } else {
        showElement.style.display = 'none';
      }
    });
  },

  hideElementTillChecked() {
    if (window.paypalToolsHiddenElemenList === undefined) {
      return;
    }
    Object.keys(window.paypalToolsHiddenElemenList).forEach(function(hideElementSelector) {
      let isHide = false;
      const hideElement = document.querySelector(hideElementSelector);

      if (hideElement instanceof Element == false) {
        return;
      }

      window.paypalToolsHiddenElemenList[hideElementSelector].forEach(function(elem) {
        if (elem.checked) {
          isHide = true;
        }
      });

      if (isHide) {
        hideElement.style.visibility = 'hidden';
      } else {
        hideElement.style.visibility = 'initial';
      }
    });
  },

  initPhoneInput(input, options = {}) {
    if (false == input instanceof Element) {
      return false;
    }

    input.addEventListener('input', function(e) {
      e.target.value = e.target.value.replace('+', '')
    });

    return intlTelInput(input, options);
  },
};

window.PaypalTools = Tools;
let event = new Event('paypal-tools-loaded');
document.dispatchEvent(event);
