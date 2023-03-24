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

<!-- Start modules/paypal/views/templates/pui/signUpLinkButton.tpl -->

{if empty($actionUrl)}

  <div class="alert alert-danger">
      {l s='Error occurred during generating sign-up link. Please, try re-configure the connection with PayPal account' mod='paypal'}
  </div>

{else}

  <a href="{$actionUrl|addslashes}"
     target="_blank"
     data-paypal-button
     data-paypal-onboard-complete="onboardCallback"
     class="btn btn-default spinner-button"
  >
    <i class="icon-signin"></i>
    <div class="spinner pp__mr-1"></div>
      {l s='Connect or create PayPal account' mod='paypal'}
  </a>

{/if}

{if isset($paypalOnboardingLib)}
  <script src="{$paypalOnboardingLib|addslashes}"></script>
{/if}

<!-- End modules/paypal/views/templates/pui/signUpLinkButton.tpl -->
