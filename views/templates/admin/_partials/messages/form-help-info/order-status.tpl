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

<h6>{l s='Customize order status' mod='paypal'}</h6>
<p>
    {l s='You can refund the orders paid via PayPal directly via your PrestaShop BackOffice. Here, you can choose the order status that triggers the refund on PayPal. Choose the option "no actions" if you would like to change the order status without triggering the automatic refund on PayPal.' mod='paypal'}
</p>
<p>
    {l s='You can cancel orders paid via PayPal directly via your PrestaShop BackOffice. Here, you can choose the order status that triggers the PayPal voiding of an authorized transaction on PayPal. Choose the option "no actions" if you would like to change the order status without triggering the automatic cancellation on PayPal.' mod='paypal'}
</p>
<p>
    {l s='You are currently using the Authorize mode. It means that you separate the payment authorization from the capture of the authorized payment. For capturing the authorized payment, you have to change the order status to "payment accepted" (or to a custom status with the same meaning). Here you can choose a custom order status for accepting the order and validating transactions in Authorize mode.' mod='paypal'}
</p>
<br>
<h6>{l s='Webhooks' mod='paypal'}</h6>
<p>
  {l s='Activating webhooks allows PayPal to update the status of your PrestaShop orders as soon as the payment status changes. You must ensure that webhooks are received by your PrestaShop.' mod='paypal'}
</p>
<p>
  {l s='We have also created an event that will call up order statuses every hour when you browse your PrestaShop back office.' mod='paypal'}
</p>
<p>
  {l s='Another solution if webhooks do not work: you can request to install a cron job on your server that will update your order statuses according to PayPal payment statuses, at the frequency you desire:' mod='paypal'} <b>{$checkAndHandleWebhookNotification}</b>
</p>







