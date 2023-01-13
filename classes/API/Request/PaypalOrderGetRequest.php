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
use PaypalAddons\classes\API\Response\DepositBankDetails;
use PaypalAddons\classes\API\Response\Error;
use PaypalAddons\classes\API\Response\PurchaseUnit;
use PaypalAddons\classes\API\Response\ResponseOrderGet;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Orders\OrdersGetRequest;
use PayPalHttp\HttpException;
use Throwable;

class PaypalOrderGetRequest extends RequestAbstract
{
    protected $idPayment;

    public function __construct(PayPalHttpClient $client, AbstractMethodPaypal $method, $idPayment)
    {
        parent::__construct($client, $method);
        $this->idPayment = $idPayment;
    }

    public function execute()
    {
        $response = new ResponseOrderGet();

        try {
            $orderGetRequest = new OrdersGetRequest($this->idPayment);
            $orderGetRequest->headers = array_merge($this->getHeaders(), $orderGetRequest->headers);
            $exec = $this->client->execute($orderGetRequest);

            if (in_array($exec->statusCode, [200, 201, 202])) {
                $response->setSuccess(true)
                    ->setData($exec);
                $response->getAddress()
                    ->setAddress1($this->getAddress1($exec))
                    ->setAddress2($this->getAddress2($exec))
                    ->setCity($this->getCity($exec))
                    ->setPostCode($this->getPostCode($exec))
                    ->setCountryCode($this->getCountryCode($exec))
                    ->setStateCode($this->getStateCode($exec))
                    ->setPhone($this->getPhone($exec))
                    ->setFullName($this->getFullName($exec));
                $response->getClient()
                    ->setEmail($this->getEmail($exec))
                    ->setFirstName($this->getFirstName($exec))
                    ->setLastName($this->getLastName($exec));
                $response->setPurchaseUnit($this->getPurchaseUnit($exec));
                $response->setStatus($this->getStatus($exec));
                $response->setDepositBankDetails($this->getDepositBankDetails($exec));
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

    protected function getAddress1(\PayPalHttp\HttpResponse $exec)
    {
        return $exec->result->purchase_units[0]->shipping->address->address_line_1;
    }

    protected function getAddress2(\PayPalHttp\HttpResponse $exec)
    {
        $address = $exec->result->purchase_units[0]->shipping->address;
        if (isset($address->address_line_2)) {
            return $address->address_line_2;
        } else {
            return '';
        }
    }

    protected function getCity(\PayPalHttp\HttpResponse $exec)
    {
        return $exec->result->purchase_units[0]->shipping->address->admin_area_2;
    }

    protected function getPostCode(\PayPalHttp\HttpResponse $exec)
    {
        return $exec->result->purchase_units[0]->shipping->address->postal_code;
    }

    protected function getCountryCode(\PayPalHttp\HttpResponse $exec)
    {
        return $exec->result->purchase_units[0]->shipping->address->country_code;
    }

    protected function getStateCode(\PayPalHttp\HttpResponse $exec)
    {
        $address = $exec->result->purchase_units[0]->shipping->address;

        if (isset($address->admin_area_1)) {
            return $address->admin_area_1;
        } else {
            return '';
        }
    }

    protected function getPhone(\PayPalHttp\HttpResponse $exec)
    {
        if (empty($exec->result->payer->phone->phone_number->national_number)) {
            return '';
        }

        return $exec->result->payer->phone->phone_number->national_number;
    }

    protected function getFullName(\PayPalHttp\HttpResponse $exec)
    {
        return $exec->result->purchase_units[0]->shipping->name->full_name;
    }

    protected function getEmail(\PayPalHttp\HttpResponse $exec)
    {
        if (false == empty($exec->result->payer->email_address)) {
            return $exec->result->payer->email_address;
        }

        return '';
    }

    protected function getFirstName(\PayPalHttp\HttpResponse $exec)
    {
        if (false == empty($exec->result->payer->name->given_name)) {
            return $exec->result->payer->name->given_name;
        }

        return '';
    }

    protected function getLastName(\PayPalHttp\HttpResponse $exec)
    {
        if (false == empty($exec->result->payer->name->surname)) {
            return $exec->result->payer->name->surname;
        }

        return '';
    }

    protected function getPurchaseUnit(\PayPalHttp\HttpResponse $exec)
    {
        if (empty($exec->result->purchase_units)) {
            return new PurchaseUnit();
        }

        $purchaseUnit = new PurchaseUnit();

        if (false == empty($exec->result->purchase_units[0]->amount->value)) {
            $purchaseUnit->setAmount($exec->result->purchase_units[0]->amount->value);
        }

        if (false == empty($exec->result->purchase_units[0]->amount->currency_code)) {
            $purchaseUnit->setCurrency($exec->result->purchase_units[0]->amount->currency_code);
        }

        return $purchaseUnit;
    }

    protected function getStatus(\PayPalHttp\HttpResponse $exec)
    {
        if (empty($exec->result->status)) {
            return '';
        }

        return $exec->result->status;
    }

    protected function getDepositBankDetails(\PayPalHttp\HttpResponse $exec)
    {
        if (empty($exec->result->payment_source->pay_upon_invoice->deposit_bank_details)) {
            return new DepositBankDetails();
        }

        $bankDetails = new DepositBankDetails();

        if (false == empty($exec->result->payment_source->pay_upon_invoice->deposit_bank_details->bic)) {
            $bankDetails->setBic($exec->result->payment_source->pay_upon_invoice->deposit_bank_details->bic);
        }

        if (false == empty($exec->result->payment_source->pay_upon_invoice->deposit_bank_details->bank_name)) {
            $bankDetails->setBankName($exec->result->payment_source->pay_upon_invoice->deposit_bank_details->bank_name);
        }

        if (false == empty($exec->result->payment_source->pay_upon_invoice->deposit_bank_details->iban)) {
            $bankDetails->setIban($exec->result->payment_source->pay_upon_invoice->deposit_bank_details->iban);
        }

        if (false == empty($exec->result->payment_source->pay_upon_invoice->deposit_bank_details->account_holder_name)) {
            $bankDetails->setAccountHolderName($exec->result->payment_source->pay_upon_invoice->deposit_bank_details->account_holder_name);
        }

        return $bankDetails;
    }
}
