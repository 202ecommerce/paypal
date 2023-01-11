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

<style>
  .product-quantity {
    flex-wrap: wrap;
  }

  .product-quantity [installment-container] {
    flex-basis: 100%;
  }
</style>

<div style="padding: 5px 0">
    {include file='./banner.tpl'}
</div>

<script>
    Banner.prototype.updateAmount = function() {
        var quantity = parseFloat(document.querySelector('input[name="qty"]').value);
        var productPrice = 0;
        var productPriceDOM = document.querySelector('[itemprop="price"]');

        if (productPriceDOM == null) {
            productPriceDOM = document.querySelector('.current-price-value');
        }

        if (productPriceDOM instanceof Element) {
            productPrice = parseFloat(productPriceDOM.getAttribute('content'));
        }

        this.amount = quantity * productPrice;
    };

    Banner.prototype.getProductInfo = function() {
        var data = {
            idProduct: paypalBanner_IdProduct,
            quantity: 1, //default quantity
            combination: this.getCombination().join('|'),
            page: 'product'
        };

        var qty = document.querySelector('input[name="qty"]');

        if (qty instanceof Element) {
            data.quantity = qty.value;
        }


        return data;
    };

    Banner.prototype.getCombination = function() {
        var combination = [];
        var re = /group\[([0-9]+)\]/;

        $.each($('#add-to-cart-or-refresh').serializeArray(), function (key, item) {
            if (res = item.name.match(re)) {
                combination.push("".concat(res[1], " : ").concat(item.value));
            }
        });

        return combination;
    };

    Banner.prototype.checkProductAvailability = function() {
        if (typeof paypalBanner_scInitController == 'undefined') {
            return;
        }

        var url = new URL(paypalBanner_scInitController);
        url.searchParams.append('ajax', '1');
        url.searchParams.append('action', 'CheckAvailability');

        fetch(url.toString(), {
            method: 'post',
            headers: {
                'content-type': 'application/json;charset=UTF-8'
            },
            body: JSON.stringify(this.getProductInfo())
        }).then(function (res) {
            return res.json();
        }).then(function (json) {
            var banner = document.querySelector(this.container);

            if (json.success) {
                banner.style.display = 'block';
            } else {
                banner.style.display = 'none';
            }
        }.bind(this));
    };

    window.addEventListener('load', function() {
        var paypalBanner = new Banner({
            layout: layout,
            placement: placement,
            container: '[paypal-banner-message]'
        });
        paypalBanner.updateAmount();
        paypalBanner.initBanner();
        paypalBanner.checkProductAvailability();

        prestashop.on('updatedProduct', function() {
            paypalBanner.updateAmount();
            paypalBanner.initBanner();
            paypalBanner.checkProductAvailability();
        });
    });
</script>
