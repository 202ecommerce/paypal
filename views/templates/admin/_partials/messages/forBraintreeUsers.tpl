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

<div class="pp__mb-4">
    <div class="alert alert-danger">
        {l s='Starting July, 2019, Braintree payment solution is separated from PayPal module. There are 2 different
modules: PayPal official (v5.0.0) and Braintree official (v1.0.0). ' mod='paypal'}
        <br>
        {l s='You are using the v5.0.0 of PayPal module : the Braintree payment solution is not available via PayPal anymore. ' mod='paypal'}
        <br>
        {{l s='You can continue to use Braintree by installing the new Braintree module available via [a @href1@]addons.prestashop[/a] for free.' mod='paypal'}|paypalreplace:['@href1@' => {'https://addons.prestashop.com'}, '@target@' => {'target="blank"'}]}
        <br>
        {l s='You will be able to migrate your account settings and orders created via Braintree once you install the new Braintree module.' mod='paypal'}
        <br>
        {l s='Please note that we highly recommend to uninstall the PayPal module once you finish your Braintree settings migration.' mod='paypal'}
    </div>

    <div class="pp__flex pp__justify-content-center">
        <a class="btn btn-default"
           href="{$link->getAdminLink('AdminPayPalSetup', true, null, ['useWithoutBraintree' => 1])}">
            {{l s='I understand.[br]You would like to use PayPal without Braintree' mod='paypal'}|paypalreplace}
        </a>
    </div>
</div>
