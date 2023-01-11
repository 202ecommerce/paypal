{**
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
 *}

{include file='../_partials/javascript.tpl'}

<div installment-container>
  <div banner-container>
    <div paypal-banner-message></div>
  </div>
</div>

<script>
  window.Banner = function (conf) {

      this.placement = typeof conf.placement != 'undefined' ? conf.placement : null;

      this.amount = typeof conf.amount != 'undefined' ? conf.amount : null;

      this.layout = typeof conf.layout != 'undefined' ? conf.layout : null;

      this.color = typeof conf.color != 'undefined' ? conf.color : null;

      this.container = typeof conf.container != 'undefined' ? conf.container : null;

      this.textAlign = typeof conf.textAlign != 'undefined' ? conf.textAlign : null;

      this.currency = typeof conf.currency != 'undefined' ? conf.currency : null;
  };

  Banner.prototype.initBanner = function() {
      if (typeof totPaypalSdk == 'undefined') {
          setTimeout(this.initBanner.bind(this), 200);
          return;
      }

      var conf = {
          style: {
              ratio: '20x1'
          }
      };

      if (this.currency) {
          conf['currency'] = this.currency;
      }

      if (this.textAlign) {
          conf['style']['text'] = {
              'align': this.textAlign
          }
      }

      if (this.placement) {
          conf.placement = this.placement;
      }

      if (this.amount) {
          conf.amount = this.amount;
      }

      if (this.layout) {
          conf.style.layout = this.layout;
      }

      if (this.color && this.layout == 'flex') {
          conf.style.color = this.color;
      }

      totPaypalSdk.Messages(conf).render(this.container);
  };
</script>
