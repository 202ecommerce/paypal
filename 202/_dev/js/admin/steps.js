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

class Steps {
  constructor(selector) {
    this.$stepsContainer = $(selector);
    this.btn = '[data-btn-action]';
    this.content = '[data-step-content]';
    this.currentStepBadge = '[data-badge-current-step]';
    this.maxStepBadge = '[data-badge-max-step]';
    this.stepsProgress = '[data-steps-progress]';
    this.modalConfiguration = '[data-modal-dialog-configuration]';
    this.controller = document.location.href;
  }

  init() {
    this.registerEvents();
    this.$stepsContainer.find(this.maxStepBadge).html($(this.content).length);
  }

  registerEvents() {
    $(document).on('afterFormSaved', (e) => {
      if (e.originalEvent.detail.form.classList.contains('form-modal') == false) {
        return;
      }
      const currentStepIndex = this.getCurrentStepIndex();
      this.setAction('next');
      this.updateCurrentBadgeStep();
      this.updateStepsProgress();

      if (currentStepIndex + 1 === this.getSteps().length) {
        $(e.originalEvent.detail.form).closest('.modal').modal('hide');
      }
    });
    $(document).on('click', this.btn, (e) => {
      e.preventDefault();
      this.setAction($(e.currentTarget).data('btn-action'));
      this.updateCurrentBadgeStep();
      this.updateStepsProgress();

      if ($(e.currentTarget).attr('data-dismiss') === 'modal') {
        $(e.currentTarget).closest('.modal').modal('hide');
      }
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
      $nextBtn.attr('data-dismiss', 'modal');
    } else {
      $nextBtn.removeAttr('data-dismiss');
    }
  }

  setActiveStep(currentIndex, newIndex) {
    if (newIndex + 1 > this.getSteps().length) {
      return;
    }
    if (newIndex === currentIndex) {
      return;
    }

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

  setShowStep(index) {
    const $tabContent = this.$stepsContainer.find(this.content);
    $tabContent.addClass('d-none').eq(index).removeClass('d-none');
  }
}

export default Steps;
