<?php

/*
 * Since 2007 PayPal
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
 *  @author Since 2007 PayPal
 *  @author 202 ecommerce <tech@202-ecommerce.com>
 *  @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 *  @copyright PayPal
 *
 */

use PaypalAddons\classes\AbstractMethodPaypal;
use PaypalAddons\classes\API\Model\WebhookEvent;
use PaypalAddons\classes\Constants\WebhookHandler;
use PaypalAddons\classes\Webhook\WebhookEventHandler;
use PaypalPPBTlib\Extensions\ProcessLogger\ProcessLoggerHandler;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class PaypalAbstarctModuleFrontController
 */
class PaypalWebhookhandlerModuleFrontController extends PaypalAbstarctModuleFrontController
{
    /** @var array */
    protected $requestData;

    protected $request;

    protected $webhookEventHandler;

    protected $method;
    /** @var PayPal */
    public $module;

    public function __construct()
    {
        parent::__construct();

        $this->request = Tools::file_get_contents('php://input');
        $this->webhookEventHandler = new WebhookEventHandler();
        $this->method = AbstractMethodPaypal::load();
    }

    public function run()
    {
        parent::init();

        if ($this->isCheckAvailability()) {
            header($_SERVER['SERVER_PROTOCOL'] . ' ' . WebhookHandler::STATUS_AVAILABLE);
            exit;
        }

        if (false == ($this->module->getWebhookOption()->isEnable() && $this->module->getWebhookOption()->isAvailable())) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 405 Method Not Allowed', true, 405);

            return;
        }

        try {
            $this->setShopContext();
            if ($this->requestIsValid()) {
                $webhookEvent = new WebhookEvent();
                $webhookEvent->fromArray($this->getRequestData());

                if ($this->webhookEventHandler->handle($webhookEvent)) {
                    header($_SERVER['SERVER_PROTOCOL'] . ' 200 OK');
                } else {
                    header($_SERVER['SERVER_PROTOCOL'] . ' 422 Unprocessable Content', true, 422);
                }
            } else {
                $paypalOrder = $this->initPaypalOrder($this->getRequestData());

                ProcessLoggerHandler::openLogger();
                ProcessLoggerHandler::logError(
                    Tools::substr('Invalid webhook event. Data: ' . $this->getRequest(), 0, 999),
                    $paypalOrder->id_transaction,
                    Validate::isLoadedObject($paypalOrder) ? $paypalOrder->id_order : null,
                    Validate::isLoadedObject($paypalOrder) ? $paypalOrder->id_cart : null,
                    null,
                    null,
                    Validate::isLoadedObject($paypalOrder) ? $paypalOrder->sandbox : (int) Configuration::get('PAYPAL_SANDBOX'),
                    null
                );
                ProcessLoggerHandler::closeLogger();
                header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad Request', true, 400);
            }
        } catch (Throwable $exception) {
            $message = 'Error code: ' . $exception->getCode() . '.';
            $message .= 'Short message: ' . $exception->getMessage() . '.';
            $paypalOrder = $this->initPaypalOrder($this->getRequestData());

            ProcessLoggerHandler::openLogger();
            ProcessLoggerHandler::logError(
                $message,
                $paypalOrder->id_transaction,
                Validate::isLoadedObject($paypalOrder) ? $paypalOrder->id_order : null,
                Validate::isLoadedObject($paypalOrder) ? $paypalOrder->id_cart : null,
                null,
                null,
                Validate::isLoadedObject($paypalOrder) ? $paypalOrder->sandbox : (int) Configuration::get('PAYPAL_SANDBOX'),
                null
            );
            ProcessLoggerHandler::closeLogger();

            header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
        }
    }

    protected function isCheckAvailability()
    {
        return (bool) Tools::isSubmit('checkAvailability');
    }

    /**
     * @return bool
     */
    protected function requestIsValid()
    {
        try {
            if (empty($this->getRequestData()['id'])) {
                return false;
            }

            $response = $this->method->getWebhookEventDetail($this->getRequestData()['id']);

            if ($response->isSuccess() === false) {
                return false;
            }

            $this->request = $response->getEvent()->toJson();
            $this->requestData = $response->getEvent()->toArray();
        } catch (Exception $e) {
            return false;
        }

        return true;
    }

    protected function getRequestData()
    {
        if (false == empty($this->requestData)) {
            return $this->requestData;
        }

        $this->requestData = json_decode($this->getRequest(), true);

        return $this->requestData;
    }

    protected function getRequest()
    {
        return $this->request;
    }

    protected static function isInWhitelistForGeolocation()
    {
        return true;
    }

    protected function displayMaintenancePage()
    {
    }

    protected function initPaypalOrder($requestData)
    {
        $event = new WebhookEvent();
        $event->fromArray($requestData);
        /* @phpstan-ignore-next-line */
        if (false == empty($event->getResource()->supplementary_data->related_ids->order_id)) {
            $paymentId = $event->getResource()->supplementary_data->related_ids->order_id;

            return $this->module->getPaypalOrderService()->getPaypalOrderByPaymentId($paymentId);
        }

        return new PaypalOrder();
    }

    protected function setShopContext()
    {
        $event = new WebhookEvent();
        $event->fromArray($this->getRequestData());
        $cart = new Cart($event->getCartId());

        if (false === Validate::isLoadedObject($cart)) {
            return;
        }

        $shop = new Shop($cart->id_shop);
        $this->context->shop = $shop;
        Shop::setContext(Shop::CONTEXT_SHOP, $shop->id);
    }
}
