class Section {
  constructor(sectionSelector = '[data-section-configuration]') {
    this.sectionToggleSelector = '[data-section-toggle]';
    this.sectionSelector = sectionSelector;
    this.formSelector = '[data-form-configuration]';
    this.$dashboard = $('[data-dashboard]');
    this.$btnSectionReset = $('[data-btn-section-reset]');
  }

  init() {
    this.registerEvents();
  }


  registerEvents() {
    $(this.sectionToggleSelector).on('click', (e) => {
      e.preventDefault();
      e.stopPropagation();
      this.showSection(e.currentTarget);
      this.hideDashboard();
    });

    this.$btnSectionReset.on('click', () => {
      this.reset();
    })
  }

  showSection(element) {
    const $form = $(this.formSelector).filter(this.getFormSelector(element));
    $form.closest(this.sectionSelector).removeClass('d-none');
    this.$btnSectionReset.removeClass('d-none');
  }

  getFormSelector(element) {
    const section = $(element).data('section-toggle');

    let formSelector = false;

    switch (section) {
      case 'tracking':
        formSelector = '#pp_tracking_form';
        break;

      case 'configuration':
        formSelector = '#pp_checkout_form, #pp_installment_form, #pp_shortcut_configuration_form, #pp_order_status_form, #pp_white_list_form';
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
    this.$btnSectionReset.addClass('d-none');
  }

  reset() {
    this.showDashboard();
    this.hideAllSections();
  }

}

export default Section;