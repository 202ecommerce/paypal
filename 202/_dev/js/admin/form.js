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

import {Tools} from './../tools.js';

class Form {
  constructor(formSelector = '[data-form-configuration]') {
    this.formGroupDynamicSelector = '.form-group-dynamic';
    this.inputDynamicSelector = '.custom-control-input';
    this.inputInstallementColor = '[name="PAYPAL_INSTALLMENT_COLOR"]';
    this.controller = document.location.href;
    this.generateCredentialsEventDone = false;
  }

  init() {
    paypal.refreshMessenging = this.refreshMessenging;
    paypal.saveDataMessengingConfigurator = this.saveDataMessengingConfigurator;
    paypal.saveProcessInstallment = this.saveProcessInstallment;
    paypal.submitInstallmentForm = this.submitInstallmentForm;
    paypal.refreshForms = this.refreshForms;
    paypal.refreshMessenging();
    this.registerEvents();
  }

  registerEvents() {
    $(document).on('change', `${this.formGroupDynamicSelector} ${this.inputDynamicSelector}`, (e) => {
      const groupName = e.currentTarget.closest(this.formGroupDynamicSelector).getAttribute('group-name');
      const $formGroups = $(e.currentTarget).closest(this.formGroupDynamicSelector).siblings(`[group-name="${groupName}"]`);
      if ($(e.currentTarget).prop('checked')) {
        $formGroups.removeClass('d-none');
        if (groupName == 'PAYPAL_ENABLE_INSTALLMENT' && $('[group-name="PAYPAL_ENABLE_INSTALLMENTG"]').length) {
          $('[group-name="PAYPAL_ENABLE_INSTALLMENTG"]').removeClass('d-none');
          paypal.refreshMessenging();
        }
      } else {
        $formGroups.addClass('d-none');
        if (groupName == 'PAYPAL_ENABLE_INSTALLMENT' && $('[group-name="PAYPAL_ENABLE_INSTALLMENTG"]').length) {
          $('[group-name="PAYPAL_ENABLE_INSTALLMENTG"]').addClass('d-none');
        }
      }
    });

    $(document).on('change', this.inputInstallementColor, (e) => {
      this.updateSwatchColor(e.currentTarget);
    });

    $(document).on('change', '[customize-style-shortcut-container] .form-control', (e) => this.updatePreviewButton(e));
    $(document).on('change', '[data-type="height"]', (e) => this.checkHeight(e));
    $(document).on('change', '[data-type="width"]', (e) => this.checkWidth(e));
    $(document).on('click', '[logout-button]', () => this.resetCredentials());
    $(document).on('input', '[name="PAYPAL_WHITE_LIST_IP"]', (e) => this.onUpdateIP(e));

    document.addEventListener('generateCredentials', (event) => {
      this.generateCredentials(event.detail);
    });
    document.addEventListener('updateCredentials', () => {
      this.updateCredentials();
    });
    document.addEventListener('updateButtonSection', () => {
      this.updateButtonSection();
    });
    document.addEventListener('click', (event) => {
      if (event.target.hasAttribute('refresh-technical-checklist')) {
        this.refreshTechnicalChecklist();
      }
    });
    document.addEventListener('click', (event) => {
      if (event.target.hasAttribute('refresh-feature-checklist')) {
        this.refreshFeatureChecklist();
      }
    });

    $(document).on('click', '[data-form-installment]', (e) => {
      paypal.event = e;
      try {
        paypal.configuratorsaved = false;
        $('#configurator-eligibleContainer').find('button')[0].click();
        setTimeout(function() {
          console.log('Error on saving messengin configuration, continue.');
          if (paypal.configuratorsaved !== true) {
            const newConfig = JSON.stringify(paypal.messagingConfig);
            $('#PAYPAL_INSTALLMENT_MESSAGING_CONFIG').val(newConfig);
            paypal.messagingConfig = newConfig;
            paypal.submitInstallmentForm();
          }
        }, 500);
      } catch (error) {
        paypal.submitInstallmentForm();
        console.log(error);
      }
    });

    $(document).on('click', '[save-form]', (e) => {
      e.preventDefault();

      this.saveProcess(e)
        .then((result) => {
          if (result) {
            this.refreshForms(
              e.target.closest('form').classList.contains('form-modal'),
              new URL(this.controller)
            );
            document.dispatchEvent(
              (new CustomEvent(
                'afterFormSaved',
                {
                  bubbles: true,
                  detail: {
                    form: e.target.closest('form'),
                  }
                }
              ))
            );
          }
        });

    });
  }

