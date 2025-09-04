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

use PaypalAddons\classes\AbstractMethodPaypal;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Validate EC payment
 */
class PaypalEcValidationModuleFrontController extends PaypalAbstarctModuleFrontController
{
    public function init()
    {
        parent::init();

        if (Tools::isSubmit('paymentData')) {
            $paymentData = $this->parsePaymentData(Tools::getValue('paymentData'));
            $this->values['paymentId'] = $paymentData->getOrderId();
        }

        $this->values['short_cut'] = Tools::getvalue('short_cut');

        if (empty($this->values['paymentId'])) {
            $this->values['paymentId'] = Tools::getvalue('token');
        }
    }

    /**
     * @see FrontController::postProcess()
     */
    public function postProcess()
    {
        /** @var MethodEC $method_ec */
        $method_ec = AbstractMethodPaypal::load();

        try {
            $method_ec->setParameters($this->values);

            if ($method_ec->getShortCut()) {
                /** @var PaypalAddons\classes\API\Response\Response $resultPath */
                $resultPath = $method_ec->doOrderPatch();

                if ($resultPath->isSuccess() == false) {
                    throw new Exception($resultPath->getError()->getMessage());
                }
            }

            $method_ec->validation();
            $cart = Context::getContext()->cart;
            /* @phpstan-ignore-next-line */
            $customer = new Customer($cart->id_customer);
            $this->redirectUrl = 'index.php?controller=order-confirmation&id_cart=' . $cart->id . '&id_module=' . $this->module->id . '&id_order=' . $this->module->currentOrder . '&key=' . $customer->secure_key;
        } catch (PaypalAddons\classes\Exception\PayerActionRequired $e) {
            $this->redirectUrl = $e->getPayerActionLink();

            return;
        } catch (PaypalAddons\classes\PaypalException $e) {
            $this->_errors['error_code'] = $e->getCode();
            $this->_errors['error_msg'] = $e->getMessage();
            $this->_errors['msg_long'] = $e->getMessageLong();
        } catch (Exception $e) {
            $this->_errors['error_code'] = $e->getCode();
            $this->_errors['error_msg'] = $e->getMessage();
        } finally {
            $this->transaction_detail = $method_ec->getDetailsTransaction();
        }

        // unset cookie of payment init
        Context::getContext()->cookie->__unset('paypal_ecs');
        Context::getContext()->cookie->__unset('paypal_ecs_email');

        if (!empty($this->_errors)) {
            $this->redirectUrl = Context::getContext()->link->getModuleLink($this->name, 'error', $this->_errors);
        }
    }
}
