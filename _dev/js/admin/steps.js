class Steps {
  constructor(selector) {
    this.$stepsContainer = $(selector);
    this.btn = '[data-btn-action]';
    this.content = '[data-step-content]';
    this.currentStepBadge = '[data-badge-current-step]';
    this.stepsProgress = '[data-steps-progress]';
  }

  init() {
    this.registerEvents();
  }

  registerEvents() {
    $(this.btn).on('click', (e) => {
      e.preventDefault();
      this.setAction($(e.currentTarget).data('btn-action'));
      this.updateCurrentBadgeStep();
      this.updateStepsProgress();
    });
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
