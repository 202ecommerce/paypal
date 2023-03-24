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

namespace PaypalAddons\services;

class PaymentData
{
    /** @var string */
    protected $orderId;

    /** @var string */
    protected $payerID;

    /** @var string */
    protected $paymentID;

    /** @var string */
    protected $billingToken;

    /** @var string */
    protected $facilitatorAccessToken;

    public function fromArray($data)
    {
        if (false == empty($data['orderID'])) {
            $this->setOrderId($data['orderID']);
        }

        if (false == empty($data['payerID'])) {
            $this->setPayerId($data['payerID']);
        }

        if (false == empty($data['paymentID'])) {
            $this->setPaymentID($data['paymentID']);
        }

        if (false == empty($data['billingToken'])) {
            $this->setBillingToken($data['billingToken']);
        }

        if (false == empty($data['facilitatorAccessToken'])) {
            $this->setFacilitatorAccessToken($data['facilitatorAccessToken']);
        }

        return $this;
    }

    public function setOrderId($orderID)
    {
        if (is_string($orderID)) {
            $this->orderId = $orderID;
        }

        return $this;
    }

    protected function setPayerId($payerID)
    {
        if (is_string($payerID)) {
            $this->payerID = $payerID;
        }

        return $this;
    }

    protected function setPaymentID($paymentID)
    {
        if (is_string($paymentID)) {
            $this->paymentID = $paymentID;
        }

        return $this;
    }

    protected function setBillingToken($billingToken)
    {
        if (is_string($billingToken)) {
            $this->billingToken = $billingToken;
        }

        return $this;
    }

    protected function setFacilitatorAccessToken($facilitatorAccessToken)
    {
        if (is_string($facilitatorAccessToken)) {
            $this->facilitatorAccessToken = $facilitatorAccessToken;
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getOrderId()
    {
        return $this->orderId;
    }

    /**
     * @return string
     */
    public function getPayerID()
    {
        return $this->payerID;
    }

    /**
     * @return string
     */
    public function getPaymentID()
    {
        return $this->paymentID;
    }

    /**
     * @return string
     */
    public function getBillingToken()
    {
        return $this->billingToken;
    }

    /**
     * @return string
     */
    public function getFacilitatorAccessToken()
    {
        return $this->facilitatorAccessToken;
    }
}
