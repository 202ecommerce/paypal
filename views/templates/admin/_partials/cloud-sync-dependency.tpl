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
<div id="cdc-container"></div>
{literal}
<script>
  if (window.mboCdcDependencyResolver === undefined) {
    const mboCdn = document.createElement('script');
    mboCdn.src = 'https://assets.prestashop3.com/dst/mbo/v1/mbo-cdc-dependencies-resolver.umd.js';
    document.body.appendChild(mboCdn);
  }

  setTimeout(function(){
    const init = function() {
      if (!window.mboCdcDependencyResolver) {
        setTimeout(init, 200);
        return;
      }

      const renderMboCdcDependencyResolver = window.mboCdcDependencyResolver.render
      const context = {
        ...{/literal}{$dependencies|json_encode}{literal},
        onDependenciesResolved: () => location.reload(),
        onDependencyFailed: (dependencyData) => console.log('Failed to install dependency', dependencyData),
      }
      renderMboCdcDependencyResolver(context, '#cdc-container');
    }

    init();
  }, 0);

</script>
{/literal}
