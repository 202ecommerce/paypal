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

namespace PaypalAddons\classes\Form;

use PaypalAddons\classes\AbstractMethodPaypal;
use PaypalAddons\classes\Constants\PaypalConfigurations;
use PaypalAddons\classes\Constants\WebHookConf;
use PaypalAddons\classes\Webhook\CreateWebhook;
use PaypalAddons\classes\Webhook\WebhookOption;

if (!defined('_PS_VERSION_')) {
    exit;
}

class OrderStatusForm implements FormInterface
{
    /** @var \PayPal */
    protected $module;

    protected $method;

    protected $webhookOption;

    public function __construct()
    {
        /* @phpstan-ignore-next-line */
        $this->module = \Module::getInstanceByName('paypal');
        $this->method = AbstractMethodPaypal::load();
        $this->webhookOption = new WebhookOption();
    }

    public function getDescription()
    {
        $fields = [];
        $orderStatuses = $this->getStatusList();

        $fields[PaypalConfigurations::CUSTOMIZE_ORDER_STATUS] = [
            'type' => 'switch',
            'label' => $this->module->l('Customize your order status', 'AdminPayPalCustomizeCheckoutController'),
            'name' => PaypalConfigurations::CUSTOMIZE_ORDER_STATUS,
            'hint' => $this->module->l('Please use this option only if you want to change the assigned default PayPal status on PrestaShop Order statuses.', 'AdminPayPalCustomizeCheckoutController'),
            'values' => [
                [
                    'id' => PaypalConfigurations::CUSTOMIZE_ORDER_STATUS . '_on',
                    'value' => 1,
                    'label' => $this->module->l('Enabled', 'AdminPayPalCustomizeCheckoutController'),
                ],
                [
                    'id' => PaypalConfigurations::CUSTOMIZE_ORDER_STATUS . '_off',
                    'value' => 0,
                    'label' => $this->module->l('Disabled', 'AdminPayPalCustomizeCheckoutController'),
                ],
            ],
            'value' => (int) \Configuration::get(PaypalConfigurations::CUSTOMIZE_ORDER_STATUS),
        ];
        $fields[PaypalConfigurations::OS_REFUNDED] = [
            'type' => 'select',
            'label' => $this->module->l('Order Status for triggering the refund on PayPal', 'AdminPayPalCustomizeCheckoutController'),
            'name' => PaypalConfigurations::OS_REFUNDED,
            'hint' => $this->module->l('You can refund the orders paid via PayPal directly via your PrestaShop BackOffice. Here you can choose the order status that triggers the refund on PayPal. Choose the option "no actions" if you would like to change the order status without triggering the automatic refund on PayPal.', 'AdminPayPalCustomizeCheckoutController'),
            'desc' => $this->module->l('Default status : Refunded', 'AdminPayPalCustomizeCheckoutController'),
            'options' => $orderStatuses,
            'value' => (int) \Configuration::get(PaypalConfigurations::OS_REFUNDED),
        ];

        if ($this->method->getIntent() == 'CAPTURE') {
            $fields[PaypalConfigurations::OS_CANCELED] = [
                'type' => 'select',
                'label' => $this->module->l('Order Status for triggering the cancellation on PayPal', 'AdminPayPalCustomizeCheckoutController'),
                'name' => PaypalConfigurations::OS_CANCELED,
                'hint' => $this->module->l('You can cancel orders paid via PayPal directly via your PrestaShop BackOffice. Here you can choose the order status that triggers the PayPal voiding of an authorized transaction on PayPal. Choose the option "no actions" if you would like to change the order status without triggering the automatic cancellation on PayPal.', 'AdminPayPalCustomizeCheckoutController'),
                'desc' => $this->module->l(' Default status : Canceled', 'AdminPayPalCustomizeCheckoutController'),
                'options' => $orderStatuses,
                'value' => (int) \Configuration::get(PaypalConfigurations::OS_CANCELED),
            ];
        }

        if ($this->method instanceof \MethodEC) {
            if ($this->method->getIntent() == 'AUTHORIZE') {
                $fields[PaypalConfigurations::OS_WAITING_VALIDATION] = [
                    'type' => 'select',
                    'label' => $this->module->l('Payment captured and waiting for validation by admin', 'MethodEC'),
                    'name' => PaypalConfigurations::OS_WAITING_VALIDATION,
                    'desc' => $this->module->l('Default status : Waiting for PayPal payment', 'AdminPayPalCustomizeCheckoutController'),
                    'options' => $orderStatuses,
                    'value' => (int) \Configuration::get(PaypalConfigurations::OS_WAITING_VALIDATION),
                ];
                $fields[PaypalConfigurations::OS_ACCEPTED] = [
                    'type' => 'select',
                    'label' => $this->module->l('Payment accepted via BO (call PayPal to get the payment)', 'AdminPayPalCustomizeCheckoutController'),
                    'name' => PaypalConfigurations::OS_ACCEPTED,
                    'hint' => $this->module->l('You are currently using the Authorize mode. It means that you separate the payment authorization from the capture of the authorized payment. For capturing the authorized payement you have to change the order status to "payment accepted" (or to a custom status with the same meaning). Here you can choose a custom order status for accepting the order and validating transaction in Authorize mode.', 'AdminPayPalCustomizeCheckoutController'),
                    'desc' => $this->module->l('Default status : Payment accepted', 'AdminPayPalCustomizeCheckoutController'),
                    'options' => $orderStatuses,
                    'value' => (int) \Configuration::get(PaypalConfigurations::OS_ACCEPTED),
                ];
                $fields[PaypalConfigurations::OS_CAPTURE_CANCELED] = [
                    'type' => 'select',
                    'label' => $this->module->l('Payment canceled via BO (call PayPal to cancel the capture)', 'AdminPayPalCustomizeCheckoutController'),
                    'name' => PaypalConfigurations::OS_CAPTURE_CANCELED,
                    'hint' => $this->module->l('You are currently using the Authorize mode. It means that you separate the payment authorization from the capture of the authorized payment. For canceling the authorized payment you have to change the order status to "canceled" (or to a custom status with the same meaning). Here you can choose an order status for canceling the order and voiding the transaction in Authorize mode.', 'AdminPayPalCustomizeCheckoutController'),
                    'desc' => $this->module->l('Default status : Canceled', 'AdminPayPalCustomizeCheckoutController'),
                    'options' => $orderStatuses,
                    'value' => (int) \Configuration::get(PaypalConfigurations::OS_CAPTURE_CANCELED),
                ];
            } else {
                $fields[PaypalConfigurations::OS_ACCEPTED_TWO] = [
                    'type' => 'select',
                    'label' => $this->module->l('Payment accepted and transaction completed', 'MethodPPP'),
                    'name' => PaypalConfigurations::OS_ACCEPTED_TWO,
                    'hint' => $this->module->l('You are currently using the Sale mode (the authorization and capture occur at the same time as the sale). So the payement is accepted instantly and the new order is created in the "Payment accepted" status. You can customize the status for orders with completed transactions. Ex : you can create an additional status "Payment accepted via PayPal" and set it as the default status.', 'MethodPPP'),
                    'desc' => $this->module->l('Default status : Payment accepted', 'MethodPPP'),
                    'options' => $orderStatuses,
                    'value' => (int) \Configuration::get(PaypalConfigurations::OS_ACCEPTED_TWO),
                ];
            }
        } else {
            $fields[PaypalConfigurations::OS_ACCEPTED_TWO] = [
                'type' => 'select',
                'label' => $this->module->l('Payment accepted and transaction completed', 'MethodPPP'),
                'name' => PaypalConfigurations::OS_ACCEPTED_TWO,
                'hint' => $this->module->l('You are currently using the Sale mode (the authorization and capture occur at the same time as the sale). So the payement is accepted instantly and the new order is created in the "Payment accepted" status. You can customize the status for orders with completed transactions. Ex : you can create an additional status "Payment accepted via PayPal" and set it as the default status.', 'MethodPPP'),
                'desc' => $this->module->l('Default status : Payment accepted', 'MethodPPP'),
                'options' => $orderStatuses,
                'value' => (int) \Configuration::get(PaypalConfigurations::OS_ACCEPTED_TWO),
            ];
        }

        if ($this->method instanceof \MethodMB && $this->method->getIntent() == 'AUTHORIZE') {
            $fields[PaypalConfigurations::OS_WAITING_VALIDATION] = [
                'type' => 'select',
                'label' => $this->module->l('Payment authorized, waiting for validation by admin (paid via PayPal express checkout)', 'MethodMB'),
                'name' => PaypalConfigurations::OS_WAITING_VALIDATION,
                'hint' => $this->module->l('You are currently using the Authorize mode. It means that you separate the payment authorization from the capture of the authorized payment. By default the orders will be created in the "Waiting for PayPal payment" but you can customize it if needed.', 'MethodMB'),
                'desc' => $this->module->l('Default status : Waiting for PayPal payment', 'MethodMB'),
                'options' => $orderStatuses,
                'value' => (int) \Configuration::get(PaypalConfigurations::OS_WAITING_VALIDATION),
            ];
        }

        if ($this->webhookOption->isEnable() && $this->webhookOption->isEligibleContext()) {
            if ($this->method->getIntent() == 'CAPTURE') {
                $fields[PaypalConfigurations::OS_WAITING_VALIDATION] = [
                    'type' => 'select',
                    'label' => $this->module->l('Payment captured and waiting for validation by webhook', 'AdminPayPalCustomizeCheckoutController'),
                    'name' => PaypalConfigurations::OS_WAITING_VALIDATION,
                    'desc' => $this->module->l('Default status : Waiting for PayPal payment', 'AdminPayPalCustomizeCheckoutController'),
                    'options' => $orderStatuses,
                    'value' => (int) \Configuration::get(PaypalConfigurations::OS_WAITING_VALIDATION),
                ];
            }
        }

        if ($this->webhookOption->isEligibleContext()) {
            $fields[WebHookConf::ENABLE] = [
                'type' => 'switch',
                'label' => $this->module->l('Enable PayPal webhooks', 'AdminPayPalCustomizeCheckoutController'),
                'name' => WebHookConf::ENABLE,
                'hint' => $this->module->l('PayPal webhooks allow you to automatically update the order status on PrestaShop once the status of transaction on PayPal is changed.', 'AdminPayPalCustomizeCheckoutController'),
                'values' => [
                    [
                        'id' => WebHookConf::ENABLE . '_on',
                        'value' => 1,
                        'label' => $this->module->l('Enabled', 'AdminPayPalCustomizeCheckoutController'),
                    ],
                    [
                        'id' => WebHookConf::ENABLE . '_off',
                        'value' => 0,
                        'label' => $this->module->l('Disabled', 'AdminPayPalCustomizeCheckoutController'),
                    ],
                ],
                'value' => $this->webhookOption->isEnable(),
                'variant' => 'primary',
            ];
        }

        return [
            'legend' => [
                'title' => $this->module->l('Order status', 'AdminPayPalCustomizeCheckoutController'),
            ],
            'fields' => $fields,
            'submit' => [
                'title' => $this->module->l('Save', 'AdminPayPalCustomizeCheckoutController'),
                'name' => 'orderStatusForm',
            ],
            'id_form' => 'pp_order_status_form',
            'help' => $this->getHelpInfo(),
        ];
    }

