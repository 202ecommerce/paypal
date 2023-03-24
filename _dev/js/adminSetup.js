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

// Import functions for scrolling effect to necessary block on click
import {hoverConfig, hoverTabConfig} from './functions.js';
import {Onboarding} from "./Onboarding";

export  const SetupAdmin = {
  init() {
    // Disconnect from braintree account
    $('#logoutAccount').on('click', (event) => {
      SetupAdmin.logoutAccount();
    });

    // Connect to braintree account
    $('#confirmCredentials').click((event) => {
      $(event.currentTarget).closest('form').submit();
    });

    // Check credentials (TLS version, country, enabling SSL)
    $(document).on('click', '#btn-check-requirements', () => {
      SetupAdmin.checkRequirements();
    });

    // Scroll to necessary block
    $('[data-pp-link-settings]').on('click', (e) => {
      e.preventDefault();
      const el = $(e.target.attributes.href.value);

      if (el.length) {
        hoverConfig(el);
      } else {
        hoverTabConfig();
      }
    });

    // Remove effect after leaving cursor from the block
    $('.defaultForm').on('mouseleave', (e) => {
      $(e.currentTarget).removeClass('pp-settings-link-on');
    });

    // Handle click on "Install Prestashop Checkout" button
    $('.ps-checkout-info').on('click', (e) => {
      let action = e.target.getAttribute('data-action');
      SetupAdmin.psCheckoutHandleAction(action);
    });

    $(document).on('contextmenu', '[data-paypal-button]', (e) => {
      e.preventDefault();
    });

    window.onboardCallback = Onboarding.handleResponse;

    $('[data-update-rounding-settings]').on('click', (e) => {
      SetupAdmin.updateRoundingSettings(e);
    });

    $('[data-show-rounding-alert]').on('click', (e) => {
      let $alert = $('[data-rounding-alert]');
      $alert.removeClass('hidden');
      let offset = $alert.offset().top - $('.page-head').height() - 45;
      $('html, body').animate({scrollTop: offset}, 500);
    });
  },

  logoutAccount() {
    $.ajax({
      url: controllerUrl,
      type: 'POST',
      data: {
        ajax: true,
        action: 'logOutAccount',
      },
      success(response) {
        if (response.status) {
          document.location = response.redirectUrl;
        }
      },
    });

  },

  checkRequirements() {
    $.ajax({
      url: controllerUrl,
      type: 'POST',
      data: {
        ajax: true,
        action: 'CheckCredentials',
      },
      success(response) {
        $('#btn-check-requirements').closest('.status-block-container').html(response);
      },
    });
  },

  psCheckoutHandleAction(action) {
    if (action != null) {
      $.ajax({
        url: controllerUrl,
        type: 'POST',
        data: {
          ajax: true,
          action: 'HandlePsCheckoutAction',
          actionHandled: action,
        },
        success(response) {
          if (response.redirect) {
            window.open(response.url, '_blank');
          }
        },
      });
    }

  },

  updateRoundingSettings(el) {
    $.ajax({
      url: controllerUrl,
      type: 'POST',
      data: {
        ajax: true,
        action: 'UpdateRoundingSettings',
      },
      success(response) {
        let $alert = $(el.currentTarget).closest('[data-rounding-alert]');
        if ($alert.length > 0) {
          $alert.removeClass('alert-warning').addClass('alert-success');
          $alert.html(response);
          setTimeout(() => $alert.remove(), 5000);
        }
      },
    });
  },

};

window.addEventListener('load', () => SetupAdmin.init());

// Wait until window is loaded
$(window).on('load', () => $('[data-paypal-button]').removeClass('spinner-button'));
