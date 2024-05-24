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

class Section {
  constructor(sectionSelector = '[data-section-configuration]') {
    this.sectionToggleSelector = '[data-section-toggle]';
    this.sectionSelector = sectionSelector;
    this.formSelector = '[data-form-configuration]';
    this.$dashboard = $('[data-dashboard]');
    this.$btnSectionReset = '[data-btn-section-reset]';
    this.controller = document.location.href;
  }

  init() {
    this.registerEvents();
  }

  registerEvents() {
    $(document).on('click', this.sectionToggleSelector, (e) => {
      e.preventDefault();
      e.stopPropagation();
      this.showSection(e.currentTarget.getAttribute('data-section-toggle'));
      this.hideDashboard();
    });

    document.addEventListener('showSection', (event) => {
      this.showSection(event.detail.section);
      this.hideDashboard();
    });

    $(document).on('click', this.$btnSectionReset, () => {
      this.reset();
    });

    document.addEventListener('afterFormSaved', (e) => {
      if (e.detail.form.id === 'pp_account_form') {
        this.refreshWelcomeBoard();
      }
    });
  }

  showSection(section) {
    document.location.hash = section;
    const $form = $(this.formSelector).filter(this.getFormSelector(section));
    $form.closest(this.sectionSelector).removeClass('d-none');
    $(this.$btnSectionReset).removeClass('d-none');
  }

  getFormSelector(section) {

    let formSelector = false;

    switch (section) {
      case 'tracking':
        formSelector = '#pp_tracking_form';
        break;

      case 'configuration':
        formSelector = '#pp_checkout_form, #pp_installment_form, #pp_installment_messenging_form, #pp_shortcut_configuration_form, #pp_order_status_form, #pp_white_list_form';
        break;

      case 'account':
        formSelector = '#pp_account_form';
        break;

      default:
        break;
    }

    return formSelector;
  }

  hideDashboard() {
    this.$dashboard.addClass('d-none');
  }

  showDashboard() {
    this.$dashboard.removeClass('d-none');
  }

  hideAllSections() {
    $(this.formSelector).closest(this.sectionSelector).addClass('d-none');
    $(this.$btnSectionReset).addClass('d-none');
  }

  reset() {
    this.showDashboard();
    this.hideAllSections();
    document.location.hash = '';
  }

  refreshWelcomeBoard() {
    const url = new URL(this.controller);
    url.searchParams.append('ajax', 1);
    url.searchParams.append('action', 'getWelcomeBoard');

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

        document.querySelector('[welcome-board]').outerHTML = response.content;
      });
  }

}

export default Section;
