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
import { SetupAdmin } from './adminSetup.js';

var HelpAdmin = {
  init() {

    // Check credentials (TLS version, country, enabling SSL)
    $('#ckeck_requirements').click(() => {
      HelpAdmin.checkCredentials();
    });

    // Handle click on "Install Prestashop Checkout" button
    $('.install-ps-checkout').click(() => {
      SetupAdmin.psCheckoutHandleAction('install');
    })
  },

  checkCredentials() {
    $.ajax({
      url: controllerUrl,
      type: 'POST',
      dataType: 'JSON',
      data: {
        ajax: true,
        action: 'CheckCredentials',
      },
      success(response) {
        let alert; let
          typeAlert;

        // Remove error messages
        $('.action_response').html('');
        if (response.success == true) {
          typeAlert = 'success';
        } else {
          typeAlert = 'danger';
        }
        for (const key in response.message) {
          alert = HelpAdmin.getAlert(response.message[key], typeAlert);
          $(alert).appendTo('.action_response');
        }
      },
    });
  },

  // Show error message
  getAlert(message, typeAlert) {
    const alert = document.createElement('div');
    let messageNode = document.createElement('div');
    messageNode.innerHTML = message;
    alert.className = `alert alert-${typeAlert}`;
    alert.appendChild(messageNode);
    return alert;
  },
};

document.addEventListener('DOMContentLoaded', () => {
  HelpAdmin.init();
});
