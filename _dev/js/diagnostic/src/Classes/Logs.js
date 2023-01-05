/*
 * NOTICE OF LICENSE
 *
 * This source file is subject to a commercial license from SARL 202 ecommerce
 * Use, copy, modification or distribution of this source file without written
 * license agreement from the SARL 202 ecommerce is strictly forbidden.
 * In order to obtain a license, please contact us: tech@202-ecommerce.com
 * ...........................................................................
 * INFORMATION SUR LA LICENCE D'UTILISATION
 *
 * L'utilisation de ce fichier source est soumise a une licence commerciale
 * concedee par la societe 202 ecommerce
 * Toute utilisation, reproduction, modification ou distribution du present
 * fichier source sans contrat de licence ecrit de la part de la SARL 202 ecommerce est
 * expressement interdite.
 * Pour obtenir une licence, veuillez contacter 202-ecommerce <tech@202-ecommerce.com>
 * ...........................................................................
 *
 * @author    202-ecommerce <tech@202-ecommerce.com>
 * @copyright Copyright (c) 202-ecommerce
 * @license   Commercial license
 * @version   [version]
 */

import axios from 'axios';
import qs from 'qs';

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

    const response = await axios.post(url, qs.stringify({
      ajax: 1,
      value: $btn.data('value'),
      type: $btn.data('type'),
      event: 'loadLogs',
    }));

    if (response.data.content) {
      $panelGroup.find('[data-zone-content]').html(response.data.content);
    }
    $panelGroup.data('loaded', true);
  }
}