  updateSwatchColor(element) {
    const newColor = $(element).find('option:selected').data('color');
    const $swatch = $(element).next('.color-swatch');
    $swatch.css('background', newColor);
    if (newColor == '#fff') {
      $swatch.addClass('border');
    } else {
      $swatch.removeClass('border');
    }
  }

  updatePreviewButton(e) {
    const container = $(e.target).closest('[customize-style-shortcut-container]');

    if (container.find('[msg-container]').find('.alert').length > 0) {
      return false;
    }

    const preview = container.find('[preview-section]').find('[button-container]');
    const color = container.find('[data-type="color"]').val();
    const shape = container.find('[data-type="shape"]').val();
    const label = container.find('[data-type="label"]').val();
    const width = container.find('[data-type="width"]').val();
    const height = container.find('[data-type="height"]').val();

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
  }

  checkHeight(e) {
    const containerSize = $(e.target).closest('[chain-input-container]');
    const msgContainer = containerSize.find('[msg-container]');
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
 }

 checkWidth(e) {
   const containerSize = $(e.target).closest('[chain-input-container]');
   const msgContainer = containerSize.find('[msg-container]');
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
 }

  resetCredentials() {
    const url = new URL(this.controller);
    url.searchParams.append('ajax', 1);
    url.searchParams.append('action', 'resetCredentials');
    url.searchParams.append('isSandbox', this.isSandbox() ? 1 : 0);

    fetch(url.toString(), {
      method: 'GET',
    })
      .then((response) => {
        return response.json();
      })
      .then((response) => {
        if (response.success) {
          if (this.isSandbox()) {
            document.querySelector('[name="is_configured_sandbox"]').value = 0;
            document.querySelector('[name="paypal_clientid_sandbox"]').value = '';
            document.querySelector('[name="paypal_secret_sandbox"]').value = '';
            document.querySelector('[name="merchant_id_sandbox"]').value = '';
          } else {
            document.querySelector('[name="is_configured_live"]').value = 0;
            document.querySelector('[name="paypal_clientid_live"]').value = '';
            document.querySelector('[name="paypal_secret_live"]').value = '';
            document.querySelector('[name="merchant_id_live"]').value = '';
          }
        }

        this.updateButtonSection();
      });
  }

  updateButtonSection() {
    const liveSection = document.querySelector('[onboarding-button-section] [live-section]');
    const sandboxSection = document.querySelector('[onboarding-button-section] [sandbox-section]');
    const logoutSection = document.querySelector('[onboarding-button-section] [logout-section]');
    this.updateMerchantLabel();

    if (this.isConfigured()) {
      liveSection.style.display = 'none';
      sandboxSection.style.display = 'none';
      logoutSection.style.display = null;
      return;
    }

    if (this.isSandbox()) {
      liveSection.style.display = 'none';
      sandboxSection.style.display = null;
      logoutSection.style.display = 'none';
    } else {
      liveSection.style.display = null;
      sandboxSection.style.display = 'none';
      logoutSection.style.display = 'none';
    }
  }

  updateCredentials() {
    const liveSection = document.querySelector('[credential-section] [live-section]');
    const sandboxSection = document.querySelector('[credential-section] [sandbox-section]');

    if (this.isSandbox()) {
      liveSection.style.display = 'none';
      sandboxSection.style.display = null;
    } else {
      liveSection.style.display = null;
      sandboxSection.style.display = 'none';
    }

    this.updateMerchantLabel();
  }

