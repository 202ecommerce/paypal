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

use PaypalAddons\classes\AbstractMethodPaypal;
use PaypalAddons\classes\Constants\WebHookConf;
use PaypalAddons\classes\Webhook\CreateWebhook;
use PaypalAddons\classes\Webhook\WebhookAvailability;

if (!defined('_PS_VERSION_')) {
    exit;
}

class Checker
{
    protected $context;

    protected $module;

    protected $method;

    protected $webhookAvailability;

    public function __construct()
    {
        $this->context = \Context::getContext();
        $this->module = \Module::getInstanceByName('paypal');
        $this->webhookAvailability = new WebhookAvailability();
        $this->method = AbstractMethodPaypal::load();
    }

    public function checkTLSVersion()
    {
        $return = [
            'status' => false,
            'error_message' => '',
        ];

        if (defined('CURL_SSLVERSION_TLSv1_2') == false) {
            define('CURL_SSLVERSION_TLSv1_2', 6);
        }

        $tls_server = $this->context->link->getModuleLink($this->module->name, 'tlscurltestserver');
        $return['ping_page'] = $tls_server;
        $curl = curl_init($tls_server);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($curl, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
        $response = curl_exec($curl);
        if (trim($response) != 'ok') {
            $return['status'] = false;
            $curl_info = curl_getinfo($curl);
            if ($curl_info['http_code'] == 401) {
                $return['error_message'] = $this->module->l('401 Unauthorised. Please note that the TLS verification can\'t be done if you have htaccess password protection, debug or maintenance mode enabled on your web site.', 'AdminPayPalController');
            } else {
                $return['error_message'] = curl_error($curl);
            }
        } else {
            $return['status'] = true;
        }

        return $return;
    }

    public function checkWebhook()
    {
        $return = [
            'state' => true,
            'message' => $this->module->l('PayPal webhooks are enabled with success.', 'AdminPayPalController'),
        ];

        $webhookAvailable = $this->webhookAvailability->check();

        if ($webhookAvailable->isSuccess() == false) {
            $return['state'] = false;
            $return['message'] = $this->context->smarty->fetch(
                _PS_MODULE_DIR_ . $this->module->name . '/views/templates/admin/_partials/messages/webhookhandler_not_available.tpl'
            );
        }

        if ($return['state'] && !$this->isWebhookCreated()) {
            $return['state'] = false;
            $return['message'] = $this->module->l('PayPal webhooks can not be enabled. The webhook listener was not created. Webhooks are not used by the module until the moment the problem will be fixed. Please try to refresh the page and click on \'check requirements\' again.', 'AdminPayPalController');
        }

        \Configuration::updateValue(WebHookConf::AVAILABLE, (int) $return['state']);

        return $return;
    }

    protected function isWebhookCreated()
    {
        $response = (new CreateWebhook($this->method))
            ->setUpdate(false)
            ->execute();

        return $response->isSuccess();
    }

    public function isProductsAvailable(\Cart $cart)
    {
        if (!$this->isAllProductsInStock($cart)) {
            return false;
        }
        if (method_exists($cart, 'checkAllProductsAreStillAvailableInThisState') && !$cart->checkAllProductsAreStillAvailableInThisState()) {
            return false;
        }
        if (method_exists($cart, 'checkAllProductsHaveMinimalQuantities') && !$cart->checkAllProductsHaveMinimalQuantities()) {
            return false;
        }

        return true;
    }

    public function isAllProductsInStock(\Cart $cart)
    {
        if (version_compare(_PS_VERSION_, '1.7.3.2', '>=')) {
            return $cart->isAllProductsInStock(true);
        }

        foreach ($cart->getProducts(false, false, null, false) as $product) {
            if ($product['is_virtual']) {
                continue;
            }
            $idProductAttribute = empty($product['id_product_attribute']) ? null : $product['id_product_attribute'];
            $availableOutOfStock = \Product::isAvailableWhenOutOfStock($product['out_of_stock']);
            $productQuantity = \Product::getQuantity(
                $product['id_product'],
                $idProductAttribute,
                null,
                $cart,
                $product['id_customization']
            );

            if ($productQuantity < 0 && !$availableOutOfStock) {
                return false;
            }
        }

        return true;
    }
}
