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
import { SetupAdmin } from './adminSetup.js';
import {Tools} from './tools.js';

var CustomizeCheckout = {
  init() {
    // Scroll to necessary block
    $('[data-pp-link-settings]').on('click', (e) => {
      let el = $(e.target.attributes.href.value);
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

    CustomizeCheckout.checkConfigurations();
    CustomizeCheckout.updateHookPreview();
    $('input').change(CustomizeCheckout.checkConfigurations);
    $('select').change(CustomizeCheckout.checkConfigurations);
    $(document).on('click', '[toggle-style-configuration]', function (e) {
      CustomizeCheckout.toggleStyleConfiguration(e);
      CustomizeCheckout.updatePreviewButton(e);
      CustomizeCheckout.updateColorDescription(e);
    });
    $(document).on('change', '[customize-style-shortcut-container]', CustomizeCheckout.updatePreviewButton);
    $(document).on('change', '[data-type="color"]', CustomizeCheckout.updateColorDescription);
    $(document).on('change', '.pp-select-preview-container', CustomizeCheckout.updateHookPreview);
    $(document).on('change', '[data-type="height"]', CustomizeCheckout.checkHeight);
    $(document).on('change', '[data-type="width"]', CustomizeCheckout.checkWidth);

    if (typeof sectionSelector !== 'undefined') {
      CustomizeCheckout.scrollTo(sectionSelector);
    }
  },

    checkConfigurations() {
      const paypalEcEnabled = $('input[name="paypal_mb_ec_enabled"]');
      const paypalApiCard = $('input[name="paypal_api_card"]');
      const EcOptions = [
          'paypal_express_checkout_in_context',
          'paypal_express_checkout_shortcut_cart',
          'paypal_express_checkout_shortcut',
          'paypal_express_checkout_shortcut_signup',
          'paypal_api_advantages',
          'paypal_config_brand',
          'paypal_config_logo'
      ];
      const MbCardOptions = [
          'paypal_vaulting',
          'paypal_merchant_installment'
      ];
      const customOrderStatus = $('[name="paypal_customize_order_status"]');
      const statusOptions = [
          'paypal_os_refunded',
          'paypal_os_canceled',
          'paypal_os_accepted',
          'paypal_os_capture_canceled',
          'paypal_os_waiting_validation',
          'paypal_os_accepted_two',
          'paypal_os_processing',
          'paypal_os_validation_error',
          'paypal_os_refunded_paypal'
      ];
      const customShortcutStyle = document.querySelector('[name="PAYPAL_EXPRESS_CHECKOUT_CUSTOMIZE_SHORTCUT_STYLE"]');
      const shortcutLocationProduct = $('[name="paypal_express_checkout_shortcut"]');
      const shortcutLocationCart = $('[name="paypal_express_checkout_shortcut_cart"]');
      const shortcutLocationSignup = $('[name="paypal_express_checkout_shortcut_signup"]');
      const showShortcutOnProductPage = document.querySelector('[name="paypal_express_checkout_shortcut"]');
      const displayModeProductPage = document.querySelector('[name="PAYPAL_EXPRESS_CHECKOUT_DISPLAY_MODE_PRODUCT"]');
      const showShortcutOnCartPage = document.querySelector('[name="paypal_express_checkout_shortcut_cart"]');
      const displayModeCartPage = document.querySelector('[name="PAYPAL_EXPRESS_CHECKOUT_DISPLAY_MODE_CART"]');
      const showShortcutOnSignupPage = document.querySelector('[name="paypal_express_checkout_shortcut_signup"]');
      const displayModeSignupPage = document.querySelector('[name="PAYPAL_EXPRESS_CHECKOUT_DISPLAY_MODE_SIGNUP"]');


      // Show the product page display configurations of a shortcut if need
      if (showShortcutOnProductPage.checked && customShortcutStyle.checked) {
        $('[data-section-customize-mode-product]').closest('.form-group').show();
        CustomizeCheckout.showConfiguration('PAYPAL_EXPRESS_CHECKOUT_DISPLAY_MODE_PRODUCT');
        CustomizeCheckout.showConfiguration('PAYPAL_EXPRESS_CHECKOUT_SHORTCUT_STYLE_COLOR_PRODUCT');

        if (displayModeProductPage.value === '1') {
          CustomizeCheckout.showConfiguration('PAYPAL_EXPRESS_CHECKOUT_SHORTCUT_HOOK_PRODUCT');
          CustomizeCheckout.hideConfiguration('productPageWidgetCode');
        } else if (displayModeProductPage.value === '2') {
          CustomizeCheckout.hideConfiguration('PAYPAL_EXPRESS_CHECKOUT_SHORTCUT_HOOK_PRODUCT');
          CustomizeCheckout.showConfiguration('productPageWidgetCode');
        }
      } else {
        $('[data-section-customize-mode-product]').closest('.form-group').hide();
        CustomizeCheckout.hideConfiguration('PAYPAL_EXPRESS_CHECKOUT_DISPLAY_MODE_PRODUCT');
        CustomizeCheckout.hideConfiguration('productPageWidgetCode');
        CustomizeCheckout.hideConfiguration('PAYPAL_EXPRESS_CHECKOUT_SHORTCUT_HOOK_PRODUCT');
        CustomizeCheckout.hideConfiguration('PAYPAL_EXPRESS_CHECKOUT_SHORTCUT_STYLE_COLOR_PRODUCT');
      }

      // Show the cart page display configurations of a shortcut if need
      if (showShortcutOnCartPage.checked  && customShortcutStyle.checked) {
        $('[data-section-customize-mode-cart]').closest('.form-group').show();
        CustomizeCheckout.showConfiguration('PAYPAL_EXPRESS_CHECKOUT_DISPLAY_MODE_CART');
        CustomizeCheckout.showConfiguration('PAYPAL_EXPRESS_CHECKOUT_SHORTCUT_STYLE_COLOR_CART');

        if (displayModeCartPage.value === '1') {
          CustomizeCheckout.showConfiguration('PAYPAL_EXPRESS_CHECKOUT_SHORTCUT_HOOK_CART');
          CustomizeCheckout.hideConfiguration('cartPageWidgetCode');
        } else if (displayModeCartPage.value === '2') {
          CustomizeCheckout.hideConfiguration('PAYPAL_EXPRESS_CHECKOUT_SHORTCUT_HOOK_CART');
          CustomizeCheckout.showConfiguration('cartPageWidgetCode');
        }
      } else {
        $('[data-section-customize-mode-cart]').closest('.form-group').hide();
        CustomizeCheckout.hideConfiguration('PAYPAL_EXPRESS_CHECKOUT_DISPLAY_MODE_CART');
        CustomizeCheckout.hideConfiguration('cartPageWidgetCode');
        CustomizeCheckout.hideConfiguration('PAYPAL_EXPRESS_CHECKOUT_SHORTCUT_HOOK_CART');
        CustomizeCheckout.hideConfiguration('PAYPAL_EXPRESS_CHECKOUT_SHORTCUT_STYLE_COLOR_CART');
      }

      // Show the signup page display configurations of a shortcut if need
      if (showShortcutOnSignupPage.checked  && customShortcutStyle.checked) {
        $('[data-section-customize-mode-signup]').closest('.form-group').show();
        CustomizeCheckout.showConfiguration('PAYPAL_EXPRESS_CHECKOUT_DISPLAY_MODE_SIGNUP');
        CustomizeCheckout.showConfiguration('PAYPAL_EXPRESS_CHECKOUT_SHORTCUT_STYLE_COLOR_SIGNUP');

        if (displayModeSignupPage.value === '1') {
          CustomizeCheckout.showConfiguration('PAYPAL_EXPRESS_CHECKOUT_SHORTCUT_HOOK_SIGNUP');
          CustomizeCheckout.hideConfiguration('signupPageWidgetCode');
        } else if (displayModeSignupPage.value === '2') {
          CustomizeCheckout.hideConfiguration('PAYPAL_EXPRESS_CHECKOUT_SHORTCUT_HOOK_SIGNUP');
          CustomizeCheckout.showConfiguration('signupPageWidgetCode');
        }
      } else {
        $('[data-section-customize-mode-signup]').closest('.form-group').hide();
        CustomizeCheckout.hideConfiguration('PAYPAL_EXPRESS_CHECKOUT_DISPLAY_MODE_SIGNUP');
        CustomizeCheckout.hideConfiguration('signupPageWidgetCode');
        CustomizeCheckout.hideConfiguration('PAYPAL_EXPRESS_CHECKOUT_SHORTCUT_HOOK_SIGNUP');
        CustomizeCheckout.hideConfiguration('PAYPAL_EXPRESS_CHECKOUT_SHORTCUT_STYLE_COLOR_SIGNUP');
      }

      if (paypalEcEnabled.length > 0 ) {
        if (paypalEcEnabled.prop('checked') == true) {
          EcOptions.forEach(CustomizeCheckout.showConfiguration);
          $('.message-context').show();
        } else {
          EcOptions.forEach(CustomizeCheckout.hideConfiguration);
          $('.message-context').hide();
        }
      }

      if (paypalApiCard.length > 0) {
        if (paypalApiCard.prop('checked') == true) {
          MbCardOptions.forEach(CustomizeCheckout.showConfiguration);
        } else {
          MbCardOptions.forEach(CustomizeCheckout.hideConfiguration);
        }
      }

      if (customOrderStatus.length > 0) {
        if (customOrderStatus.prop('checked') == true) {
          statusOptions.forEach(CustomizeCheckout.showConfiguration);
          $('.advanced-help-message').show();
        } else {
          statusOptions.forEach(CustomizeCheckout.hideConfiguration);
          $('.advanced-help-message').hide();
        }
      }

      // Show the alert if the customize shortcut style is active and any shortcut location is not active
      if (customShortcutStyle !== null) {
        if (
          customShortcutStyle.checked === true
          && shortcutLocationProduct.prop('checked') === false
          && shortcutLocationCart.prop('checked') === false
          && shortcutLocationSignup.prop('checked') === false
        ) {
            $('.shortcut-customize-style-alert').closest('.form-group').show();
            $('.shortcut-customize-style-alert').removeClass('hidden');
        } else {
            $('.shortcut-customize-style-alert').closest('.form-group').hide();
            $('.shortcut-customize-style-alert').addClass('hidden');
        }
      }
    },

    // Hide block while switch inactive
    hideConfiguration(name) {
        let selector = `[name="${name}"]`;
        let configuration = $(selector);
        let formGroup = configuration.closest('.form-group');

        formGroup.hide();
    },

    // Show block while switch is active
    showConfiguration(name) {
        let selector = `[name="${name}"]`;
        let configuration = $(selector);
        let formGroup = configuration.closest('.form-group');

        formGroup.show();
    },

  toggleStyleConfiguration(e) {
    var button = $(e.target);
    var configurations = button.closest('[customize-style-shortcut-container]').find('[configuration-section]');
    var preview = button.closest('[customize-style-shortcut-container]').find('[preview-section]');

    if (configurations.hasClass('hidden')) {
      button.find('i').addClass('icon-remove');
      button.find('i').removeClass('icon-edit');
      configurations.removeClass('hidden');
      preview.removeClass('invisible');
    } else {
      button.find('i').removeClass('icon-remove');
      button.find('i').addClass('icon-edit');
      configurations.addClass('hidden');
      preview.addClass('invisible');
    }
  },

  updatePreviewButton(e) {
    var container = $(e.target).closest('[customize-style-shortcut-container]');
    var preview = container.find('[preview-section]').find('[button-container]');
    var configurations = container.find('[configuration-section]');
    var color = configurations.find('[data-type="color"]').val();
    var shape = configurations.find('[data-type="shape"]').val();
    var label = configurations.find('[data-type="label"]').val();
    var width = configurations.find('[data-type="width"]').val();
    var height = configurations.find('[data-type="height"]').val();

    $.ajax({
      url: controllerUrl,
      type: 'POST',
      dataType: 'JSON',
      data: {
        ajax: true,
        action: 'getShortcut',
        color: color,
        shape: shape,
        label: label,
        height: height,
        width: width
      },
      success(response) {
        if ('content' in response) {
          preview.html(response.content);
        }
      },
    })
  },

  updateColorDescription(e) {
    var container = $(e.target).closest('[customize-style-shortcut-container]');
    var color = container.find('[data-type="color"]').val();

    container.find('[after-select-content] [data-color]').hide();

    if (color === 'gold') {
      container.find('[after-select-content] [data-color="gold"]').show();
    } else if(color === 'blue') {
      container.find('[after-select-content] [data-color="blue"]').show();
    } else if (['silver', 'white', 'black'].includes(color)) {
      container.find('[after-select-content] [data-color="other"]').show();
    }

  },

  updateHookPreview() {
    const containers = $('.pp-select-preview-container');

    containers.each((index, container) => {
      container = $(container);
      let option = container.find('option:selected');
      let previewPath = option.attr('data-preview-image');
      let previewContainter = container.find('.pp-preview');
      previewContainter.css('background-image', `url(${previewPath})`);
    });
  },

  checkHeight(e) {
     const containerSize = $(e.target).closest('[chain-input-container]');
     const msgContainer = containerSize.closest('[field]').find('[msg-container]');
     const inputHeight = containerSize.find('[data-type="height"]');
     let height = inputHeight.val();
     let msg = null;

     if (height == 'undefined') {
       return true;
     }

     height = parseInt(height);

     if (height > 55 || height < 25) {
       msg = Tools.getAlert(inputHeight.attr('data-msg-error'), 'danger');
     }

     if (msg == null) {
       msgContainer.html('');
       return true;
     }

     msgContainer.html(msg);
     return true;
  },

  checkWidth(e) {
    const containerSize = $(e.target).closest('[chain-input-container]');
    const msgContainer = containerSize.closest('[field]').find('[msg-container]');
    const inputWidth = containerSize.find('[data-type="width"]');
    let width = inputWidth.val();
    let msg = null;

    if (width == 'undefined') {
      return true;
    }

    width = parseInt(width);

    if (width < 150) {
      msg = Tools.getAlert(inputWidth.attr('data-msg-error'), 'danger');
    }

    if (msg == null) {
      msgContainer.html('');
      return true;
    }

    msgContainer.html(msg);
    return true;
  },

  scrollTo(selector) {
    const el = $(sectionSelector);
    // Scroll to current block
    $('html, body').animate({
      scrollTop: el.offset().top - 200 + "px"
    }, 900);
  }

};

$(document).ready(() => {
  CustomizeCheckout.init();
  // Handle click on "Install Prestashop Checkout" button
  $('.install-ps-checkout').click(() => {
    SetupAdmin.psCheckoutHandleAction('install');
  })
});
