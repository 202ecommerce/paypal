{**
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
 *  @author since 2007 PayPal
 *  @author 202 ecommerce <tech@202-ecommerce.com>
 *  @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 *  @copyright PayPal
 *
 *}
{include file="{$module_dir}/views/templates/_partials/javascript.tpl"}

<div id="prestashop-cloudsync"></div>

<script>
  if (window.cloudSyncSharingConsent === undefined) {
    const cloudsyncCdn = document.createElement('script');
    cloudsyncCdn.src = '{$urlCloudsync|escape:'htmlall':'UTF-8'}';
    document.body.appendChild(cloudsyncCdn);
  }
  if (window.psaccountsVue === undefined) {
    const accountsCdn = document.createElement('script');
    accountsCdn.src = '{$urlAccountsCdn|escape:'htmlall':'UTF-8'}';
    document.body.appendChild(accountsCdn);
  }

  setTimeout(function(){
    const initCloudsync = function() {
      if (!window.cloudSyncSharingConsent || !window.psaccountsVue) {
        setTimeout(initCloudsync, 200);
        return;
      }

      window.psaccountsVue.init();
      window.cloudSyncSharingConsent.init('#prestashop-cloudsync');
      window.cloudSyncSharingConsent.on('OnboardingCompleted', (isCompleted) => {
        console.log('OnboardingCompleted', isCompleted);
      });
      window.cloudSyncSharingConsent.isOnboardingCompleted((isCompleted) => {
        console.log('Onboarding is already Completed', isCompleted);
      });
    }

    initCloudsync();
  }, 0);
</script>
