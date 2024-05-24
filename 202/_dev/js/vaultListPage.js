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


document.addEventListener('click', (event) => {
  if (event.target instanceof HTMLElement) {
    if (!event.target.hasAttribute('remove-payment-source')) {
      return;
    }

    const account = event.target.getAttribute('account');
    const id = event.target.getAttribute('id-paypal-vaulting');
    const url = new URL(document.location.href);

    if (!id) {
      return;
    }
    if (!confirm(`Do you confirm deleting ${account} ?`)) {
      return;
    }

    url.searchParams.append('ajax', '1');
    url.searchParams.append('action', 'RemovePaypalVaulting');
    url.searchParams.append('id', event.target.getAttribute('id-paypal-vaulting'));

    fetch(url)
      .then(response => response.json())
      .then((response) => {
        if (response.success) {
          const paymentSource = event.target.closest('[payment-source]');
          const mainSection = document.getElementById('main');

          if (paymentSource instanceof HTMLElement) {
            paymentSource.remove();
          }

          if (mainSection instanceof HTMLElement) {
            const alert = document.createElement('div');
            let html = '<div class="alert alert-info">Your saved account has been deleted';
            html += '<button type="button" class="close" data-dismiss="alert" aria-label="Close">';
            html += '<span aria-hidden="true">&times;</span>';
            html += '</button></div>';
            alert.innerHTML = html;

            if (mainSection.firstChild) {
              mainSection.insertBefore(alert, mainSection.firstChild);
            } else {
              mainSection.appendChild(alert);
            }
          }
        }
      });
  }



});
