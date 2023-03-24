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

use PaypalAddons\services\ServicePaypalIpn;
use PaypalAddons\services\ServicePaypalOrder;
use PaypalPPBTlib\Extensions\ProcessLogger\ProcessLoggerHandler;

class PaypalIpnModuleFrontController extends PaypalAbstarctModuleFrontController
{
    /** @var ServicePaypalIpn */
    protected $servicePaypalIpn;

    /** @var ServicePaypalOrder */
    protected $servicePaypalOrder;

    public function __construct()
    {
        parent::__construct();
        $this->servicePaypalIpn = new ServicePaypalIpn();
        $this->servicePaypalOrder = new ServicePaypalOrder();
    }

    public function run()
    {
        try {
            if ($this->requestIsValid()) {
                if ($this->handleIpn(Tools::getAllValues())) {
                    header('HTTP/1.1 200 OK');
                } else {
                    header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
                }
            }
        } catch (\Exception $e) {
            $message = 'Error code: ' . $e->getCode() . '.';
            $message .= 'Short message: ' . $e->getMessage() . '.';

            ProcessLoggerHandler::openLogger();
            ProcessLoggerHandler::logError(
                $message,
                \Tools::getValue('txn_id') ? \Tools::getValue('txn_id') : null,
                null,
                null,
                null,
                null,
                (int) \Configuration::get('PAYPAL_SANDBOX'),
                null
            );
            ProcessLoggerHandler::closeLogger();

            header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
        }
    }

    /**
     * @param $data array Ipn message data
     *
     * @return bool
     */
    protected function handleIpn($data)
    {
        if ($this->alreadyHandled($data)) {
            return true;
        }

        $logResponse = [
            'payment_status' => isset($data['payment_status']) ? $data['payment_status'] : null,
            'ipn_track_id' => isset($data['ipn_track_id']) ? $data['ipn_track_id'] : null,
        ];

        if ($data['payment_status'] == 'Refunded' && isset($data['parent_txn_id'])) {
            $transactionRef = $data['parent_txn_id'];
        } else {
            $transactionRef = $data['txn_id'];
        }

        $paypalOrder = $this->servicePaypalOrder->getPaypalOrderByTransaction($transactionRef);

        if (Validate::isLoadedObject($paypalOrder) == false) {
            return false;
        }

        $orders = $this->servicePaypalOrder->getPsOrders($paypalOrder);

        ProcessLoggerHandler::openLogger();
        foreach ($orders as $order) {
            ProcessLoggerHandler::logInfo(
                'IPN response : ' . $this->jsonEncode($logResponse),
                $data['txn_id'],
                $order->id,
                $order->id_cart,
                null,
                'PayPal',
                (int) Configuration::get('PAYPAL_SANDBOX')
            );
        }
        ProcessLoggerHandler::closeLogger();

        $paypalIpn = new PaypalIpn();
        $paypalIpn->id_transaction = $data['txn_id'];
        $paypalIpn->status = $data['payment_status'];
        $paypalIpn->response = $this->jsonEncode($logResponse);
        $paypalIpn->save();

        $psOrderStatus = $this->getPsOrderStatus($data['payment_status']);

        if ($psOrderStatus > 0) {
            $this->servicePaypalOrder->setOrderStatus($paypalOrder, $psOrderStatus);
        }

        return true;
    }

    protected function getPsOrderStatus($transactionStatus)
    {
        $orderStatus = 0;
        if ((int) Configuration::get('PAYPAL_CUSTOMIZE_ORDER_STATUS')) {
            switch ($transactionStatus) {
                case 'Completed':
                    $orderStatus = (int) Configuration::get('PAYPAL_OS_ACCEPTED_TWO');
                    break;
                case 'Refunded':
                    $orderStatus = (int) Configuration::get('PAYPAL_OS_REFUNDED_PAYPAL');
                    break;
                case 'Failed':
                    $orderStatus = (int) Configuration::get('PAYPAL_OS_VALIDATION_ERROR');
                    break;
                case 'Reversed':
                    $orderStatus = (int) Configuration::get('PAYPAL_OS_VALIDATION_ERROR');
                    break;
                case 'Denied':
                    $orderStatus = (int) Configuration::get('PAYPAL_OS_VALIDATION_ERROR');
                    break;
            }
        } else {
            switch ($transactionStatus) {
                case 'Completed':
                    $orderStatus = (int) Configuration::get('PS_OS_PAYMENT');
                    break;
                case 'Refunded':
                    $orderStatus = (int) Configuration::get('PS_OS_REFUND');
                    break;
                case 'Failed':
                    $orderStatus = (int) Configuration::get('PS_OS_CANCELED');
                    break;
                case 'Reversed':
                    $orderStatus = (int) Configuration::get('PS_OS_CANCELED');
                    break;
                case 'Denied':
                    $orderStatus = (int) Configuration::get('PS_OS_CANCELED');
                    break;
            }
        }

        return $orderStatus;
    }

    /**
     * @return bool
     */
    protected function requestIsValid()
    {
        $curl = curl_init($this->module->getIpnPaypalListener() . '?cmd=_notify-validate&' . http_build_query($_POST));
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_TIMEOUT, 90);
        $response = curl_exec($curl);

        return trim($response) == 'VERIFIED';
    }

    protected function alreadyHandled($data)
    {
        return $this->servicePaypalIpn->exists($data['txn_id'], $data['payment_status']);
    }

    /**
     * @param $orders array
     * @param $idState int
     *
     * @return bool
     */
    protected function setOrderStatus($orders, $idState)
    {
        /** @var $order \Order */
        foreach ($orders as $order) {
            $order->setCurrentState((int) $idState);
        }

        return true;
    }

    /**
     * @param $value mixed
     *
     * @return string
     */
    public function jsonEncode($value)
    {
        $result = json_encode($value);

        if (json_last_error() == JSON_ERROR_UTF8) {
            $result = json_encode($this->utf8ize($value));
        }

        return $result;
    }

    public function utf8ize($mixed)
    {
        if (is_array($mixed)) {
            foreach ($mixed as $key => $value) {
                $mixed[$key] = $this->utf8ize($value);
            }
        } elseif (is_string($mixed)) {
            return utf8_encode($mixed);
        }

        return $mixed;
    }
}
