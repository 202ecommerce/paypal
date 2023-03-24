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

<!-- Start bnpl. Module Paypal -->
{include file = "{$psPaypalDir}/views/templates/_partials/javascript.tpl" assign=javascriptBlock}
{block name='head'}
  {$javascriptBlock nofilter}
{/block}

{block name='content'}{/block}

{block name='js'}{/block}

{block name='init-button'}
  <script>
      function waitPaypalSDKIsLoaded() {
          if (typeof totPaypalBnplSdkButtons === 'undefined' || typeof BNPL === 'undefined') {
              setTimeout(waitPaypalSDKIsLoaded, 200);
              return;
          }

          BNPL.init();

          if (typeof bnplColor != 'undefined') {
              BNPL.setColor(bnplColor);
          }

          if (typeof PAYPAL_MOVE_BUTTON_AT_END != 'undefined') {
            BNPL.isMoveButtonAtEnd = PAYPAL_MOVE_BUTTON_AT_END;
          }

          BNPL.initButton();
      }

      waitPaypalSDKIsLoaded();
  </script>
{/block}
<!-- End bnpl. Module Paypal -->



