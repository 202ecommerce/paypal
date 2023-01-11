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

<p>
    <div>
        {l s='You are using the German merchant account. Starting from the v5.0.0 of the PayPal Official module it is required to use PayPal Plus instead of PayPal Express Checkout.' mod='paypal'}
    </div>

    <div>
        {{l s='Please add your REST API credentials below to setup your account and continue to offer the PayPal payment solution to your customers. [a @href1@]Learn more about PayPal Plus[/a].' mod='paypal'}|paypalreplace:['@href1@' => {'https://www.paypal.com/de/webapps/mpp/paypal-plus'}, '@target@' => {'target="blank"'}]}
    </div>
</p>
