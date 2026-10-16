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

import 'bootstrap/js/src/popover';

// init in-context
window.addEventListener('load', () => {
  initPaypalInfo();

  if (typeof prestashop === 'undefined') {
    return;
  }

  // The one-page checkout loads and re-renders the payment options by ajax
  prestashop.on('opcPaymentMethodsLoading', hidePopups);
  prestashop.on('opcPaymentMethodsUpdated', initPaypalInfo);
});

const initPaypalInfo = () => {
  const popovers = [];

  // Insert paypal info block after option name
  $("input[name='payment-option'][data-module-name^='paypal']").each((index, paymentOption) => {
    let dataPaypalInfo = $(`#${paymentOption.id}-additional-information [data-paypal-info]`);

    if (dataPaypalInfo.length == 0 || dataPaypalInfo.is('[data-paypal-info-initialized]')) {
      return;
    }

    let info = document.querySelector(`#${paymentOption.id}-additional-information [data-pp-info]`);

    if (info instanceof Element) {
      dataPaypalInfo.find('[data-paypal-info-popover]').attr('data-content', info.outerHTML);
      info.remove();
    }

    dataPaypalInfo.attr('data-paypal-info-initialized', '1');
    dataPaypalInfo.insertAfter($(`#${paymentOption.id}-container label`).first());
    dataPaypalInfo.show();
    popovers.push(...dataPaypalInfo.find('[data-paypal-info-popover]').toArray());
  });

  if (popovers.length == 0) {
    return;
  }

  // Show block with paypal payment benefits
  try {
    let configs = getConfigPopup();
    // Pass the options explicitly: Bootstrap 5 (e.g. Hummingbird theme) replaces $.fn.popover
    // and ignores the Bootstrap 4 data-content/data-html/data-container attributes
    $(popovers).each((index, popover) => {
      $(popover).popover({
        placement: configs.popoverPlacement,
        trigger: configs.popoverTrigger,
        animation: false,
        html: true,
        container: 'body',
        content: popover.getAttribute('data-content'),
      });
    }).on('click', (e) => e.preventDefault());
  } catch (e) {
    console.error(e);
  }

  if ($(window).width() > 991) {
    hoverPopup($(popovers));
  }
};

const hidePopups = () => {
  try {
    $('[data-paypal-info-popover]').popover('hide');
  } catch (e) {
    console.error(e);
  }

  $('body').removeClass('pp-popover');
};

const getConfigPopup = () => {
  let placement = 'right',
    trigger = 'hover focus';
  if ($(window).width() < 992) {
    placement = 'bottom';
    trigger = 'click';
  }
  return {
    popoverPlacement: placement,
    popoverTrigger: trigger
  }
}

const hoverPopup = (popovers) => {
  popovers.on('shown.bs.popover', (e) => {
    const icon = e.currentTarget.querySelector('i');
    icon.innerText = 'cancel';
    $('body').addClass('pp-popover');
  });

  popovers.on('hidden.bs.popover', (e) => {
    const icon = e.currentTarget.querySelector('i');
    icon.innerText = 'info';
    $('body').removeClass('pp-popover');
  });
}
