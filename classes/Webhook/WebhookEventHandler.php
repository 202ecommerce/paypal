<?php
/**
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
 */

namespace PaypalAddons\classes\Webhook;

use Configuration;
use Context;
use Db;
use DbQuery;
use Employee;
use Exception;
use PayPal\Api\WebhookEvent;
use PaypalAddons\classes\Constants\WebHookType;
use PaypalAddons\services\ActualizeTotalPaid;
use PaypalAddons\services\PaymentTotalAmount;
use PaypalAddons\services\PaypalContext;
use PaypalAddons\services\ServicePaypalOrder;
use PaypalAddons\services\StatusMapping;
use PaypalAddons\services\WebhookService;
use PaypalCapture;
use PaypalOrder;
use PaypalPPBTlib\Extensions\ProcessLogger\ProcessLoggerHandler;
use PaypalWebhook;
use Shop;
use Throwable;
use Tools;
use Validate;

class WebhookEventHandler
{
    protected $servicePaypalOrder;

    protected $context;

    public function __construct()
    {
        $this->servicePaypalOrder = new ServicePaypalOrder();
        $this->context = Context::getContext();
    }

    public function handle(WebhookEvent $event)
    {
        if ($this->alreadyHandled($event)) {
            return true;
        }

        $this->init();
        ProcessLoggerHandler::openLogger();
        $msg = 'Webhook event : ' . $this->jsonEncode([
                'event_type' => $event->getEventType(),
                'webhook_id' => $event->getId(),
                'data' => $event->toArray(),
            ]);
        $msg = Tools::substr($msg, 0, 999);

        if ($event->resource->status != 'COMPLETED') {
            ProcessLoggerHandler::logInfo(
                $msg,
                empty($event->getResource()->id) ? '' : $event->getResource()->id,
                null,
                null,
                null,
                'PayPal',
                (int) Configuration::get('PAYPAL_SANDBOX')
            );
            ProcessLoggerHandler::closeLogger();

            return true;
        }

        $paypalOrder = $this->initPaypalOrder($event);

        if (Validate::isLoadedObject($paypalOrder) == false) {
            return false;
        }

        $orders = $this->servicePaypalOrder->getPsOrders($paypalOrder);
        $psOrderStatus = $this->getPsOrderStatus($event);
        PaypalContext::getContext()->set('skipHandleHookActionOrderStatusUpdate', true);

        foreach ($orders as $order) {
            //If there are several shops, then PayPal sends webhook event to each shop. The module should
            //handle the event once.
            if ($this->isMultishop()) {
                if ($order->id_shop != $this->context->shop->id) {
                    return false;
                }
            }

            ProcessLoggerHandler::logInfo(
                $msg,
                empty($event->getResource()->id) ? '' : $event->getResource()->id,
                $order->id,
                $order->id_cart,
                $order->id_shop,
                'PayPal',
                (int) Configuration::get('PAYPAL_SANDBOX')
            );

            if ($psOrderStatus == 0) {
                continue;
            }

            if ($order->current_state == $psOrderStatus) {
                continue;
            }

            if ($event->getEventType() == WebHookType::CAPTURE_COMPLETED) {
                if (false === in_array($order->current_state, [$this->getStatusMapping()->getWaitValidationStatus(), $this->getStatusMapping()->getPsOutOfStock()])) {
                    continue;
                }
            }

            if ($this->getStatusMapping()->getRefundStatus() == $psOrderStatus) {
                $paypalOrder->payment_status = 'refunded';
                $paypalOrder->save();
            }

            $order->setCurrentState($psOrderStatus);
        }
        ProcessLoggerHandler::closeLogger();

        if ($this->isCaptureAuthorization($event)) {
            $capture = PaypalCapture::loadByOrderPayPalId($paypalOrder->id);

            if (Validate::isLoadedObject($capture)) {
                $capture->id_capture = empty($event->getResource()->id) ? '' : $event->getResource()->id;
                $capture->result = empty($event->getResource()->status) ? '' : $event->getResource()->status;
                $capture->capture_amount = $this->getAmount($event);
                $capture->save();
            }

            $this->actualizeOrder($paypalOrder, $event);
        }

        if ($psOrderStatus == $this->getStatusMapping()->getCanceledStatus()) {
            $this->removeEventInWaiting($paypalOrder);
        }

        $paypalWebhook = $this->getWebhookService()->createForOrder($paypalOrder, $psOrderStatus);
        $paypalWebhook->id_webhook = $event->getId();
        $paypalWebhook->event_type = $event->getEventType();
        $paypalWebhook->data = $event->toJSON();
        $paypalWebhook->date_completed = date(PaypalWebhook::DATE_FORMAT);
        // Trying to save a webhook event without field 'data' if there is an error
        try {
            $paypalWebhook->save();
        } catch (Throwable $e) {
            $paypalWebhook->data = '';
            $paypalOrder->save();
        } catch (Exception $e) {
            $paypalWebhook->data = '';
            $paypalOrder->save();
        }

        if ($psOrderStatus == $this->getStatusMapping()->getAcceptedStatus()) {
            $this->servicePaypalOrder->setTransactionId($paypalOrder, $event->getResource()->id);
        }

        return true;
    }

