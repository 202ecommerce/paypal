<?php

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
