{**
 * since 2007 PayPal
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

{assign var='variant' value=$variant|default:'normal'}

<div class="alert alert-warning {[
  'hidden' => $variant == 'hidden'
]|classnames}" data-rounding-alert>
  <button type="button" class="close" data-dismiss="alert">×</button>
    <div>
      {{l s='Your rounding settings are not fully compatible with PayPal requirements. In order to avoid some of the transactions to fail, please change the PrestaShop rounding mode in [a @href1@] Preferences > General[/a] to:' mod='paypal'}|paypalreplace:['@href1@' => {$link->getAdminLink('AdminPreferences', true)}, '@target@' => {'target="blank"'}]}
    </div>

    {include file="../../_partials/roundingSettings.tpl"}
</div>
