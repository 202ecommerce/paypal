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
import intlTelInput from 'intl-tel-input';

export const Tools = {
  // Show error message
  getAlert(message, typeAlert) {
    const alert = document.createElement('div');
    let messageNode = document.createElement('div');
    messageNode.innerHTML = message;
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

    Tools.disable(disabledElement);

    checkBox.addEventListener('click', function() {
      if (checkBox.checked) {
        Tools.enable(disabledElement);
      } else {
        Tools.disable(disabledElement);
      }
    });

    $('.payment-option').click(function() {
      if (checkBox.checked) {
        Tools.enable(disabledElement);
      } else {
        Tools.disable(disabledElement);
      }
    });
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

    if (hideElementSelector in window.paypalToolsHiddenElemenList) {
      window.paypalToolsHiddenElemenList[hideElementSelector].push(checkElement);
      return;
    }

    window.paypalToolsHiddenElemenList[hideElementSelector] = [checkElement];
    const options = checkElement.closest('.payment-options');

    if (options instanceof Element == false) {
      return;
    }

    options.addEventListener('input', function(event) {
      let isHide = false;
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
    })
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

    if (showElementSelector in window.paypalToolsShowElemenList) {
      window.paypalToolsShowElemenList[showElementSelector].push(checkElement);
      return;
    }

    window.paypalToolsShowElemenList[showElementSelector] = [checkElement];
    const options = checkElement.closest('.payment-options');

    if (options instanceof Element == false) {
      return;
    }

    options.addEventListener('input', function(event) {
      let isShow = false;
      window.paypalToolsShowElemenList[showElementSelector].forEach(function(elem) {
        if (elem.checked) {
          isShow = true;
        }
      });

      if (isShow) {
        showElement.style.display = 'block';
      } else {
        showElement.style.display = 'none';
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
