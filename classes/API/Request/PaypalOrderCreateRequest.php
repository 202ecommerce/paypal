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

namespace PaypalAddons\classes\API\Request;

use Exception;
use PaypalAddons\classes\AbstractMethodPaypal;
use PaypalAddons\classes\API\Response\Error;
use PaypalAddons\classes\API\Response\ResponseOrderCreate;
use PaypalAddons\services\Builder\BuilderInterface;
use PaypalAddons\services\Builder\OrderCreateBody;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalHttp\HttpException;
use Throwable;

class PaypalOrderCreateRequest extends RequestAbstract
{
    /** @var BuilderInterface */
    protected $bodyBuilder;

    public function __construct(PayPalHttpClient $client, AbstractMethodPaypal $method)
    {
        parent::__construct($client, $method);

        $this->bodyBuilder = $this->initBodyBuilder();
    }

    public function execute()
    {
        $response = new ResponseOrderCreate();
        $order = new OrdersCreateRequest();
        $order->body = $this->buildRequestBody();
        $order->headers = array_merge($this->getHeaders(), $order->headers);

        try {
            $exec = $this->client->execute($order);

            if (in_array($exec->statusCode, [200, 201, 202])) {
                $response->setSuccess(true)
                    ->setData($exec)
                    ->setPaymentId($exec->result->id)
                    ->setStatusCode($exec->statusCode)
                    ->setApproveLink($this->getLink('approve', $exec->result->links));
            } elseif ($exec->statusCode == 204) {
                $response->setSuccess(true);
            } else {
                $error = new Error();
                $resultDecoded = json_decode($exec->message);
                $error->setMessage($resultDecoded->message);
                $response->setSuccess(false)
                    ->setError($error);
            }
        } catch (HttpException $e) {
            $error = new Error();
            $resultDecoded = json_decode($e->getMessage());
            $error->setMessage($resultDecoded->details[0]->description)->setErrorCode($e->getCode());
            $response->setSuccess(false)
                ->setError($error);
        } catch (Throwable $e) {
            $error = new Error();
            $error->setMessage($e->getMessage())
                ->setErrorCode($e->getCode());
            $response->setSuccess(false)
                ->setError($error);
        } catch (Exception $e) {
            $error = new Error();
            $error->setMessage($e->getMessage())
                ->setErrorCode($e->getCode());
            $response->setSuccess(false)
                ->setError($error);
        }

        return $response;
    }

    /**
     * @param $nameLink string
     * @param $links array
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

    /**
     * @return array
     */
    protected function buildRequestBody()
    {
        return $this->bodyBuilder->build();
    }

    protected function initBodyBuilder()
    {
        return new OrderCreateBody($this->context, $this->method);
    }
}
