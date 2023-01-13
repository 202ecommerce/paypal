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

namespace PaypalAddons\services\Order;

use Order;
use PayPal;

class RefundAmountCalculator
{
    /**
     * @param mixed $params
     *
     * @return float
     */
    public function calculate($params)
    {
        $amount = 0;

        if (empty($params['productList'])) {
            return $amount;
        }

        foreach ($params['productList'] as $product) {
            $amount += \Tools::ps_round($product['amount'], PayPal::getPrecision());
        }

        if (false == empty($params['partialRefundShippingCost'])) {
            $amount += $params['partialRefundShippingCost'];
        }

        // For prestashop version > 1.7.7
        if (false == empty($params['cancel_product'])) {
            $refundData = $params['cancel_product'];
            $amount += floatval(str_replace(',', '.', $refundData['shipping_amount']));
        }

        $amount -= $this->calculateDiscount($params);

        return $amount;
    }

    /**
     * @param mixed $params
     *
     * @return float
     */
    public function calculateDiscount($params)
    {
        // $params differs according PS version
        $amount = 0;

        if (false == empty($params['refund_voucher_off'])) {
            if (false == empty($params['order_discount_price'])) {
                return floatval($params['order_discount_price']);
            }
        }

        if (false == empty($params['cancel_product']['voucher_refund_type'])) {
            if ($params['cancel_product']['voucher_refund_type'] == 1) {
                if ($params['order'] instanceof Order) {
                    return (float) $params['order']->total_discounts_tax_incl;
                }
            }
        }

        return $amount;
    }
}
