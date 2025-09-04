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
use PaypalAddons\classes\API\ExtensionSDK\Webhook\CreateWebhook;
use PaypalAddons\classes\API\HttpAdoptedResponse;
use PaypalAddons\classes\API\Model\Webhook;
use PaypalAddons\classes\API\Model\WebhookEventType;
use PaypalAddons\classes\API\Response\Error;
use PaypalAddons\classes\API\Response\Response;
use PaypalAddons\classes\Constants\WebHookType;
use PaypalAddons\classes\PaypalException;
use PaypalAddons\classes\Webhook\WebhookHandlerUrl;

if (!defined('_PS_VERSION_')) {
    exit;
}

class PaypalCreateWebhookRequest extends RequestAbstract
{
    protected $webhook;

    public function __construct(HttpClient $client, AbstractMethodPaypal $method, $webhook = null)
    {
        parent::__construct($client, $method);

        if ($webhook instanceof Webhook) {
            $this->webhook = $webhook;
        } else {
            $this->webhook = $this->initDefaultWebhook();
        }
    }

    public function execute()
    {
        $response = $this->getResponse();
        $request = new CreateWebhook($this->webhook);

        try {
            $exec = $this->client->execute($request);

            if ($exec instanceof HttpAdoptedResponse) {
                $exec = $exec->getAdoptedResponse();
            }

            $response->setSuccess(true)->setData(new Webhook(json_encode($exec->result)));
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

    /** @return Response*/
    protected function getResponse()
    {
        return new Response();
    }

    protected function initDefaultWebhook()
    {
        $webhook = new Webhook();
        $webhook->setUrl((new WebhookHandlerUrl())->get());
        $eventTypes = [];

        foreach (WebHookType::getAll() as $type) {
            $eventTypes[] = (new WebhookEventType())->setName($type);
        }

        return $webhook->setEventTypes($eventTypes);
    }
}
