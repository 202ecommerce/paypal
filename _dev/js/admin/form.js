import {Tools} from './../tools.js';

class Form {
  constructor(formSelector = '[data-form-configuration]') {
    this.formGroupDynamicSelector = '.form-group-dynamic';
    this.inputDynamicSelector = '.custom-control-input';
    this.inputInstallementColor = '[name="PAYPAL_INSTALLMENT_COLOR"]';
  }

  init() {
    this.registerEvents();
  }


  registerEvents() {
    $(`${this.formGroupDynamicSelector} ${this.inputDynamicSelector}`).on('change', (e) => {
      const groupName = e.currentTarget.closest(this.formGroupDynamicSelector).getAttribute('group-name');
      const $formGroups = $(e.currentTarget).closest(this.formGroupDynamicSelector).siblings(`[group-name="${groupName}"]`);
      if ($(e.currentTarget).prop('checked')) {
        $formGroups.removeClass('d-none');
      } else {
        $formGroups.addClass('d-none');
      }
    });

    $(this.inputInstallementColor).on('change', (e) => {
      this.updateSwatchColor(e.currentTarget);
    });

    $('[customize-style-shortcut-container] .form-control').on('change', (e) => this.updatePreviewButton(e));
    $('[data-type="height"]').on('change', (e) => this.checkHeight(e));
    $('[data-type="width"]').on('change', (e) => this.checkWidth(e));
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
}

export default Form;
