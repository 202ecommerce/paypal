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


<p>
    {{l s='[b]Payment Action:Sale:[/b] the money moves instantly from the buyer’s account to the seller’s account at the time of payment.' mod='paypal'}|paypalreplace:[]}
</p>
<p>
  {{l s='[b]Payment Action:Authorization/capture:[/b] The authorized mode is a deferred mode of payment that requires the funds to be collected manually when you want to transfer the money. This mode is used if you want to ensure that you have the merchandise before depositing the money' mod='paypal'}|paypalreplace:[]}
</p>
<p>
    {l s='We recommend the Authorize configuration only in case you need to check the availability of your products before confirming the order.' mod='paypal'}
</p>
<p>
    {l s='In-context has shown a better conversion rate.' mod='paypal'}
</p>
<p>
    {{l s='[b]PayPal In-Context:[/b] opens in a pop-up window, allowing your buyers to finalize their payment without leaving your website. Optimized, modern and reassuring experience which benefits from the same security standards than during a redirection to the PayPal website.' mod='paypal'}|paypalreplace:[]}
</p>

<p>
  {{l s='[b]PayPal Redirect:[/b] This mode will only be applied to eligible payments; other payment methods offered by the PayPal module will be displayed in context.' mod='paypal'}|paypalreplace:[]}
</p>

<p>
    {{l s='[b]PayPal Express Checkout shortcuts[/b] allow a faster checkout for your buyers. PayPal automatically provides you with the client\'s shipping and billing information.' mod='paypal'}|paypalreplace:[]}
</p>

{if $isShowCustomerInstruction|default:false}
  <p>
      {{l s='[b]Customer service[/b] instruction is a required message for using Pay upon invoice methode.' mod='paypal'}|paypalreplace:[]}
  </p>
{/if}

<p>
    {{l s='[b]Show PayPal benefits to your customers:[/b] you can increase your conversion rate by presenting PayPal benefits to your customers on the payment methods selection page.' mod='paypal'}|paypalreplace:[]}
</p>

<p>
  {l s='PayPal shortcuts allow a faster checkout for your buyers. PayPal automatically provides you with the client\'s shipping and billing information.' mod='paypal'}
</p>

<p>
    {{l s='[b]Brand name[/b] shown on top left during PayPal checkout: A label that overrides the business name in the PayPal account on the PayPal pages. If you are using PayPal Checkout redirect, you can also customize your shop logo. The logo can be changed via your business profile settings.' mod='paypal'}|paypalreplace:[]}
</p>

{if $isShowVaultingFunctionality|default:false}
    <p>
      {{l s='[b]The PayPal save payments[/b] allows merchants to securely store their customer’s payment methods. This helps reduce a merchant’s exposure to security and compliance risks. And also provides a quick and easy checkout experience for repeat buyers.' mod='paypal'}|paypalreplace:[]}
    </p>
{/if}