    public function save($data = null)
    {
        if (is_null($data)) {
            $data = \Tools::getAllValues();
        }

        if (empty($data['orderStatusForm'])) {
            return false;
        }

        \Configuration::updateValue(
            PaypalConfigurations::CUSTOMIZE_ORDER_STATUS,
            isset($data[PaypalConfigurations::CUSTOMIZE_ORDER_STATUS]) ? 1 : 0
        );
        if (isset($data[PaypalConfigurations::OS_REFUNDED])) {
            \Configuration::updateValue(
                PaypalConfigurations::OS_REFUNDED,
                (int) $data[PaypalConfigurations::OS_REFUNDED]
            );
        }
        if (isset($data[PaypalConfigurations::OS_CANCELED])) {
            \Configuration::updateValue(
                PaypalConfigurations::OS_CANCELED,
                (int) $data[PaypalConfigurations::OS_CANCELED]
            );
        }
        if (isset($data[PaypalConfigurations::OS_ACCEPTED])) {
            \Configuration::updateValue(
                PaypalConfigurations::OS_ACCEPTED,
                (int) $data[PaypalConfigurations::OS_ACCEPTED]
            );
        }
        if (isset($data[PaypalConfigurations::OS_ACCEPTED_TWO])) {
            \Configuration::updateValue(
                PaypalConfigurations::OS_ACCEPTED_TWO,
                (int) $data[PaypalConfigurations::OS_ACCEPTED_TWO]
            );
        }
        if (isset($data[PaypalConfigurations::OS_WAITING_VALIDATION])) {
            \Configuration::updateValue(
                PaypalConfigurations::OS_WAITING_VALIDATION,
                (int) $data[PaypalConfigurations::OS_WAITING_VALIDATION]
            );
        }
        if (isset($data[PaypalConfigurations::OS_CAPTURE_CANCELED])) {
            \Configuration::updateValue(
                PaypalConfigurations::OS_CAPTURE_CANCELED,
                (int) $data[PaypalConfigurations::OS_CAPTURE_CANCELED]
            );
        }

        if (isset($data[WebHookConf::ENABLE])) {
            $response = (new CreateWebhook())->setUpdate(false)->execute();
            \Configuration::updateValue(WebHookConf::ENABLE, (int) $response->isSuccess());
        // ToDo: Need show warning if creation of the webhook is failed
        } else {
            \Configuration::updateValue(WebHookConf::ENABLE, 0);
        }

        return true;
    }

    protected function getStatusList()
    {
        $orderStatuses = [
            [
                'value' => 0,
                'title' => $this->module->l('No action'),
            ],
        ];
        $prestashopOrderStatuses = \OrderState::getOrderStates(\Context::getContext()->language->id);

        foreach ($prestashopOrderStatuses as $prestashopOrderStatus) {
            $orderStatuses[] = [
                'value' => $prestashopOrderStatus['id_order_state'],
                'title' => $prestashopOrderStatus['name'],
            ];
        }

        return $orderStatuses;
    }

    protected function getHelpInfo()
    {
        $tpl = \Context::getContext()->smarty->createTemplate(_PS_MODULE_DIR_ . $this->module->name . '/views/templates/admin/_partials/messages/form-help-info/order-status.tpl');
        $tpl->assign(
            'checkAndHandleWebhookNotification',
            \Context::getContext()->link->getModuleLink(
                $this->module->name,
                'checkAndHandleWebhookNotification',
                ['token' => $this->module->getSecurityKey()]
            )
        );

        return $tpl->fetch();
    }
}
