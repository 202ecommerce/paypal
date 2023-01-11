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

{include file='./cart-banner.tpl'}

<script>
    window.addEventListener('load', function() {
        var checkoutSummaryObserver = new MutationObserver(function(mutations) {
            var url = new URL(paypalInstallmentController);
            url.searchParams.append('ajax', '1');
            url.searchParams.append('action', 'GetCartTotal');

            fetch(url.toString())
                .then(function(response) {
                    return response.json();
                })
                .then(function(response) {
                    if (typeof response.cartTotal == 'undefined') {
                        return true;
                    }

                    paypalBanner.amount = response.cartTotal;
                    paypalBanner.initBanner();
                });
        });

        checkoutSummaryObserver.observe(
            document.getElementById('js-checkout-summary').parentElement,
            {
                childList: true,
                subtree: true
            }
        );
    });

</script>
