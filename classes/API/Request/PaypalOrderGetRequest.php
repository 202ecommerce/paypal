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
use PaypalAddons\classes\API\ExtensionSDK\Order\OrdersGetRequest;
use PaypalAddons\classes\API\HttpAdoptedResponse;
use PaypalAddons\classes\API\Response\DepositBankDetails;
use PaypalAddons\classes\API\Response\Error;
use PaypalAddons\classes\API\Response\PurchaseUnit;
use PaypalAddons\classes\API\Response\ResponseOrderGet;
use PaypalAddons\classes\PaypalException;

if (!defined('_PS_VERSION_')) {
    exit;
}

class PaypalOrderGetRequest extends RequestAbstract
{
    protected $idPayment;

    public function __construct(HttpClient $client, AbstractMethodPaypal $method, $idPayment)
    {
        parent::__construct($client, $method);
        $this->idPayment = $idPayment;
    }

    public function execute()
    {
        $response = new ResponseOrderGet();

        try {
            $orderGetRequest = new OrdersGetRequest($this->idPayment);
            $exec = $this->client->execute($orderGetRequest);

            if ($exec instanceof HttpAdoptedResponse) {
                $exec = $exec->getAdoptedResponse();
            }

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

                $response->setTransactionId($this->getTransactionId($exec))
                    ->setPaymentMethod($this->getPaymentMethod($exec))
                    ->setPaymentTool($this->getPaymentTool())
                    ->setMethod($this->getMethodTransaction())
                    ->setDateTransaction($this->getDateTransaction($exec));
            } else {
                $response->setSuccess(false)->setData($exec);
            }
        } catch (PaypalException $e) {
            $error = new Error();
            $resultDecoded = json_decode($e->getMessage());
            $error->setMessage($resultDecoded->details[0]->description)->setErrorCode($e->getCode());

            $response->setSuccess(false)
                ->setError($error);
        } catch (\Throwable $e) {
            $error = new Error();
            $error->setErrorCode($e->getCode())->setMessage($e->getMessage());
            $response->setError($error)->setSuccess(false);
        }

        return $response;
    }

    protected function getAddress1($exec)
    {
        return empty($exec->result->purchase_units[0]->shipping->address->address_line_1) ? '' : $exec->result->purchase_units[0]->shipping->address->address_line_1;
    }

    protected function getAddress2($exec)
    {
        return empty($exec->result->purchase_units[0]->shipping->address->address_line_2) ? '' : $exec->result->purchase_units[0]->shipping->address->address_line_2;
    }

    protected function getCity($exec)
    {
        return empty($exec->result->purchase_units[0]->shipping->address->admin_area_2) ? '' : $exec->result->purchase_units[0]->shipping->address->admin_area_2;
    }

    protected function getPostCode($exec)
    {
        return empty($exec->result->purchase_units[0]->shipping->address->postal_code) ? '' : $exec->result->purchase_units[0]->shipping->address->postal_code;
    }

    protected function getCountryCode($exec)
    {
        return empty($exec->result->purchase_units[0]->shipping->address->country_code) ? '' : $exec->result->purchase_units[0]->shipping->address->country_code;
    }

    protected function getStateCode($exec)
    {
        return empty($exec->result->purchase_units[0]->shipping->address->admin_area_1) ? '' : $exec->result->purchase_units[0]->shipping->address->admin_area_1;
    }

    protected function getPhone($exec)
    {
        if (empty($exec->result->payer->phone->phone_number->national_number)) {
            return '';
        }

        return $exec->result->payer->phone->phone_number->national_number;
    }

    protected function getFullName($exec)
    {
        return empty($exec->result->purchase_units[0]->shipping->name->full_name) ? '' : $exec->result->purchase_units[0]->shipping->name->full_name;
    }

    protected function getEmail($exec)
    {
        if (false == empty($exec->result->payer->email_address)) {
            return $exec->result->payer->email_address;
        }

        return '';
    }

    protected function getFirstName($exec)
    {
        if (false == empty($exec->result->payer->name->given_name)) {
            return $exec->result->payer->name->given_name;
        }

        return '';
    }

    protected function getLastName($exec)
    {
        if (false == empty($exec->result->payer->name->surname)) {
            return $exec->result->payer->name->surname;
        }

        return '';
    }

    protected function getPurchaseUnit($exec)
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

    protected function getStatus($exec)
    {
        if (empty($exec->result->status)) {
            return '';
        }

        return $exec->result->status;
    }

    protected function getDepositBankDetails($exec)
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

    protected function getTransactionId($exec)
    {
        if (false === empty($exec->result->purchase_units[0]->payments->captures[0]->id)) {
            return $exec->result->purchase_units[0]->payments->captures[0]->id;
        }

        if (false === empty($exec->result->purchase_units[0]->payments->authorizations[0]->id)) {
            return $exec->result->purchase_units[0]->payments->authorizations[0]->id;
        }

        return '';
    }

    protected function getPaymentTool()
    {
        return '';
    }

    protected function getPaymentMethod($exec)
    {
        if (false === empty($exec->result->payment_source->giropay)) {
            return 'giropay';
        }

        return 'paypal';
    }

    protected function getDateTransaction($exec)
    {
        if (empty($exec->result->purchase_units[0]->payments->captures[0])) {
            return new \DateTime();
        }

        $date = \DateTime::createFromFormat(\DateTime::ATOM, $exec->result->purchase_units[0]->payments->captures[0]->create_time);

        if (!$date) {
            $date = new \DateTime();
        }

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