  updateMerchantLabel() {
    const labelSandbox = document.querySelector('[merchant-label-sandbox]');
    const labelLive = document.querySelector('[merchant-label-live]');

    if (labelLive === null || labelSandbox === null) {
      return;
    }

    if (!this.isConfigured()) {
      labelSandbox.style.display = 'none';
      labelLive.style.display = 'none';
      return;
    }

    if (this.isSandbox()) {
      labelSandbox.querySelector('[merchant-id]').textContent = document.querySelector('[name="merchant_id_sandbox"]').value;
      labelSandbox.style.display = null;
      labelLive.style.display = 'none';
    } else {
      labelLive.querySelector('[merchant-id]').textContent = document.querySelector('[name="merchant_id_live"]').value;
      labelLive.style.display = null;
      labelSandbox.style.display = 'none';
    }
  }

  saveProcessInstallment(event) {
    return new Promise((resolve, reject) => {
      event.target.disabled = true;
      const formData = new FormData(document.getElementById('pp_installment_messenging_form'));
      const url = new URL(document.location);
      formData.append('installmentMessengingForm', 1);
      url.searchParams.append('ajax', 1);
      url.searchParams.append('action', 'saveForm');

      fetch(url.toString(), {
        method: 'POST',
        body: formData,
      })
        .then((response) => {
          event.target.disabled = false;
          return response.json();
        })
        .then((response) => {
          resolve(response.success == true);
        });
    });
  }

  saveProcess(event) {
    return new Promise((resolve, reject) => {
      event.target.disabled = true;
      const formData = new FormData(event.currentTarget.closest('form'));
      const url = new URL(this.controller);
      formData.append(event.currentTarget.getAttribute('name'), 1);
      url.searchParams.append('ajax', 1);
      url.searchParams.append('action', 'saveForm');

      fetch(url.toString(), {
        method: 'POST',
        body: formData,
      })
        .then((response) => {
          event.target.disabled = false;
          return response.json();
        })
        .then((response) => {
          resolve(response.success == true);
        });
    });
  }

  generateCredentials(data) {
    if (this.generateCredentialsEventDone === true) {
      return false;
    }
    const url = new URL(this.controller);
    url.searchParams.append('ajax', 1);
    url.searchParams.append('action', 'generateCredentials');
    url.searchParams.append('authCode', data.authCode);
    url.searchParams.append('sharedId', data.sharedId);
    url.searchParams.append('isSandbox', this.isSandbox() ? 1 : 0);
    this.generateCredentialsEventDone = true;

    fetch(url.toString(), {
      method: 'GET',
    })
      .then((response) => {
        return response.json();
      })
      .then((response) => {
        if (response.success) {
          if (response.isSandbox) {
            document.querySelector('[name="is_configured_sandbox"]').value = 1;
            document.querySelector('[name="paypal_clientid_sandbox"]').value = response.clientid;
            document.querySelector('[name="paypal_secret_sandbox"]').value = response.secret;
            document.querySelector('[name="merchant_id_sandbox"]').value = response.merchantId;
          } else {
            document.querySelector('[name="is_configured_live"]').value = 1;
            document.querySelector('[name="paypal_clientid_live"]').value = response.clientid;
            document.querySelector('[name="paypal_secret_live"]').value = response.secret;
            document.querySelector('[name="merchant_id_live"]').value = response.merchantId;
          }
        } else {
          this.errorMessage(response);
        }

        this.updateButtonSection();
        this.generateCredentialsEventDone = false;
      });
  }

  errorMessage(response) {
    let messageSection = '#paypal_error_ajax';
    let prependTo = '[onboarding-button-section]';
    if (!$(messageSection).length || $(messageSection).length <= 0) {
      const htmlDiv = '<div id="paypal_error_ajax" class="alert alert-danger"></div>';
      $(prependTo).append(htmlDiv);
    }
    $(messageSection).html(response.message);
  }
  isSandbox() {
    const mode = document.querySelector('#pp_account_form [name="mode"]');

    if (mode) {
      return mode.value == 'SANDBOX';
    }

    return false;
  }

