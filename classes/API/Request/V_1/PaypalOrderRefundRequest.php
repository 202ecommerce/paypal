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
use PayPal;
use PayPal\Api\Amount;
use PayPal\Api\DetailedRefund;
use PayPal\Api\Payment;
use PayPal\Api\RefundRequest;
use PayPal\Api\Sale;
use PaypalAddons\classes\AbstractMethodPaypal;
use PaypalAddons\classes\API\Response\Error;
use PaypalAddons\classes\API\Response\ResponseOrderRefund;
use PaypalOrder;
use Throwable;

class PaypalOrderRefundRequest extends RequestAbstractMB
{
    /** @var PaypalOrder */
    protected $paypalOrder;

    public function __construct(AbstractMethodPaypal $method, PaypalOrder $paypalOrder)
    {
        parent::__construct($method);
        $this->paypalOrder = $paypalOrder;
    }

    /**
     * @return ResponseOrderRefund
     */
    public function execute()
    {
        $response = new ResponseOrderRefund();

        try {
            $sale = Sale::get($this->paypalOrder->id_transaction, $this->getApiContext($this->paypalOrder->sandbox));
            $amt = $this->getAmount($sale);
            $refundRequest = new RefundRequest();
            $refundRequest->setAmount($amt);
            $exec = $sale->refundSale($refundRequest, $this->getApiContext($this->paypalOrder->sandbox));

            $response->setSuccess(true)
                ->setIdTransaction($exec->id)
                ->setStatus($exec->state)
                ->setAmount($exec->total_refunded_amount->value)
                ->setDateTransaction($this->getDateTransaction($exec));

            return $response;
        } catch (Throwable $e) {
            $error = new Error();
            $error
                ->setMessage($e->getMessage())
                ->setErrorCode($e->getCode());

            return $response
                ->setSuccess(false)
                ->setError($error);
        } catch (Exception $e) {
            $error = new Error();
            $error
                ->setMessage($e->getMessage())
                ->setErrorCode($e->getCode());

            return $response
                ->setSuccess(false)
                ->setError($error);
        }
    }

    protected function getDateTransaction(DetailedRefund $detailedRefund)
    {
        $date = DateTime::createFromFormat(DateTime::ISO8601, $detailedRefund->update_time);

        return $date->format('Y-m-d TH:i:s');
    }

    /**
     * @param Sale $sale
     *
     * @return Amount
     */
    protected function getAmount(Sale $sale)
    {
        $payment = Payment::get($sale->getParentPayment(), $this->getApiContext($this->paypalOrder->sandbox));
        $transaction = $payment->getTransactions()[0];
        $amount = 0;

        foreach ($transaction->getRelatedResources() as $resources) {
            $resource = null;

            if (isset($resources->sale)) {
                $resource = $resources->sale;
            }

            if (isset($resources->refund)) {
                $resource = $resources->refund;
            }

            if ($resource === null) {
                continue;
            }

            if (is_callable([$resource, 'amount'], true)) {
                $transactionAmount = $resource->amount;
                if (is_callable([$transactionAmount, 'total'], true)) {
                    $amount += $transactionAmount->total;
                }
            }
        }

        $amt = new Amount();

        return $amt
            ->setCurrency($sale->getAmount()->getCurrency())
            ->setTotal(number_format($amount, Paypal::getDecimal($this->paypalOrder->currency), '.', ''));
    }
}
