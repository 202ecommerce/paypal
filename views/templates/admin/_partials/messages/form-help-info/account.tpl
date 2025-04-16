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

<div class="h4">
  {l s='How can I log in with my PayPal account ?' mod='paypal'}
</div>
<br>
<p>
  {l s='Before connecting your shop with your PayPal account, please select the mode of connection. There is 2 connection modes :' mod='paypal'}
</p>

<p>
  {{l s='[b]Sandbox[/b] mode allows you a mock link to test the flow between Prestashop and PayPal. You can test as long as needed. If you want to create an account, please use the button "Connect or create your PayPal account".' mod='paypal'}|paypalreplace:[]}
</p>
<p>
    {{l s='[b]Production[/b] mode allows you to sell our products online and be paid by customers. If you want to create an account, please click use the button "Connect or create your PayPal account".' mod='paypal'}|paypalreplace:[]}
</p>
<p>
  {{l s='[b]Note:[/b] You need to switch to the “Production” mode to activate your payment solution.' mod='paypal'}|paypalreplace:[]}
</p>
