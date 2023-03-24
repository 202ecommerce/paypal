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

use Exception;
use PaypalAddons\classes\AbstractMethodPaypal;
use PaypalAddons\classes\Exception\RefundCalculationException;
use PaypalOrder;
use Throwable;

class PaymentTotalAmount
{
    /**
     * @param PaypalOrder $paypalOrder
     *
     * @return float
     *
     * @throws RefundCalculationException
     */
    public function get(PaypalOrder $paypalOrder)
    {
        $method = AbstractMethodPaypal::load($paypalOrder->method);
        $total = 0;
        $totalRefund = 0;

        try {
            $order = $method->getInfo($paypalOrder->id_payment);
            $payments = $order->getData()->result->purchase_units[0]->payments;

            if (isset($payments->captures)) {
                foreach ($payments->captures as $capture) {
                    $total += $capture->amount->value;
                }
            }

            if (isset($payments->refunds)) {
                foreach ($payments->refunds as $refund) {
                    $totalRefund += $refund->amount->value;
                }
            }
        } catch (Throwable $e) {
            throw new RefundCalculationException($e->getMessage());
        } catch (Exception $e) {
            throw new RefundCalculationException($e->getMessage());
        }

        return (float) $total - $totalRefund;
    }
}
