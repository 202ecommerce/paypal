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

namespace PaypalAddons\classes\API\Injector;

if (!defined('_PS_VERSION_')) {
    exit;
}

use PaypalAddons\classes\AbstractMethodPaypal;
use PaypalAddons\classes\API\Client\HttpClient;
use PaypalAddons\classes\API\ExtensionSDK\AccessTokenRequest;
use PaypalAddons\classes\API\HttpJsonResponse;
use PaypalAddons\classes\API\HttpResponse;
use PaypalAddons\classes\API\InjectorInterface;
use PaypalAddons\classes\API\Request\HttpRequestInterface;
use PaypalAddons\classes\API\Token\BasicToken;
use PaypalAddons\classes\API\Token\OAuthToken;
use PaypalAddons\classes\API\TokenInterface;

class AuthorizationInjector implements InjectorInterface
{
    /** @var HttpClient */
    protected $client;
    /** @var AbstractMethodPaypal */
    protected $method;

    public function __construct(HttpClient $client, AbstractMethodPaypal $method)
    {
        $this->client = $client;
        $this->method = $method;
    }

    /** @return void*/
    public function inject(&$object)
    {
        if (false === $object instanceof HttpRequestInterface) {
            return;
        }

        $headers = $object->getHeaders();

        if (isset($headers['Authorization'])) {
            return;
        }

        if ($object instanceof AccessTokenRequest) {
            $headers['Authorization'] = 'Basic ' . $this->initBasicToken()->getToken();
            $object->setHeaders($headers);

            return;
        }

        $oauthToken = $this->initOAuthToken();

        if ($oauthToken->isEligible()) {
            $headers['Authorization'] = 'Bearer ' . $oauthToken->getToken();
            $object->setHeaders($headers);

            return;
        }
        /** @var HttpJsonResponse $getAccessTokenResponse */
        $getAccessTokenResponse = $this->fetchToken();

        if (false === $oauthToken->update($getAccessTokenResponse->toArray())) {
            return;
        }

        $headers['Authorization'] = 'Bearer ' . $oauthToken->getToken();
        $object->setHeaders($headers);
    }

    /** @return TokenInterface*/
    protected function initBasicToken()
    {
        return new BasicToken($this->method);
    }

    /** @return TokenInterface*/
    protected function initOAuthToken()
    {
        return new OAuthToken($this->method);
    }

    /** @return HttpResponse*/
    protected function fetchToken()
    {
        return $this->client->execute(new AccessTokenRequest());
    }
}