    protected function alreadyHandled(WebhookEvent $event)
    {
        $query = (new DbQuery())
            ->from(PaypalWebhook::$definition['table'])
            ->where('id_webhook = \'' . pSQL($event->getId()) . '\'')
            ->select(PaypalWebhook::$definition['primary']);

        try {
            return (bool) Db::getInstance()->getValue($query);
        } catch (Throwable $e) {
            return false;
        } catch (Exception $e) {
            return false;
        }
    }

    protected function initPaypalOrder(WebhookEvent $event)
    {
        if (false == empty($event->getResource()->supplementary_data->related_ids->order_id)) {
            $paymentId = $event->getResource()->supplementary_data->related_ids->order_id;

            return $this->servicePaypalOrder->getPaypalOrderByPaymentId($paymentId);
        }

        $transaction = $this->getTransactionRef($event);

        if (false == empty($transaction)) {
            return $this->servicePaypalOrder->getPaypalOrderByTransaction($transaction);
        }

        return new PaypalOrder();
    }

    protected function getTransactionRef(WebhookEvent $event)
    {
        if ($this->isCaptureAuthorization($event)) {
            return $this->getAuthorizationId($event);
        }

        if ($event->getEventType() == WebHookType::CAPTURE_REFUNDED) {
            foreach ($event->getResource()->links as $link) {
                if ($link->rel == 'up') {
                    return $this->getTransactionFromHref($link->href);
                }
            }
        }

        return empty($event->getResource()->id) ? '' : (string) $event->getResource()->id;
    }

    /**
     * @param mixed $data
     *
     * @return bool
     */
    protected function isCaptureAuthorization(WebhookEvent $event)
    {
        return (bool) $this->getAuthorizationId($event);
    }

    /**
     * @param mixed $data
     *
     * @return string
     */
    protected function getAuthorizationId(WebhookEvent $event)
    {
        try {
            return $event->getResource()->supplementary_data->related_ids->authorization_id;
        } catch (Throwable $e) {
            return '';
        } catch (Exception $e) {
            return '';
        }
    }

    /**
     * @param mixed $data
     *
     * @return float
     */
    protected function getAmount(WebhookEvent $event)
    {
        try {
            return (float) $event->getResource()->amount->value;
        } catch (Throwable $e) {
            return 0;
        } catch (Exception $e) {
            return 0;
        }
    }

    protected function getWebhookService()
    {
        return new WebhookService();
    }

    protected static function isInWhitelistForGeolocation()
    {
        return true;
    }

    protected function displayMaintenancePage()
    {
    }

    protected function actualizeOrder(PaypalOrder $paypalOrder, WebhookEvent $webhookEvent)
    {
        $orders = $this->servicePaypalOrder->getPsOrders($paypalOrder);

        if (count($orders) > 1) {
            // todo: implement
            return;
        }

        $order = array_shift($orders);

        if (empty($webhookEvent->resource->amount->value)) {
            return;
        }

        $this->getActualizeTotalPaid()->actualize($order, $webhookEvent->resource->amount->value);
    }

    /**
     * @return ActualizeTotalPaid
     */
    protected function getActualizeTotalPaid()
    {
        return new ActualizeTotalPaid();
    }

    protected function removeEventInWaiting(PaypalOrder $paypalOrder)
    {
        $webhookEvents = $this->getWebhookService()->getPendingWebhooks($paypalOrder);

        if (empty($webhookEvents)) {
            return;
        }

        foreach ($webhookEvents as $webhookEvent) {
            $webhookEvent->delete();
        }
    }

    protected function jsonEncode($value)
    {
        $result = json_encode($value);

        if (json_last_error() == JSON_ERROR_UTF8) {
            $result = json_encode($this->utf8ize($value));
        }

        return $result;
    }

    protected function utf8ize($mixed)
    {
        if (is_array($mixed)) {
            foreach ($mixed as $key => $value) {
                $mixed[$key] = $this->utf8ize($value);
            }
        } elseif (is_string($mixed)) {
            return utf8_encode($mixed);
        }

        return $mixed;
    }

    /**
     * @param string $href
     *
     * @return string
     */
    protected function getTransactionFromHref($href)
    {
        $tmp = explode('/', $href);

        return (string) array_pop($tmp);
    }

    protected function getPsOrderStatus(WebhookEvent $event)
    {
        if ($event->getEventType() == WebHookType::CAPTURE_REFUNDED) {
            $paymentTotal = $this->getPaymentTotal($event);

            if ($paymentTotal > 0) {
                return 0;
            }
        }

        return $this->getStatusMapping()->getPsOrderStatusByEventType($event->getEventType());
    }

    protected function getPaymentTotal(WebhookEvent $event)
    {
        $paypalOrder = $this->initPaypalOrder($event);

        return $this->getPaymentTotalAmountService()->get($paypalOrder);
    }

    protected function getPaymentTotalAmountService()
    {
        return new PaymentTotalAmount();
    }

    /** @return StatusMapping*/
    protected function getStatusMapping()
    {
        return new StatusMapping();
    }

    protected function isMultishop()
    {
        return Shop::isFeatureActive();
    }

    protected function init()
    {
        if ($this->context->employee == null) {
            $this->setEmployeeInContext();
        }
    }

    protected function setEmployeeInContext()
    {
        $employees = Employee::getEmployeesByProfile(1);

        if (false === empty($employees)) {
            $employee = new Employee((int) $employees[0]);
            $this->context->employee = $employee;
        }
    }
}
