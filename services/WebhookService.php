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

namespace PaypalAddons\services;

use PaypalAddons\classes\Webhook\WebhookEventHandler;

if (!defined('_PS_VERSION_')) {
    exit;
}

class WebhookService
{
    /** @var \PayPal */
    protected $module;

    public function __construct()
    {
        /* @phpstan-ignore-next-line */
        $this->module = \Module::getInstanceByName('paypal');
    }

    /**
     * @param \PaypalOrder $paypalOrder
     *
     * @return \PaypalWebhook
     *
     * @throws \PrestaShopException
     */
    public function createForOrder(\PaypalOrder $paypalOrder, $idState = 0)
    {
        $query = (new \DbQuery())
            ->select('id_paypal_webhook')
            ->from(\PaypalWebhook::$definition['table'])
            ->where('id_paypal_order = ' . (int) $paypalOrder->id)
            ->where('id_webhook IS NULL OR id_webhook = ""');

        if ($idState) {
            $query->where('id_state = ' . (int) $idState);
        }

        $idPaypalWebhook = (int) \Db::getInstance()->getValue($query);

        if ($idPaypalWebhook) {
            $webhook = new \PaypalWebhook($idPaypalWebhook);
        } else {
            $webhook = new \PaypalWebhook();
        }

        $webhook->id_paypal_order = (int) $paypalOrder->id;
        $webhook->id_state = $idState;
        $webhook->save();

        return $webhook;
    }

    /**
     * @param \PaypalOrder|null $paypalOrder
     *
     * @return \PaypalWebhook[]
     */
    public function getPendingWebhooks(\PaypalOrder $paypalOrder = null, $delay = null)
    {
        $webhooks = [];
        $query = (new \DbQuery())
            ->select('id_paypal_webhook')
            ->from(\PaypalWebhook::$definition['table'])
            ->where('id_webhook IS NULL OR id_webhook = ""');

        if ($paypalOrder) {
            $query->where('id_paypal_order = ' . (int) $paypalOrder->id);
        }
        if ($delay) {
            $query->where(
                sprintf(
                    'date_add < DATE_SUB(STR_TO_DATE(\'%s\', GET_FORMAT(DATETIME,\'ISO\')), INTERVAL %d HOUR)',
                    date(\PaypalWebhook::DATE_FORMAT),
                    (int) $delay
                )
            );
        }

        try {
            $result = \Db::getInstance()->executeS($query);
        } catch (\Throwable $e) {
            return $webhooks;
        }

        if (empty($result)) {
            return $webhooks;
        }

        foreach ($result as $row) {
            try {
                $webhook = new \PaypalWebhook($row['id_paypal_webhook']);
                if (\Validate::isLoadedObject($webhook) === true) {
                    $webhooks[] = $webhook;
                }
            } catch (\Throwable $e) {
            }
        }

        return $webhooks;
    }

    public function checkAndHandleNotifications()
    {
        $method = \PaypalAddons\classes\AbstractMethodPaypal::load();

        if (false === $this->module->getWebhookOption()->isEnable()) {
            return;
        }
        if (false === $method->isConfigured()) {
            return;
        }
        if (empty($this->module->getWebhookService()->getPendingWebhooks())) {
            return;
        }

        $res = $method->getWebhookEventList(['page_size' => 50]);
        $handler = new WebhookEventHandler();

        foreach ($res->getList() as $event) {
            $handler->handle($event);
        }
        /** @var \PaypalWebhook $webhookEvent */
        foreach ($this->module->getWebhookService()->getPendingWebhooks(null, 72) as $webhookEvent) {
            $webhookEvent->delete();
        }
    }
}
