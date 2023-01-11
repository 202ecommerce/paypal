<?php
/*
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
 */


namespace PaypalTests;

use Cart;
use Configuration;
use DateTime;
use Module;
use PayPal\Api\WebhookEvent;
use PHPUnit\Framework\TestCase;

abstract class AbstractTest extends TestCase
{
    protected function createOrderForWebhookEvent(WebhookEvent $event, $idOrderStatus)
    {
        $module = Module::getInstanceByName('paypal');
        $cart = new Cart();
        $cart->id_carrier = 0;
        $cart->id_customer = $this->getDefaultCustomerId();
        $cart->id_address_delivery = $this->getDefaultAddressId();
        $cart->id_address_invoice = $this->getDefaultAddressId();
        $cart->id_currency = $this->getDefaultCurrencyId();
        $cart->save();
        $cart->updateQty(1, $this->getDefaultProductId());

        $transactionDetail = [
            'method' => $this->getDefaultMethod(),
            'currency' => $event->getResource()->amount->currency_code,
            'payment_status' => $event->getResource()->status,
            'payment_method' => 'paypal',
            'id_payment' => empty($event->getResource()->supplementary_data->related_ids->order_id) ? '' : $event->getResource()->supplementary_data->related_ids->order_id,
            'payment_tool' => '',
            'date_transaction' => (new DateTime())->format('Y-m-d H:i:s'),
            'transaction_id' => $event->getResource()->id,
            'capture' => true,
            'intent' => 'CAPTURE',
        ];

        $module->validateOrder(
            $cart->id,
            $idOrderStatus,
            (float) $event->getResource()->amount->value,
            'paypal',
            null,
            $transactionDetail,
            $this->getDefaultCurrencyId()
        );

        return $module->currentOrder;
    }

    protected function getDefaultCustomerId()
    {
        return 2;
    }

    protected function getDefaultAddressId()
    {
        return 2;
    }

    protected function getDefaultProductId()
    {
        return 12;
    }

    protected function getDefaultCurrencyId()
    {
        return (int) Configuration::get('PS_CURRENCY_DEFAULT');
    }

    protected function getDefaultMethod()
    {
        return 'EC';
    }

    protected function initWebhookEvent($file)
    {
        $webhookEvent = new WebhookEvent();
        $webhookEvent->fromJson(file_get_contents(__DIR__ . '/dataset/' . $file));

        return $webhookEvent;
    }

}