  isConfigured() {
    if (this.isSandbox()) {
      return document.querySelector('[name="is_configured_sandbox"]').value == 1;
    }

    return document.querySelector('[name="is_configured_live"]').value == 1;
  }

  refreshFeatureChecklist() {
    const url = new URL(this.controller);
    url.searchParams.append('ajax', 1);
    url.searchParams.append('action', 'renderFeatureChecklist');
    document.querySelector('[refresh-feature-checklist]').disabled = true;

    fetch(url.toString(), {
      method: 'GET',
    })
      .then((response) => {
        document.querySelector('[refresh-feature-checklist]').disabled = false;
        return response.json();
      })
      .then((response) => {
        if (response.success) {
          document.querySelector('[feature-checklist-container]').innerHTML = response.content;
        }
      });
  }

  refreshTechnicalChecklist() {
    const url = new URL(this.controller);
    url.searchParams.append('ajax', 1);
    url.searchParams.append('action', 'renderTechnicalChecklist');
    document.querySelector('[refresh-technical-checklist]').disabled = true;

    fetch(url.toString(), {
      method: 'GET',
    })
      .then((response) => {
        document.querySelector('[refresh-technical-checklist]').disabled = false;
        return response.json();
      })
      .then((response) => {
        if (response.success) {
          document.querySelector('[technical-checklist-container]').innerHTML = response.content;
        }
      });
  }

  refreshForms(isModal=false, url) {
    url.searchParams.append('ajax', 1);
    url.searchParams.append('action', 'getForms');
    url.searchParams.append('isModal', (isModal ? '1' : '0'));

    fetch(url.toString(), {
      method: 'GET',
    })
      .then((response) => {
        return response.json();
      })
      .then((response) => {
        if (response.success == false) {
          return;
        }

        for (const idForm in response.forms) {
          const container = $(`[form-container="${idForm}"]`);

          if (container.length == 0) {
            continue;
          }

          container.html(response.forms[idForm]);
        }
        paypal.refreshMessenging();
      });
  }

  onUpdateIP(e) {
    e.currentTarget.value = e.currentTarget.value
      .split('')
      .filter((symbol) => {
        return ['0','1','2','3','4','5','6','7','8','9','.',','].indexOf(symbol) >= 0;
      })
      .join('');
  }

  submitInstallmentForm() {
    const formElement = document.getElementById('pp_installment_messenging_form');
    const e = paypal.event;
    paypal.saveProcessInstallment(e)
      .then((result) => {
        if (result) {
          paypal.refreshForms(
            formElement.classList.contains('form-modal'),
            new URL(document.location)
          );
          document.dispatchEvent(
            (new CustomEvent(
              'afterFormSaved',
              {
                bubbles: true,
                detail: {
                  form: formElement,
                }
              }
            ))
          );
          paypal.refreshMessenging();
        }
      });
  }

  saveDataMessengingConfigurator(data) {
    paypal.configuratorsaved = true;
    const newConfig = JSON.stringify(data.config);
    $('#PAYPAL_INSTALLMENT_MESSAGING_CONFIG').val(newConfig);
    paypal.messagingConfig = newConfig;
    paypal.submitInstallmentForm();
  }

  refreshMessenging() {
    if (!document.getElementById('messaging-configurator')) {
      return;
    }

    const url = new URL(document.location);
    url.searchParams.append('ajax', '1');
    url.searchParams.append('action', 'getMessagingConfig');

    fetch(url)
    .then(response => response.json())
    .then((response) => {
      if (response.success !== true) {
        return;
      }

      const config = response.config;
      config.onSave = paypal.saveDataMessengingConfigurator;

      window.merchantConfigurators.Messaging(config);
    });
  }
}

export default Form;
