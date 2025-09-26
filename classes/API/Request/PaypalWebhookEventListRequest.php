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
use PaypalAddons\classes\API\ExtensionSDK\Webhook\ListEventNotification;
use PaypalAddons\classes\API\HttpAdoptedResponse;
use PaypalAddons\classes\API\Model\WebhookEvent;
use PaypalAddons\classes\API\Response\Error;
use PaypalAddons\classes\API\Response\ResponseWebhookEventList;
use PaypalAddons\classes\PaypalException;

if (!defined('_PS_VERSION_')) {
    exit;
}

class PaypalWebhookEventListRequest extends RequestAbstract
{
    protected $params = [];

    public function __construct(HttpClient $client, AbstractMethodPaypal $method, $params = [])
    {
        parent::__construct($client, $method);

        if (is_array($params)) {
            $this->params = $params;
        }
    }

    public function execute()
    {
        $response = $this->getResponse();
        $request = new ListEventNotification($this->params);
        $list = [];

        try {
            $exec = $this->client->execute($request);

            if ($exec instanceof HttpAdoptedResponse) {
                $exec = $exec->getAdoptedResponse();
            }

            if (false === empty($exec->result->events)) {
                foreach ($exec->result->events as $event) {
                    $list[] = new WebhookEvent(json_encode($event));
                }
            }
            $response->setSuccess(true)->setData($exec)->setList($list);
        } catch (PaypalException $e) {
            $error = new Error();
            $resultDecoded = json_decode($e->getMessage(), true);

            if (empty($resultDecoded['details'][0]['description'])) {
                $error->setMessage($e->getMessage());
            } else {
                $error->setMessage($resultDecoded['details'][0]['description']);
            }

            $error->setErrorCode($e->getCode());
            $response->setSuccess(false)->setError($error);
        } catch (\Throwable $e) {
            $error = new Error();
            $error->setMessage($e->getMessage())
                ->setErrorCode($e->getCode());
            $response->setSuccess(false)
                ->setError($error);
        }

        return $response;
    }

    /** @return ResponseWebhookEventList*/
    protected function getResponse()
    {
        return new ResponseWebhookEventList();
    }
}
