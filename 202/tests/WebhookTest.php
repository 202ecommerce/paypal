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
