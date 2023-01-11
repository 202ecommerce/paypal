<?php

namespace PaypalTests;

use Order;
use PaypalAddons\classes\Webhook\WebhookEventHandler;
use PaypalAddons\services\StatusMapping;

class WebhookTest extends AbstractTest
{
    public function testCompletedEvent()
    {
        $event = $this->initWebhookEvent('event-completed.json');
        $statusMap = new StatusMapping();
        $webhookHandler = new WebhookEventHandler();
        $idOrder = $this->createOrderForWebhookEvent($event, $statusMap->getWaitValidationStatus());
        $webhookHandler->handle($event);
        $order = new Order($idOrder);

        $this->assertEquals($statusMap->getAcceptedStatus(), $order->current_state);
    }

    public function testPendingEvent()
    {
        $event = $this->initWebhookEvent('event-pending.json');
        $statusMap = new StatusMapping();
        $webhookHandler = new WebhookEventHandler();
        $idOrder = $this->createOrderForWebhookEvent($event, $statusMap->getWaitValidationStatus());
        $webhookHandler->handle($event);
        $order = new Order($idOrder);

        $this->assertEquals($statusMap->getWaitValidationStatus(), $order->current_state);
    }

    public function testRefundedEvent()
    {
        $event = $this->initWebhookEvent('event-refunded.json');
        $statusMap = new StatusMapping();
        $webhookHandler = $this
            ->getMockBuilder(WebhookEventHandler::class)
            ->onlyMethods(['getPaymentTotal'])
            ->getMock();
        $webhookHandler->method('getPaymentTotal')->willReturn(0);

        $idOrder = $this->createOrderForWebhookEvent($event, $statusMap->getAcceptedStatus());
        $webhookHandler->handle($event);
        $order = new Order($idOrder);

        $this->assertEquals($statusMap->getRefundStatus(), $order->current_state);
    }

    public function testPartialRefundedEvent()
    {
        $event = $this->initWebhookEvent('event-partial-refunded.json');
        $statusMap = new StatusMapping();
        $webhookHandler = $this
            ->getMockBuilder(WebhookEventHandler::class)
            ->onlyMethods(['getPaymentTotal'])
            ->getMock();
        $webhookHandler->method('getPaymentTotal')->willReturn(5);

        $idOrder = $this->createOrderForWebhookEvent($event, $statusMap->getAcceptedStatus());
        $webhookHandler->handle($event);
        $order = new Order($idOrder);

        $this->assertEquals($statusMap->getAcceptedStatus(), $order->current_state);
    }

    public function testCaptureDeniedEvent()
    {
        $event = $this->initWebhookEvent('event-capture-denied.json');
        $statusMap = new StatusMapping();
        $webhookHandler = new WebhookEventHandler();
        $idOrder = $this->createOrderForWebhookEvent($event, $statusMap->getWaitValidationStatus());
        $webhookHandler->handle($event);
        $order = new Order($idOrder);

        $this->assertEquals($statusMap->getCanceledStatus(), $order->current_state);
    }

    public function testAuthorizationVoidedEvent()
    {
        $event = $this->initWebhookEvent('event-authorization-voided.json');
        $statusMap = new StatusMapping();
        $webhookHandler = new WebhookEventHandler();
        $idOrder = $this->createOrderForWebhookEvent($event, $statusMap->getWaitValidationStatus());
        $webhookHandler->handle($event);
        $order = new Order($idOrder);

        $this->assertEquals($statusMap->getCanceledStatus(), $order->current_state);
    }

    public function testCaptureReversedEvent()
    {
        $event = $this->initWebhookEvent('event-capture-reversed.json');
        $statusMap = new StatusMapping();
        $webhookHandler = new WebhookEventHandler();
        $idOrder = $this->createOrderForWebhookEvent($event, $statusMap->getWaitValidationStatus());
        $webhookHandler->handle($event);
        $order = new Order($idOrder);

        $this->assertEquals($statusMap->getRefundStatus(), $order->current_state);
    }

    public function testCompletedEventWhenCurrentStatePsOutOfStock()
    {
        $event = $this->initWebhookEvent('event-completed-for-ps-out-of-stock.json');
        $statusMap = new StatusMapping();
        $webhookHandler = new WebhookEventHandler();
        $idOrder = $this->createOrderForWebhookEvent($event, $statusMap->getPsOutOfStock());
        $webhookHandler->handle($event);
        $order = new Order($idOrder);

        $this->assertEquals($statusMap->getAcceptedStatus(), $order->current_state);
    }
}
