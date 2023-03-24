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

use Configuration;
use MethodEC;
use MethodMB;
use MethodPPP;
use PaypalAddons\classes\AbstractMethodPaypal;
use PaypalAddons\classes\Constants\PaypalConfigurations;
use PaypalAddons\classes\Constants\WebHookType;

class StatusMapping
{
    /**
     * @return string $transactionStatus
     * @return int
     */
    public function getPsOrderStatusByTransaction($transactionStatus)
    {
        $orderStatus = 0;

        switch ($transactionStatus) {
            case 'Completed':
                $orderStatus = $this->getAcceptedStatus();
                break;
            case 'Refunded':
                $orderStatus = $this->getRefundStatus();
                break;
            case 'Failed':
                $orderStatus = $this->getFailedStatus();
                break;
            case 'Reversed':
                $orderStatus = $this->getFailedStatus();
                break;
            case 'Denied':
                $orderStatus = $this->getFailedStatus();
                break;
        }

        return $orderStatus;
    }

    public function getAcceptedStatus()
    {
        $idStatus = null;

        if ($this->isCustomize()) {
            if ($this->isModeSale()) {
                $idStatus = (int) Configuration::get(PaypalConfigurations::OS_ACCEPTED_TWO);
            } else {
                $idStatus = (int) Configuration::get(PaypalConfigurations::OS_ACCEPTED);
            }
        }

        if ($idStatus) {
            return $idStatus;
        }

        return (int) Configuration::get('PS_OS_PAYMENT');
    }

    public function getRefundStatus($method = null)
    {
        $idStatus = null;

        if (is_null($method)) {
            $method = AbstractMethodPaypal::load();
        }

        if ($this->isCustomize()) {
            if ($method instanceof MethodMB) {
                $idStatus = (int) Configuration::get('PAYPAL_OS_REFUNDED_PAYPAL');
            } else {
                $idStatus = (int) Configuration::get('PAYPAL_OS_REFUNDED');
            }
        }

        if ($idStatus) {
            return $idStatus;
        }

        return (int) Configuration::get('PS_OS_REFUND');
    }

    public function getFailedStatus()
    {
        $idStatus = null;

        if ($this->isCustomize()) {
            $idStatus = (int) Configuration::get('PAYPAL_OS_VALIDATION_ERROR');
        }

        if ($idStatus) {
            return $idStatus;
        }

        return (int) Configuration::get('PS_OS_CANCELED');
    }

    public function getCanceledStatus($method = null)
    {
        $idStatus = null;

        if (is_null($method)) {
            $method = AbstractMethodPaypal::load();
        }

        if ($this->isCustomize()) {
            if ($method instanceof MethodEC) {
                if ($this->isModeSale()) {
                    $idStatus = (int) Configuration::get('PAYPAL_OS_CANCELED');
                } else {
                    $idStatus = (int) Configuration::get('PAYPAL_OS_CAPTURE_CANCELED');
                }
            }
        }

        if ($idStatus) {
            return $idStatus;
        }

        return (int) Configuration::get('PS_OS_CANCELED');
    }

    public function getWaitValidationStatus()
    {
        $idStatus = null;

        if ($this->isCustomize()) {
            $idStatus = (int) Configuration::get('PAYPAL_OS_WAITING_VALIDATION');
        }

        if ($idStatus) {
            return $idStatus;
        }

        return (int) Configuration::get('PAYPAL_OS_WAITING');
    }

    public function isCustomize()
    {
        return (bool) Configuration::get('PAYPAL_CUSTOMIZE_ORDER_STATUS');
    }

    public function isModeSale($method = null)
    {
        if (is_null($method)) {
            $method = AbstractMethodPaypal::load();
        }

        if ($method instanceof MethodPPP) {
            return true;
        }

        if ($method instanceof MethodMB) {
            return true;
        }

        return Configuration::get('PAYPAL_API_INTENT') == 'sale';
    }

    /**
     * @param string $eventType
     *
     * @return int
     */
    public function getPsOrderStatusByEventType($eventType)
    {
        $orderStatus = 0;

        switch ($eventType) {
            case WebHookType::CAPTURE_COMPLETED:
                $orderStatus = $this->getAcceptedStatus();
                break;
            case WebHookType::CAPTURE_REFUNDED:
                $orderStatus = $this->getRefundStatus();
                break;
            case WebHookType::CAPTURE_REVERSED:
                $orderStatus = $this->getRefundStatus();
                break;
            case WebHookType::CAPTURE_DENIED:
                $orderStatus = $this->getCanceledStatus();
                break;
            case WebHookType::AUTHORIZATION_VOIDED:
                $orderStatus = $this->getCanceledStatus();
                break;
        }

        return $orderStatus;
    }

    public function getPsOutOfStock()
    {
        return (int) Configuration::get('PS_OS_OUTOFSTOCK_UNPAID');
    }
}
