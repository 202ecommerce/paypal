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

<div>
    <div>
      {l s='PayPal webhooks can not be enabled. The reason of the error is one of the following:' mod='paypal'}
    </div>

  <ul>
    <li>{l s='htpasswd enabled' mod='paypal'}</li>
    <li>{l s='the https protocol is not supported' mod='paypal'}</li>
    <li>{l s='wrong https certificate' mod='paypal'}</li>
    <li>{l s='invalid server settings or insufficient access rights.' mod='paypal'}</li>
  </ul>

  <div>
    {{l s='Please fix the issue or contact our support team. (link to add for [a @href1@]\'our support team\'[/a].' mod='paypal'}|paypalreplace:['@href1@' => {'https://addons.prestashop.com/fr/contactez-nous?id_product=1748'}, '@target@' => {'target="blank"'}]}
  </div>

  <div>
    {l s='Webhooks are not used by the module until the moment the problem will be fixed. Please refresh the page to check the status again.' mod='paypal'}
  </div>
</div>
