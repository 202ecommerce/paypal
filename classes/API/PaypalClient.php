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

namespace PaypalAddons\classes\API;

use PaypalAddons\classes\AbstractMethodPaypal;
use PaypalAddons\classes\API\Client\HttpClient;
use PaypalAddons\classes\API\Environment\PaypalEnvironment;
use PaypalAddons\classes\API\ExtensionSDK\AccessTokenRequest;
use PaypalAddons\classes\API\ExtensionSDK\Order\OrdersCreateRequest;
use PaypalAddons\classes\API\Injector\AuthorizationInjector;
use PaypalAddons\classes\API\Injector\BnCodeInjector;
use PaypalAddons\classes\API\Injector\UserAgentInjector;
use PaypalAddons\classes\API\Request\HttpRequestInterface;
use PaypalPPBTlib\Extensions\ProcessLogger\ProcessLoggerHandler;

if (!defined('_PS_VERSION_')) {
    exit;
}

class PaypalClient extends HttpClient
{
    public function __construct($method)
    {
        parent::__construct(new PaypalEnvironment($method));

        $this->addInjector(new AuthorizationInjector($this, $method));
        $this->addInjector(new BnCodeInjector($method));
        $this->addInjector(new UserAgentInjector());
    }

    public static function get(AbstractMethodPaypal $method)
    {
        return new self($method);
    }

    public function execute(HttpRequestInterface $httpRequest)
    {
        if ($httpRequest instanceof AccessTokenRequest) {
            return parent::execute($httpRequest);
        }

        try {
            $this->logRequest($httpRequest);
            $response = parent::execute($httpRequest);
            $this->logResponse($response);
        } catch (\Throwable $e) {
            $this->logException($e);
            throw $e;
        }

        return $response;
    }

    protected function logRequest(HttpRequestInterface $httpRequest)
    {
        $message = sprintf('[Request][%s] ', get_class($httpRequest));
        $message .= 'Path: ' . $httpRequest->getPath() . '; ';
        $body = null;
        $headers = $httpRequest->getHeaders();

        if ($httpRequest instanceof OrdersCreateRequest) {
            if (false === empty($httpRequest->getBody()['purchase_units'][0]['amount'])) {
                $body = $httpRequest->getBody()['purchase_units'][0]['amount'];
            }
        } else {
            $body = $httpRequest->getBody();
        }

        if ($body) {
            if (is_string($body)) {
                $message .= 'POST-BODY: ' . $body . '; ';
            } else {
                $message .= 'POST-BODY: ' . json_encode($body) . '; ';
            }
        }
        if (isset($headers['Authorization'])) {
            unset($headers['Authorization']);
        }
        if (false === empty($headers)) {
            $message .= 'Headers: ' . json_encode($headers);
        }

        ProcessLoggerHandler::openLogger();
        ProcessLoggerHandler::logInfo(
            $message,
            null,
            null,
            empty(\Context::getContext()->cart->id) ? null : \Context::getContext()->cart->id,
            \Context::getContext()->shop->id,
            null,
            (bool) \Configuration::get('PAYPAL_SANDBOX')
        );
        ProcessLoggerHandler::closeLogger();
    }

    /**
     * @param \Throwable $exception
     */
    protected function logException($exception)
    {
        $message = '[RequestException] ';
        $message .= 'Message: ' . $exception->getMessage();

        ProcessLoggerHandler::openLogger();
        ProcessLoggerHandler::logError(
            $message,
            null,
            null,
            empty(\Context::getContext()->cart->id) ? null : \Context::getContext()->cart->id,
            \Context::getContext()->shop->id,
            null,
            (bool) \Configuration::get('PAYPAL_SANDBOX')
        );
        ProcessLoggerHandler::closeLogger();
    }

    protected function logResponse(HttpResponse $response)
    {
        $message = '[Response] ';
        $message .= 'Code: ' . $response->getCode();

        ProcessLoggerHandler::openLogger();
        ProcessLoggerHandler::logInfo(
            $message,
            null,
            null,
            empty(\Context::getContext()->cart->id) ? null : \Context::getContext()->cart->id,
            \Context::getContext()->shop->id,
            null,
            (bool) \Configuration::get('PAYPAL_SANDBOX')
        );
        ProcessLoggerHandler::closeLogger();
    }
}
