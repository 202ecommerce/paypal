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

namespace PaypalAddons\classes\API\Onboarding;

use PaypalAddons\classes\AbstractMethodPaypal;
use PaypalAddons\classes\API\ExtensionSDK\AccessTokenRequest;
use PaypalAddons\classes\API\HttpAdoptedResponse;
use PaypalAddons\classes\API\PaypalClient;
use PaypalAddons\classes\API\Response\Error;
use PaypalAddons\classes\API\Response\ResponseGetAuthToken;

if (!defined('_PS_VERSION_')) {
    exit;
}

class PaypalGetAuthToken
{
    /** @var PaypalClient */
    protected $client;

    /** @var string */
    protected $authCode;

    /** @var string */
    protected $sharedId;

    /** @var string */
    protected $sellerNonce;

    public function __construct($authCode, $sharedId, $sellerNonce, $sandbox)
    {
        $method = AbstractMethodPaypal::load();
        $method->setSandbox($sandbox);
        $this->client = PaypalClient::get($method);
        $this->authCode = $authCode;
        $this->sharedId = $sharedId;
        $this->sellerNonce = $sellerNonce;
    }

    /**
     * @return ResponseGetAuthToken
     */
    public function execute()
    {
        $returnResponse = new ResponseGetAuthToken();
        $request = new AccessTokenRequest();
        $request->setBody(
            sprintf('grant_type=authorization_code&code=%s&code_verifier=%s', $this->authCode, $this->sellerNonce)
        );
        $request->setHeaders([
            'Content-Type' => 'text/plain',
            'Authorization' => 'Basic ' . base64_encode($this->sharedId),
        ]);

        try {
            /** @var HttpAdoptedResponse $response */
            $response = $this->client->execute($request);
            $responseDecode = $response->getAdoptedResponse();
            $returnResponse->setSuccess(true)
                ->setData($response)
                ->setAuthToken($responseDecode->result->access_token)
                ->setRefreshToken($responseDecode->result->refresh_token)
                ->setTokenType($responseDecode->result->token_type)
                ->setNonce($responseDecode->result->nonce);
        } catch (\Throwable $e) {
            $error = new Error();
            $error
                ->setMessage($e->getMessage())
                ->setErrorCode($e->getCode());
            $returnResponse->setError($error)->setSuccess(false);
        }

        return $returnResponse;
    }
}
