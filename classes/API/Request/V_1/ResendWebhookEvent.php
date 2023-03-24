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
use PayPal\Api\WebhookEvent;
use PaypalAddons\classes\AbstractMethodPaypal;
use PaypalAddons\classes\API\Response\Error;
use PaypalAddons\classes\API\Response\Response;
use Throwable;

class ResendWebhookEvent extends RequestAbstract
{
    /** @var WebhookEvent */
    protected $webhookEvent;

    public function __construct(AbstractMethodPaypal $method, WebhookEvent $webhookEvent)
    {
        parent::__construct($method);

        $this->webhookEvent = $webhookEvent;
    }

    public function execute()
    {
        $response = $this->getResponse();

        try {
            $this->webhookEvent->resend($this->getApiContext());
            $response->setSuccess(true);
        } catch (Throwable $e) {
            $error = (new Error())
                ->setMessage($e->getMessage())
                ->setErrorCode($e->getMessage());
            $response
                ->setSuccess(false)
                ->setError($error);
        } catch (Exception $e) {
            $error = (new Error())
                ->setMessage($e->getMessage())
                ->setErrorCode($e->getMessage());
            $response
                ->setSuccess(false)
                ->setError($error);
        }

        return $response;
    }

    protected function getResponse()
    {
        return new Response();
    }
}
