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

use DateTime;
use Exception;
use PayPal\Api\WebhookEvent;
use PaypalAddons\classes\API\Response\Error as PaypalError;
use PaypalAddons\classes\API\Response\Response;
use Throwable;

class GetWebhookEvents extends RequestAbstract
{
    protected $params = [];

    public function execute()
    {
        $response = $this->getResponse();

        try {
            if (empty($this->getParams()['id'])) {
                $eventList = WebhookEvent::all($this->getParams(), $this->getApiContext())->getEvents();
            } else {
                $eventList = [
                    WebhookEvent::get($this->getParams()['id'], $this->getApiContext()),
                ];
            }

            $response
                ->setSuccess(true)
                ->setData($eventList);
        } catch (Throwable $e) {
            $error = new PaypalError();
            $error
                ->setMessage($e->getMessage())
                ->setErrorCode($e->getCode());

            $response
                ->setSuccess(false)
                ->setError($error);
        } catch (Exception $e) {
            $error = new PaypalError();
            $error
                ->setMessage($e->getMessage())
                ->setErrorCode($e->getCode());

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

    public function getParams()
    {
        return $this->params;
    }

    public function setParams($params)
    {
        if (false == is_array($params)) {
            return $this;
        }
        $this->params = $params;

        return $this;
    }

    public function addParams($params)
    {
        if (false == is_array($params)) {
            return $this;
        }

        $this->params = array_merge($this->params, $params);

        return $this;
    }

    public function initDefaultParams()
    {
        $this->setParams([
            'page_size' => 50,
            'start_time' => date(Datetime::ISO8601, strtotime('- 2 day')),
        ]);

        return $this;
    }
}
