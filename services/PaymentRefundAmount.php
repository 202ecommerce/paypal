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
use PayPal\Api\WebhookEvent;
use PaypalAddons\classes\AbstractMethodPaypal;
use PaypalAddons\classes\Constants\WebHookType;
use PaypalAddons\classes\Exception\RefundCalculationException;
use PaypalOrder;
use PaypalWebhook;
use PrestaShopCollection;
use Throwable;

class PaymentRefundAmount
{
    /**
     * @param PaypalOrder $paypalOrder
     *
     * @return float
     */
    public function calculateTotalRefunded(PaypalOrder $paypalOrder)
    {
        $method = AbstractMethodPaypal::load($paypalOrder->method);
        $totalRefund = 0;

        try {
            $order = $method->getInfo($paypalOrder->id_payment);
            $payments = $order->getData()->result->purchase_units[0]->payments;

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

        return (float) $totalRefund;
    }

    /**
     * @param PaypalOrder $paypalOrder
     *
     * @return float
     */
    public function calculateReceivedWebhookEvent(PaypalOrder $paypalOrder)
    {
        $webhookEvents = $this->getWebhookEvents($paypalOrder);
        $totalRefunded = 0;

        foreach ($webhookEvents as $webhookEvent) {
            $webhookEvent = (new WebhookEvent())->fromJson($webhookEvent->data);

            if ($webhookEvent->event_type != WebHookType::CAPTURE_REFUNDED) {
                continue;
            }

            try {
                $totalRefunded += $webhookEvent->resource->amount->value;
            } catch (Throwable $e) {
            } catch (Exception $e) {
            }
        }

        return (float) $totalRefunded;
    }

    /**
     * @param PaypalOrder $paypalOrder
     *
     * @return PaypalWebhook[]
     */
    protected function getWebhookEvents(PaypalOrder $paypalOrder)
    {
        try {
            $collection = (new PrestaShopCollection(PaypalWebhook::class))
                ->where('id_paypal_order', '=', (int) $paypalOrder->id);

            return $collection->getResults();
        } catch (Throwable $e) {
            return [];
        } catch (Exception $e) {
            return [];
        }
    }
}
