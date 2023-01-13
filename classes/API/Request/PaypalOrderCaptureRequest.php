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
use PaypalAddons\classes\API\Response\ResponseOrderCapture;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;
use PayPalHttp\HttpException;
use Throwable;

class PaypalOrderCaptureRequest extends RequestAbstract
{
    /** @var string */
    protected $paymentId;

    public function __construct($client, AbstractMethodPaypal $method, $paymentId)
    {
        parent::__construct($client, $method);
        $this->paymentId = $paymentId;
    }

    public function execute()
    {
        $response = new ResponseOrderCapture();
        $orderCapture = new OrdersCaptureRequest($this->paymentId);
        $orderCapture->headers = array_merge($this->getHeaders(), $orderCapture->headers);

        try {
            $exec = $this->client->execute($orderCapture);

            if (in_array($exec->statusCode, [200, 201, 202])) {
                $response->setSuccess(true)
                    ->setData($exec)
                    ->setPaymentId($exec->result->id)
                    ->setTransactionId($this->getTransactionId($exec))
                    ->setCurrency($this->getCurrency($exec))
                    ->setCapture($this->getCapture($exec))
                    ->setTotalPaid($this->getTotalPaid($exec))
                    ->setStatus($exec->result->status)
                    ->setPaymentMethod($this->getPaymentMethod())
                    ->setPaymentTool($this->getPaymentTool())
                    ->setMethod($this->getMethodTransaction())
                    ->setDateTransaction($this->getDateTransaction($exec));
            } elseif ($exec->statusCode == 204) {
                $response->setSuccess(true);
            } else {
                $error = new Error();
                $resultDecoded = json_decode($exec->message);
                $error->setMessage($resultDecoded->message);
                $response->setSuccess(false)->setError($error);
            }
        } catch (HttpException $e) {
            $error = new Error();
            $resultDecoded = json_decode($e->getMessage());
            $error->setMessage($resultDecoded->details[0]->description)->setErrorCode($e->getCode());
            $response->setSuccess(false)
                ->setError($error);
        } catch (Throwable $e) {
            $error = new Error();
            $error->setErrorCode($e->getCode())->setMessage($e->getMessage());
            $response->setError($error)->setSuccess(false);
        } catch (Exception $e) {
            $error = new Error();
            $error->setErrorCode($e->getCode())->setMessage($e->getMessage());
            $response->setError($error)->setSuccess(false);
        }

        return $response;
    }

    protected function getTransactionId($exec)
    {
        return $exec->result->purchase_units[0]->payments->captures[0]->id;
    }

    protected function getCurrency($exec)
    {
        return $exec->result->purchase_units[0]->payments->captures[0]->amount->currency_code;
    }

    protected function getCapture($exec)
    {
        return $exec->result->purchase_units[0]->payments->captures[0]->final_capture == false;
    }

    protected function getTotalPaid($exec)
    {
        return $exec->result->purchase_units[0]->payments->captures[0]->amount->value;
    }

    protected function getPaymentTool()
    {
        return '';
    }

    protected function getPaymentMethod()
    {
        return 'paypal';
    }

    protected function getDateTransaction($exec)
    {
        $payemnts = $exec->result->purchase_units[0]->payments;
        $transaction = $payemnts->captures[0];
        $date = \DateTime::createFromFormat(\DateTime::ATOM, $transaction->create_time);

        return $date;
    }

    protected function getMethodTransaction()
    {
        switch (get_class($this->method)) {
            case 'MethodEC':
                $method = 'EC';
                break;
            case 'MethodMB':
                $method = 'MB';
                break;
            case 'MethodPPP':
                $method = 'PPP';
                break;
            default:
                $method = '';
        }

        return $method;
    }
}
