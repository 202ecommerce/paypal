<?php
/**
 *  2007-2022 PayPal
 *
 *  NOTICE OF LICENSE
 *
 *  This source file is subject to the Academic Free License (AFL 3.0)
 *  that is bundled with this package in the file LICENSE.txt.
 *  It is also available through the world-wide-web at this URL:
 *  http://opensource.org/licenses/afl-3.0.php
 *  If you did not receive a copy of the license and are unable to
 *  obtain it through the world-wide-web, please send an email
 *  to license@prestashop.com so we can send you a copy immediately.
 *
 *  DISCLAIMER
 *
 *  Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 *  versions in the future. If you wish to customize PrestaShop for your
 *  needs please refer to http://www.prestashop.com for more information.
 *
 *  @author 2007-2022 PayPal
 *  @author 202 ecommerce <tech@202-ecommerce.com>
 *  @copyright PayPal
 *  @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

require_once __DIR__ . '/../../classes/Services/Token.php';
require_once __DIR__ . '/../../classes/Transaction.php';
require_once _PS_MODULE_DIR_ . 'paypal/classes/SDK/Client/PaypalClient.php';
require_once _PS_MODULE_DIR_ . 'paypal/classes/SDK/Request/Order/OrdersCreateRequest.php';
require_once _PS_MODULE_DIR_ . 'paypal/classes/SDK/Request/Order/OrdersCaptureRequest.php';
require_once _PS_MODULE_DIR_ . 'paypal/classes/Builder/OrderBuilder.php';

class PayPalValidateBnplModuleFrontController extends ModuleFrontController
{
    /** @var PaypalClient*/
    protected $client;
    public function init()
    {
        parent::init();
        $this->client = new PaypalClient();
    }
    public function checkAccess()
    {
        if (Validate::isLoadedObject($this->context->cart)) {
            if (Tools::getValue('token') === Token::generateByCart($this->context->cart)) {
                return true;
            }
        }

        http_response_code(401);
        exit;
    }

    public function initContent()
    {
        if ($this->ajax) {
            return;
        }

        $transaction = $this->getTransactionFromRequest();
        $cart = $this->context->cart;

        try {
            $this->module->validateOrder(
                $cart->id,
                $this->getIdOrderState(),
                $transaction->getTotalPaid(),
                $this->module->displayName,
                $this->module->l('Payment accepted.'),
                $transaction->toArray()
            );
        } catch (Throwable $e) {
            return $this->redirectToErrorPage($e);
        } catch (Exception $e) {
            return $this->redirectToErrorPage($e);
        }

        $queryParams = [
            'fc' => 'module',
            'module' => 'paypal',
            'controller' => 'submit',
            'id_cart' => $cart->id,
            'id_module' => $this->module->id,
            'id_order' => $this->module->currentOrder,
            'key' => $this->context->customer->secure_key
        ];
        Tools::redirect('index.php?' . http_build_query($queryParams));
    }

    protected function getTransactionFromRequest()
    {
        $transaction = new Transaction();

        if (empty(Tools::getValue('paymentData'))) {
            return $transaction;
        }

        $paymentData = json_decode(Tools::getValue('paymentData'), true);

        if (false === empty($paymentData['purchase_units'][0]['payments']['captures'][0]['id'])) {
            $transaction->setIdTransaction($paymentData['purchase_units'][0]['payments']['captures'][0]['id']);
        }

        if (false === empty($paymentData['purchase_units'][0]['payments']['captures'][0]['amount']['value'])) {
            $transaction->setTotalPaid((float) $paymentData['purchase_units'][0]['payments']['captures'][0]['amount']['value']);
        }

        if (false === empty($paymentData['purchase_units'][0]['amount']['breakdown']['shipping']['value'])) {
            $transaction->setShipping((float) $paymentData['purchase_units'][0]['amount']['breakdown']['shipping']['value']);
        }

        if (false === empty($paymentData['purchase_units'][0]['payments']['captures'][0]['amount']['currency_code'])) {
            $transaction->setCurrency($paymentData['purchase_units'][0]['payments']['captures'][0]['amount']['currency_code']);
        }

        if (false === empty($paymentData['purchase_units'][0]['payments']['captures'][0]['status'])) {
            $transaction->setPaymentStatus($paymentData['purchase_units'][0]['payments']['captures'][0]['status']);
        }

        if (false === empty($paymentData['purchase_units'][0]['payments']['captures'][0]['create_time'])) {
            $transaction->setPaymentDate($paymentData['purchase_units'][0]['payments']['captures'][0]['create_time']);
        }

        return $transaction;
    }

    protected function getIdOrderState()
    {
        return (int) Configuration::get('PS_OS_PAYMENT');
    }

    protected function redirectToErrorPage($e)
    {
        $this->context->smarty->assign([
            'logs' => $e->getMessage(),
            'message' => $this->module->l('Error occurred:'),
        ]);
        $display = $this->initDisplayController();
        $display->setTemplate(_PS_MODULE_DIR_ . 'paypal/views/templates/front/error.tpl');
        $display->run();
    }

    protected function initDisplayController()
    {
        return (_PS_VERSION_ < '1.5') ? new BWDisplay() : new FrontController();
    }

    public function displayAjaxCreateOrder()
    {
        $return = [
            'success' => false,
            'idOrder' => null
        ];
        $response = $this->client->execute(new OrdersCreateRequest(new OrderBuilder($this->context)));

        if ($response->getCode() < 300 && $response->getCode() > 199) {
            $return['success'] = true;

            if ($response instanceof HttpJsonResponse) {
                $order = $response->toArray();

                if (false === empty($order['id'])) {
                    $return['idOrder'] = $order['id'];
                }
            }
        }

        die(json_encode($return));
    }

    public function displayAjaxCaptureOrder()
    {
        $order = json_decode(Tools::getValue('order'), true);
        $return = [
            'success' => false,
        ];

        if (false === empty($order['orderID'])) {
            $response = $this->client->execute(new OrdersCaptureRequest($order['orderID']));

            if ($response->getCode() < 300 && $response->getCode() > 199) {
                $return['success'] = true;

                if ($response instanceof HttpJsonResponse) {
                    $return = array_merge($return, $response->toArray());
                }
            }
        }

        die(json_encode($return));
    }
}
