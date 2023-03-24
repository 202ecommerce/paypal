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

namespace PaypalAddons\services\Builder;

use Carrier;
use Hook;
use Order;
use PaypalAddons\services\ServicePaypalOrder;
use PaypalAddons\services\TrackingParameters;
use Validate;

class TrackingInfoBuilder implements BuilderInterface
{
    protected $paypalOrder;

    protected $paypalOrderService;

    protected $trackingParametersService;

    public function __construct(\PaypalOrder $paypalOrder)
    {
        $this->paypalOrder = $paypalOrder;
        $this->paypalOrderService = new ServicePaypalOrder();
        $this->trackingParametersService = new TrackingParameters();
    }

    public function build()
    {
        $output = [];

        if (empty($this->paypalOrder->id_transaction)) {
            return $output;
        }

        $transactionId = $this->paypalOrder->id_transaction;
        $carrier = $this->getCarrier();
        $trackingNumber = $this->getTrackingNumber();

        $output['transaction_id'] = $transactionId;
        $output['tracking_number'] = $trackingNumber;
        $output['status'] = $this->trackingParametersService->getStatus();

        if ($paypalCarrier = $this->trackingParametersService->getPaypalCarrierByPsCarrier($carrier->id_reference)) {
            $output['carrier'] = $paypalCarrier;
        } else {
            $output['carrier'] = \PaypalAddons\classes\Constants\TrackingParameters::CARRIER_OTHER;
        }

        Hook::exec('actionAfterPaypalTrackingInfoBuild', ['id_order' => $this->paypalOrder->id_order, 'info' => &$output]);

        return $output;
    }

    protected function getTrackingNumber()
    {
        $order = new Order($this->paypalOrder->id_order);

        if (false == Validate::isLoadedObject($order)) {
            return '';
        }

        $shipping = $order->getShipping();

        if (empty($shipping[0]['tracking_number'])) {
            return '';
        }

        return $shipping[0]['tracking_number'];
    }

    protected function getCarrier()
    {
        $order = new Order($this->paypalOrder->id_order);

        if (false == Validate::isLoadedObject($order)) {
            return new Carrier();
        }

        $shipping = $order->getShipping();

        if (empty($shipping[0]['id_carrier'])) {
            return new Carrier();
        }

        return new Carrier((int) $shipping[0]['id_carrier']);
    }
}
