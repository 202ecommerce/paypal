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

use PayPal\Api\Webhook;
use PaypalAddons\classes\AbstractMethodPaypal;
use PaypalAddons\classes\API\Request\V_1\CreateWebHookRequest;
use PaypalAddons\classes\API\Request\V_1\GetWebHooks;
use PaypalAddons\classes\API\Request\V_1\UpdateWebHookEventType;
use PaypalAddons\classes\API\Response\Response;

class CreateWebhook
{
    /** @var AbstractMethodPaypal */
    protected $method;

    /** @var bool if webhook exists it will be updated ou not */
    protected $update = true;

    public function __construct($method = null)
    {
        if (is_null($method)) {
            $method = AbstractMethodPaypal::load();
        }

        $this->setMethod($method);
    }

    /**
     * Check if webhook exists and create new if not
     *
     * @return Response
     */
    public function execute()
    {
        $method = $this->getMethod();
        $response = (new GetWebHooks($method))->execute();

        if ($response->isSuccess() == false) {
            $executeResponse = (new CreateWebHookRequest($method))->execute();

            if ($executeResponse->isSuccess()) {
                @$this->updateWebhookId($executeResponse->getData());
            }

            return $executeResponse;
        }

        if (empty($response->getData())) {
            return (new CreateWebHookRequest($method))->execute();
        }

        $webhookHandler = (new WebhookHandlerUrl())->get();

        /** @var Webhook $webhook */
        foreach ($response->getData() as $webhook) {
            if ($webhook->getUrl() == $webhookHandler) {
                $this->updateWebhookId($webhook);

                if ($this->getUpdate()) {
                    return (new UpdateWebHookEventType($method, $webhook))->execute();
                }

                return (new Response())
                    ->setSuccess(true)
                    ->setData($webhook);
            }
        }

        $executeResponse = (new CreateWebHookRequest($method))->execute();

        if ($executeResponse->isSuccess()) {
            @$this->updateWebhookId($executeResponse->getData());
        }

        return $executeResponse;
    }

    /**
     * @return AbstractMethodPaypal
     */
    public function getMethod()
    {
        if ($this->method instanceof AbstractMethodPaypal) {
            return $this->method;
        }

        return AbstractMethodPaypal::load();
    }

    /**
     * @return self
     */
    public function setMethod($method)
    {
        if ($method instanceof AbstractMethodPaypal) {
            $this->method = $method;
        }

        return $this;
    }

    /**
     * @return self
     */
    public function setUpdate($update)
    {
        $this->update = (bool) $update;

        return $this;
    }

    /**
     * @return bool
     */
    public function getUpdate()
    {
        return $this->update;
    }

    /**
     * @param Webhook $webhook
     *
     * @return void
     */
    protected function updateWebhookId(Webhook $webhook)
    {
        (new WebhookId($this->method))->update($webhook->id);
    }
}
