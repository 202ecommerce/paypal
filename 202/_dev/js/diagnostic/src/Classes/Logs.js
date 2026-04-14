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
export default class Logs {
  init() {
    this.registerEvents();
  }

  registerEvents() {
    const self = this;

    $('.paypal-collapse').on('click', (event) => {
      event.preventDefault();
      event.stopPropagation();
      const $panelGroup = $(event.currentTarget).closest('.panel-group');
      if (!$panelGroup.data('loaded')) {
        this.loadLogs($panelGroup);
      }

      const $btn = $panelGroup.find('.paypal-collapse');
      $panelGroup.find('[data-log-zone]').toggleClass('d-none');
      $btn.find('a').toggleClass('collapsed');
    });
  }

  async loadLogs($panelGroup) {
    const $btn = $panelGroup.find('.paypal-collapse');
    const url = window.paypal.actionLink;
    const body = {
      ajax: 1,
      value: $btn.data('value'),
      type: $btn.data('type'),
      event: 'loadLogs',
    }

    const response = await fetch(url, {
      method: 'POST',
      body: new URLSearchParams(body).toString(),
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    });

    const data = await response.text();

    if (data) {
      $panelGroup.find('[data-zone-content]').html(data);
    }
    $panelGroup.data('loaded', true);
  }
}
