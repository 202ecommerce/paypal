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
 * Prepare EC payment
 */
class PaypalEcInitModuleFrontController extends PaypalAbstarctModuleFrontController
{
    /* @var $method AbstractMethodPaypal*/
    protected $method;

    public function init()
    {
        parent::init();
        $this->values['getToken'] = Tools::getvalue('getToken');
        $this->values['credit_card'] = Tools::getvalue('credit_card');
        $this->values['short_cut'] = 0;
        $this->setMethod(AbstractMethodPaypal::load($this->getMethodType(Tools::getAllValues())));
        $this->values['apmMethod'] = Tools::getValue('apmMethod', null);
    }

    /**
     * @see FrontController::postProcess()
     */
    public function postProcess()
    {
        try {
            $this->method->setParameters($this->values);

            if (empty($this->values['apmMethod'])) {
                $this->redirectUrl = $this->method->init()->getApproveLink();
            } else {
                $this->redirectUrl = $this->method->initApm($this->values['apmMethod'])->getPayerActionLink();
            }
        } catch (PaypalAddons\classes\PaypalException $e) {
            $this->_errors['error_code'] = $e->getCode();
            $this->_errors['error_msg'] = $e->getMessage();
            $this->_errors['msg_long'] = $e->getMessageLong();
        } catch (Exception $e) {
            $this->_errors['error_code'] = $e->getCode();
            $this->_errors['error_msg'] = $e->getMessage();
        }

        if (!empty($this->_errors)) {
            if ($this->values['getToken']) {
                $this->jsonValues = ['success' => false, 'redirect_link' => Context::getContext()->link->getModuleLink($this->name, 'error', $this->_errors)];
            } else {
                $this->redirectUrl = Context::getContext()->link->getModuleLink($this->name, 'error', $this->_errors);
            }
        }
    }

    public function setMethod($method)
    {
        $this->method = $method;
    }

    protected function getMethodType($requestData)
    {
        if (empty($requestData['methodType'])) {
            return 'EC';
        } else {
            return $requestData['methodType'];
        }
    }
}
