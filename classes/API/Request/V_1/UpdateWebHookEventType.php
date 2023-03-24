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

namespace PaypalAddons\classes\API\Request\V_1;

use Exception;
use PayPal\Api\Patch;
use PayPal\Api\PatchRequest;
use PayPal\Api\Webhook;
use PaypalAddons\classes\AbstractMethodPaypal;
use PaypalAddons\classes\API\Response\Error  as PaypalError;
use PaypalAddons\classes\API\Response\Response;
use PaypalAddons\classes\Constants\WebHookType;
use Throwable;

class UpdateWebHookEventType extends RequestAbstract
{
    /** @var Webhook */
    protected $webhook;

    /**
     * @param AbstractMethodPaypal
     * @param Webhook
     */
    public function __construct(AbstractMethodPaypal $method, Webhook $webhook)
    {
        parent::__construct($method);

        $this->setWebhook($webhook);
    }

    public function execute()
    {
        $response = new Response();

        try {
            $result = $this->webhook->update($this->getPatchRequest(), $this->getApiContext());
            $response
                ->setSuccess(true)
                ->setData($result);
        } catch (Throwable $e) {
            $error = new PaypalError();
            $error
                ->setErrorCode($e->getCode())
                ->setMessage($e->getMessage());
            $response
                ->setSuccess(false)
                ->setError($error);
        } catch (Exception $e) {
            $error = new PaypalError();
            $error
                ->setErrorCode($e->getCode())
                ->setMessage($e->getMessage());
            $response
                ->setSuccess(false)
                ->setError($error);
        }

        return $response;
    }

    public function setWebhook(Webhook $webhook)
    {
        $this->webhook = $webhook;

        return $this;
    }

    protected function getPatchRequest()
    {
        $patchRequest = new PatchRequest();
        $patch = new Patch();
        $eventTypes = [];

        foreach (WebHookType::getAll() as $type) {
            $eventType = new \stdClass();
            $eventType->name = $type;
            $eventTypes[] = $eventType;
        }

        $patch
            ->setOp('replace')
            ->setPath('/event_types')
            ->setValue($eventTypes);

        $patchRequest->addPatch($patch);

        return $patchRequest;
    }
}
