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
