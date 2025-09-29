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

namespace PaypalAddons\classes\API\Request;

use PaypalAddons\classes\AbstractMethodPaypal;
use PaypalAddons\classes\API\Client\HttpClient;
use PaypalAddons\classes\API\ExtensionSDK\Order\OrdersPatchRequest;
use PaypalAddons\classes\API\HttpAdoptedResponse;
use PaypalAddons\classes\API\Response\Error;
use PaypalAddons\classes\API\Response\Response;
use PaypalAddons\classes\PaypalException;
use PaypalAddons\services\Builder\OrderPatchBody;

if (!defined('_PS_VERSION_')) {
    exit;
}

class PaypalOrderPatchRequest extends PaypalOrderCreateRequest
{
    /** @var string */
    protected $idPayment;

    public function __construct(HttpClient $client, AbstractMethodPaypal $method, $idPayment)
    {
        parent::__construct($client, $method);
        $this->idPayment = $idPayment;
    }

    public function execute()
    {
        $response = new Response();
        $orderPath = new OrdersPatchRequest($this->idPayment, $this->initBodyBuilder());

        try {
            $exec = $this->client->execute($orderPath);

            if ($exec instanceof HttpAdoptedResponse) {
                $exec = $exec->getAdoptedResponse();
            }

            if ($exec->statusCode == 204) {
                $response->setSuccess(true);
            } else {
                $error = new Error();
                $error->setMessage('Failed order update');
                $response->setSuccess(false)->setError($error);
            }
        } catch (PaypalException $e) {
            $error = new Error();
            $resultDecoded = json_decode($e->getMessage());
            $error->setMessage($resultDecoded->details[0]->description)->setErrorCode($e->getCode());
            $response->setSuccess(false)
                ->setError($error);
        } catch (\Throwable $e) {
            $error = new Error();
            $error->setErrorCode($e->getCode())->setMessage($e->getMessage());
            $response->setError($error)->setSuccess(false);
        }

        return $response;
    }

    protected function initBodyBuilder()
    {
        return new OrderPatchBody($this->context, $this->method);
    }
}
