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
use PaypalAddons\classes\API\ExtensionSDK\ConfirmPaymentSource;
use PaypalAddons\classes\API\HttpAdoptedResponse;
use PaypalAddons\classes\API\Response\Error;
use PaypalAddons\classes\API\Response\ResponseConfirmationPaymentSource;
use PaypalAddons\classes\PaypalException;
use PaypalAddons\services\Builder\ConfirmPaymentSourceBuilder;

if (!defined('_PS_VERSION_')) {
    exit;
}

class PaypalConfirmPaymentSourceRequest extends RequestAbstract
{
    protected $paypalOrderId;

    protected $apmMethod;

    public function __construct(HttpClient $client, AbstractMethodPaypal $method, $paypalOrderId, $apmMethod)
    {
        parent::__construct($client, $method);

        $this->paypalOrderId = (string) $paypalOrderId;
        $this->apmMethod = (string) $apmMethod;
    }

    public function execute()
    {
        $response = $this->initResponse();

        try {
            $confirmPaymentSource = new ConfirmPaymentSource($this->paypalOrderId, $this->initBodyBuilder());
            $exec = $this->client->execute($confirmPaymentSource);

            if ($exec instanceof HttpAdoptedResponse) {
                $exec = $exec->getAdoptedResponse();
            }

            if ($exec->statusCode >= 200 && $exec->statusCode < 300) {
                $response->setSuccess(true)
                    ->setData($exec)
                    ->setPaymentId($exec->result->id)
                    ->setStatusCode($exec->statusCode)
                    ->setApproveLink($this->getLink('approve', $exec->result->links))
                    ->setPayerActionLink($this->getLink('payer-action', $exec->result->links));
            } else {
                $response->setSuccess(false)->setData($exec);
            }
        } catch (PaypalException $e) {
            $error = new Error();
            $resultDecoded = json_decode($e->getMessage(), true);

            if (false == empty($resultDecoded['name'])) {
                $error->setErrorCode($resultDecoded['name']);
            }

            if (false == empty($resultDecoded['message'])) {
                $error->setMessage($resultDecoded['message']);
            }

            $response->setSuccess(false)
                ->setError($error);
        } catch (\Throwable $e) {
            $error = new Error();
            $error->setErrorCode($e->getCode())
                ->setMessage($e->getMessage());
            $response->setSuccess(false)
                ->setError($error);
        }

        return $response;
    }

    protected function initResponse()
    {
        return new ResponseConfirmationPaymentSource();
    }

    protected function initBodyBuilder()
    {
        return new ConfirmPaymentSourceBuilder($this->apmMethod);
    }

    /**
     * @param string $nameLink
     * @param array $links
     *
     * @return string
     */
    protected function getLink($nameLink, $links)
    {
        foreach ($links as $link) {
            if ($link->rel == $nameLink) {
                return $link->href;
            }
        }

        return '';
    }
}
