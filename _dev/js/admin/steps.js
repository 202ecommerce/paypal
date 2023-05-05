class Steps {
  constructor(selector) {
    this.$stepsContainer = $(selector);
    this.btn = '[data-btn-action]';
    this.content = '[data-step-content]';
    this.currentStepBadge = '[data-badge-current-step]';
    this.stepsProgress = '[data-steps-progress]';
    this.controller = document.location;
  }

  init() {
    this.registerEvents();
  }

  registerEvents() {
    $(this.btn).on('click', (e) => {
      e.preventDefault();

      this.saveProcess(e)
        .then((result) => {
          if (result) {
            this.setAction($(e.currentTarget).data('btn-action'));
            this.updateCurrentBadgeStep();
            this.updateStepsProgress();
          }
        });
    });

    document.addEventListener('generateCredentials', (event) => {
      this.generateCredentials(event.detail);
    });
    document.addEventListener('updateCredentials', () => {
      this.updateCredentials();
    });
    document.addEventListener('updateButtonSection', () => {
      this.updateButtonSection();
    });
    document.querySelector('[logout-button]').addEventListener('click', () => {
      this.resetCredentials();
    });
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
  }

  saveProcess(event) {
    return new Promise((resolve, reject) => {
      if (false === event.currentTarget.hasAttribute('save-form')) {
        return resolve(true);
      }

      event.currentTarget.disabled = true;
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
          event.currentTarget.disabled = false;
          return response.json();
        })
        .then((response) => {
          resolve(response.success == true);
        });
    });
  }

  generateCredentials(data) {
    const url = new URL(this.controller);
    url.searchParams.append('ajax', 1);
    url.searchParams.append('action', 'generateCredentials');
    url.searchParams.append('authCode', data.authCode);
    url.searchParams.append('sharedId', data.sharedId);
    url.searchParams.append('isSandbox', this.isSandbox() ? 1 : 0);

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
        }

        this.updateButtonSection();
      });
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

  getSteps() {
    return this.$stepsContainer.find(this.content);
  }

  getCurrentStep() {
    const steps = this.getSteps();
    return steps.filter(':not(.d-none)');
  }

  getCurrentStepIndex() {
    const steps = this.getSteps();
    return steps.index(steps.filter(':not(.d-none)'));
  }

  getLastStepIndex() {
    return this.$stepsContainer.find(this.content).length - 1;
  }

  updateCurrentBadgeStep() {
    const currentStepIndex = this.getCurrentStepIndex();
    if (currentStepIndex <= this.getLastStepIndex()) {
      this.$stepsContainer.find(this.currentStepBadge).html(currentStepIndex + 1);
    }
  }

  updateStepsProgress() {
    const currentStepIndex = this.getCurrentStepIndex();
    const value = currentStepIndex * 100 / this.getLastStepIndex();
    this.$stepsContainer.find(this.stepsProgress).attr('aria-valuenow', value).css('width', `${value}%`);
  }

  setAction(action) {
    const currentStepIndex = this.getCurrentStepIndex();
    let nextStepIndex = currentStepIndex;
    if (action === 'prev') {
      nextStepIndex -= 1;
    }
    if (action === 'next') {
      nextStepIndex += 1;
    }

    this.setActiveStep(currentStepIndex, nextStepIndex);
  }

  updateButtons() {
    const $prevBtn = this.getCurrentStep().find('[data-btn-action="prev"]');
    const $nextBtn = this.getCurrentStep().find('[data-btn-action="next"]');

    if (this.getCurrentStepIndex() === 0) {
      $prevBtn.addClass('d-none');
    } else {
      $prevBtn.removeClass('d-none');
    }

    if (this.getCurrentStepIndex() === this.getLastStepIndex()) {
      $nextBtn.attr('data-dismiss', 'modal')
    } else {
      $nextBtn.removeAttr('data-dismiss');
    }
  }

  setActiveStep(currentIndex, newIndex) {
    if (newIndex !== currentIndex) {
      const direction = (newIndex > currentIndex)
        ? (start) => start <= newIndex
        : (start) => start >= newIndex;

      const index = (newIndex > currentIndex)
        ? (start) => start + 1
        : (start) => start - 1;

      while (direction(currentIndex)) {
        this.setShowStep(currentIndex);
        currentIndex = index(currentIndex);
      }

      this.updateButtons();
    }
  }

  setShowStep(index) {
    const $tabContent = this.$stepsContainer.find(this.content);
    $tabContent.addClass('d-none').eq(index).removeClass('d-none');
  }
}

export default Steps;
