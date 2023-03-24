/**
 * 2007-2023 PayPal
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
 *  @author 2007-2023 PayPal
 *  @author 202 ecommerce <tech@202-ecommerce.com>
 *  @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 *  @copyright PayPal
 *
 */
const jsdom = require("jsdom");

module.exports = (html, callback) => {
  if (html instanceof jsdom.JSDOM === false) return;

  const carriers = {};

  html.window.document.querySelectorAll('tr').forEach((tr) => {
    const data = {
      key: null,
      name: null,
      domain: null,
    };
    tr.querySelectorAll("td").forEach((td, index) => {
      let value = td.textContent;
      switch (index) {
        case 0:
          data.name = value;
          break;
        case 1:
          data.key = value;
          break;
        case 2:
          data.domain = value.toUpperCase();
          break;
      }
    });

    if (data.domain === null) {
      return;
    }

    if (data.domain in carriers === false) {
      carriers[data.domain] = [];
    }

    carriers[data.domain].push({ key: data.key, name: data.name});
  });

  callback(null, carriers);
}
