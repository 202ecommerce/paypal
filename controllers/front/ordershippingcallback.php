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
use PaypalAddons\services\Builder\OrderShippingCallbackResponseBody;
use PaypalAddons\services\CustomId;
use PaypalPPBTlib\Extensions\ProcessLogger\ProcessLoggerHandler;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Receives PayPal's shipping callback (SHIPPING_ADDRESS / SHIPPING_OPTIONS) fired while the
 * buyer edits their address/shipping option inside the shortcut (Smart Payment Button) popup.
 *
 * @see https://developer.paypal.com/docs/checkout/standard/customize/shipping-module
 */
class PaypalOrdershippingcallbackModuleFrontController extends PaypalAbstarctModuleFrontController
{
    /** @var string Raw request body */
    protected $request;

    /** @var AbstractMethodPaypal */
    protected $method;

    public function __construct()
    {
        parent::__construct();
        $this->request = Tools::file_get_contents('php://input');
        $this->method = AbstractMethodPaypal::load();
    }

    public function run()
    {
        parent::init();

        if (hash_equals((string) $this->module->secure_key, (string) Tools::getValue('_token')) === false) {
            $this->logError('Invalid or missing token in shipping callback request', null);
            $this->respond(401);

            return;
        }

        try {
            $requestData = json_decode($this->request, true);

            if (is_array($requestData) === false) {
                $this->respond(400);

                return;
            }

            if (empty($requestData['id']) || empty($requestData['shipping_address']['country_code'])) {
                $this->respond(400);

                return;
            }

            $idCart = $this->getCartIdFromRequest($requestData);

            if (empty($idCart)) {
                $this->logError('Unable to resolve cart id from shipping callback custom_id. Payment id: ' . $requestData['id'], null);
                $this->respond(400);

                return;
            }

            $cart = new Cart($idCart);

            if (Validate::isLoadedObject($cart) === false) {
                $this->logError('Cart not found for shipping callback', $idCart);
                $this->respond(400);

                return;
            }

            $this->setShopContext($cart);
            $this->setCurrencyContext($cart);

            $responseBody = new OrderShippingCallbackResponseBody($cart, $this->method, $requestData);
            $selectedCarrier = $responseBody->getSelectedCarrier();

            if (empty($selectedCarrier)) {
                $this->respond(422, ['name' => 'COUNTRY_ERROR']);

                return;
            }

            // Persist immediately so the PrestaShop order created after capture uses the same
            // carrier/shipping cost PayPal actually charged (scOrder.php only re-syncs the
            // delivery address, never the carrier).
            $cart->id_carrier = (int) $selectedCarrier['id_carrier'];
            $cart->update();

            $this->respond(200, $responseBody->build());
        } catch (Throwable $exception) {
            $this->logError('Error code: ' . $exception->getCode() . '. Short message: ' . $exception->getMessage() . '.', isset($idCart) ? $idCart : null);
            $this->respond(500);
        }
    }

    /**
     * Resolves the cart id from the callback payload's `custom_id`, which PayPal echoes
     * back unchanged on `purchase_units[0].custom_id` from what was sent at order-create
     * time (@see \PaypalAddons\services\CustomId::build()).
     *
     * @param array $requestData
     *
     * @return int|null
     */
    protected function getCartIdFromRequest(array $requestData)
    {
        $customId = isset($requestData['purchase_units'][0]['custom_id']) ? $requestData['purchase_units'][0]['custom_id'] : '';

        return (new CustomId())->getCartId($customId);
    }

    protected function setShopContext(Cart $cart)
    {
        $shop = new Shop((int) $cart->id_shop);

        if (Validate::isLoadedObject($shop) === false) {
            return;
        }

        $this->context->shop = $shop;
        Shop::setContext(Shop::CONTEXT_SHOP, $shop->id);
    }

    /**
     * The server-to-server callback carries no cookie/session, so `Context::getContext()->currency`
     * defaults to the shop's default currency rather than the one the cart was actually priced in.
     * `OrderCreateBody::getCurrency()` (used to price the carriers) reads from context, so it must
     * be aligned with the cart's own currency before building amounts.
     *
     * @param Cart $cart
     */
    protected function setCurrencyContext(Cart $cart)
    {
        $currency = new Currency((int) $cart->id_currency);

        if (Validate::isLoadedObject($currency) === false) {
            return;
        }

        $this->context->currency = $currency;
    }

    /**
     * @param int $statusCode
     * @param array|null $body
     */
    protected function respond($statusCode, $body = null)
    {
        $statusMessages = [
            200 => 'OK',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            422 => 'Unprocessable Content',
            500 => 'Internal Server Error',
        ];
        header($_SERVER['SERVER_PROTOCOL'] . ' ' . $statusCode . ' ' . $statusMessages[$statusCode], true, $statusCode);

        if ($body !== null) {
            header('Content-Type: application/json');
            echo json_encode($body);
        }
    }

    protected function logError($message, $idCart)
    {
        ProcessLoggerHandler::openLogger();
        ProcessLoggerHandler::logError(
            Tools::substr($message, 0, 999),
            null,
            null,
            $idCart,
            null,
            null,
            (int) Configuration::get('PAYPAL_SANDBOX'),
            null
        );
        ProcessLoggerHandler::closeLogger();
    }

    protected static function isInWhitelistForGeolocation()
    {
        return true;
    }

    protected function displayMaintenancePage()
    {
    }
}
