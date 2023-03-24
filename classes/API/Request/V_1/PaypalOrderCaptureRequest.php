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

use Customer;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PaypalAddons\classes\API\Response\ResponseOrderCapture;

class PaypalOrderCaptureRequest extends RequestAbstractMB
{
    public function execute()
    {
        $response = new ResponseOrderCapture();

        // Get the payment Object by passing paymentId
        // payment id was previously stored in session in
        // CreatePaymentUsingPayPal.php
        $payment = Payment::get($this->method->getPaymentId(), $this->getApiContext());

        // ### Payment Execute
        // PaymentExecution object includes information necessary
        // to execute a PayPal account payment.
        // The payer_id is added to the request query parameters
        // when the user is redirected from paypal back to your site
        $execution = new PaymentExecution();
        $execution->setPayerId($this->method->getPayerId());

        // ### Optional Changes to Amount
        // If you wish to update the amount that you wish to charge the customer,
        // based on the shipping address or any other reason, you could
        // do that by passing the transaction object with just `amount` field in it.
        $exec = $payment->execute($execution, $this->getApiContext());

        return $response->setSuccess(true)
            ->setData($exec)
            ->setPaymentId($this->getPaymentId($exec))
            ->setTransactionId($this->getTransactionId($exec))
            ->setCurrency($this->getCurrency($exec))
            ->setCapture($this->getCapture($exec))
            ->setTotalPaid($this->getTotalPaid($exec))
            ->setStatus($this->getPaymentStatus($exec))
            ->setPaymentMethod($this->getPaymentMethod($exec))
            ->setPaymentTool($this->getPaymentTool($exec))
            ->setMethod($this->getMethodTransaction())
            ->setDateTransaction($this->getDateTransaction($exec));
    }

    /**
     * @param Payment $payment
     *
     * @return string
     */
    protected function getPaymentId(Payment $payment)
    {
        return (string) $payment->getId();
    }

    /**
     * @param Payment $payment
     *
     * @return string
     */
    protected function getTransactionId(Payment $payment)
    {
        $paymentInfo = $payment->transactions[0];

        return (string) $paymentInfo->related_resources[0]->sale->id;
    }

    /**
     * @param Payment $payment
     *
     * @return string
     */
    protected function getCurrency(Payment $payment)
    {
        $paymentInfo = $payment->transactions[0];

        return (string) $paymentInfo->amount->currency;
    }

    /**
     * @param Payment $payment
     *
     * @return bool
     */
    protected function getCapture(Payment $payment)
    {
        return false;
    }

    /**
     * @param Payment $payment
     *
     * @return float
     */
    protected function getTotalPaid(Payment $payment)
    {
        $paymentInfo = $payment->transactions[0];

        return (float) $paymentInfo->amount->total;
    }

    /**
     * @param Payment $payment
     *
     * @return string
     */
    protected function getPaymentMethod(Payment $payment)
    {
        return (string) $payment->payer->payment_method;
    }

    /**
     * @param Payment $payment
     *
     * @return string
     */
    protected function getPaymentStatus(Payment $payment)
    {
        return (string) $payment->state;
    }

    /**
     * @param Payment $payment
     *
     * @return string
     */
    protected function getPaymentTool(Payment $payment)
    {
        return (string) isset($payment->payment_instruction) ? $payment->payment_instruction->instruction_type : '';
    }

    /**
     * @return string
     */
    protected function getMethodTransaction()
    {
        return 'MB';
    }

    /**
     * @param Payment $payment
     *
     * @return \DateTime
     */
    protected function getDateTransaction($payment)
    {
        $date = \DateTime::createFromFormat(\DateTime::ISO8601, $payment->update_time);

        return $date;
    }
}
