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
use PaypalAddons\classes\PUI\DataUserForm;
use PaypalAddons\services\PaypalContext;

/**
 * Prepare EC payment
 */
class PaypalPuiValidateModuleFrontController extends PaypalAbstarctModuleFrontController
{
    /* @var $method MethodPPP*/
    protected $method;

    public function init()
    {
        parent::init();
        PaypalContext::getContext()->set('client-session-id', Tools::getValue('sessionId'));
        $this->method = AbstractMethodPaypal::load('PPP');
    }

    /**
     * @see FrontController::postProcess()
     */
    public function postProcess()
    {
        try {
            $paypal = Module::getInstanceByName($this->name);
            $this->method->setPuiDataUser($this->getUserDataFromRequest());
            $this->method->initPui();
            $cart = Context::getContext()->cart;
            $customer = new Customer($cart->id_customer);
            $this->redirectUrl = 'index.php?controller=order-confirmation&id_cart=' . $cart->id . '&id_module=' . $paypal->id . '&id_order=' . $paypal->currentOrder . '&key=' . $customer->secure_key;
        } catch (PaypalAddons\classes\PaypalException $e) {
            $this->_errors['error_code'] = $e->getCode();
            $this->_errors['error_msg'] = $e->getMessage();
            $this->_errors['msg_long'] = $e->getMessageLong();
        } catch (Exception $e) {
            $this->_errors['error_code'] = $e->getCode();
            $this->_errors['error_msg'] = $e->getMessage();
        }

        Context::getContext()->cookie->__unset('paypal_pSc');
        Context::getContext()->cookie->__unset('paypal_pSc_email');

        if (!empty($this->_errors)) {
            $this->redirectUrl = Context::getContext()->link->getModuleLink($this->name, 'error', $this->_errors);
        }
    }

    protected function getUserDataFromRequest()
    {
        $userData = new DataUserForm();
        $userData->setFirstName(Tools::getValue('paypal_pui_firstname', ''));
        $userData->setLastName(Tools::getValue('paypal_pui_lastname', ''));
        $userData->setEmail(Tools::getValue('paypal_pui_email', ''));
        $userData->setBirth(Tools::getValue('paypal_pui_birhday', ''));
        $userData->setPhone(Tools::getValue('paypal_pui_phone', ''));

        return $userData;
    }
}
