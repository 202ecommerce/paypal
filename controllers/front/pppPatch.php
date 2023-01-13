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

use PaypalAddons\classes\AbstractMethodPaypal;

/**
 * Update PPP payment request before initialize it.
 */
class PaypalPppPatchModuleFrontController extends PaypalAbstarctModuleFrontController
{
    public function postProcess()
    {
        $method_ppp = AbstractMethodPaypal::load('PPP');
        $idPayment = Tools::getValue('idPayment') ? Tools::getValue('idPayment') : Context::getContext()->cookie->paypal_plus_payment;
        $method_ppp->setPaymentId($idPayment);

        if ($idPayment) {
            try {
                $resultPath = $method_ppp->doOrderPatch();

                if ($resultPath->isSuccess()) {
                    $this->jsonValues = ['success' => $resultPath->isSuccess()];
                    Context::getContext()->cookie->__unset('paypal_plus_payment');
                } else {
                    $this->_errors['error_msg'] = $resultPath->getError()->getMessage();
                    $this->jsonValues = ['success' => false, 'redirect_link' => Context::getContext()->link->getModuleLink($this->name, 'error', $this->_errors)];
                }
            } catch (Exception $e) {
                $this->_errors['error_code'] = $e->getCode();
                $this->_errors['error_msg'] = $e->getMessage();
                $this->jsonValues = ['success' => false, 'redirect_link' => Context::getContext()->link->getModuleLink($this->name, 'error', $this->_errors)];
            }
        }
    }
}
