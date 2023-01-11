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
 *
 */

namespace PaypalPPBTlib;
use \ModuleFrontController;
use Symfony\Component\HttpFoundation\JsonResponse;
use PaypalPPBTlib\Extensions\ProcessLogger\ProcessLoggerHandler;

abstract class CommonAbstarctModuleFrontController extends ModuleFrontController
{
    /** @var string module name */
    public $name = 'paypal';

    /** @var  array Contain ajax response. */
    public $jsonValues;

    /** @var  array  POST and GET values defined in init function */
    public $values;

    /** @var  string Contain redirect URL.. */
    public $redirectUrl;

    /** @var  array An array of error information : error_msg, error_code, msg_long. */
    public $_errors;

    /** @var  array An array of transaction information : method, currency, transaction_id, payment_status, payment_method, id_payment, capture, payment_tool, date_transaction. */
    public $transaction_detail = array();

    /**
     * @see ModuleFrontController::run
     */
    public function run()
    {
        $this->init();
        if ($this->checkAccess()) {
            if ($this->ajax) {
                $this->ajaxProcess();
            } else {
                $this->postProcess();
            }
        }

        if (empty($this->_errors) == false) {
            $message = '';
            if (isset($this->_errors['error_code'])) {
                $message .= 'Error code: ' . $this->_errors['error_code'] . ';';
            }

            if (isset($this->_errors['logger_msg'])) {
                $message .= 'Short message: ' . $this->_errors['logger_msg'] . ';';
            } else {
                if (isset($this->_errors['error_msg']) && $this->_errors['error_msg']) {
                    $message .= 'Short message: ' . $this->_errors['error_msg'] . ';';
                }
                if (isset($this->_errors['msg_long']) && $this->_errors['msg_long']) {
                    $message .= 'Long message: ' . $this->_errors['msg_long'] . ';';
                }
            }

            ProcessLoggerHandler::openLogger();
            ProcessLoggerHandler::logError(
                $message,
                isset($this->transaction_detail['transaction_id']) ? $this->transaction_detail['transaction_id'] : null,
                null,
                \Context::getContext()->cart->id,
                \Context::getContext()->shop->id,
                isset($this->transaction_detail['payment_tool']) && $this->transaction_detail['payment_tool']  ? $this->transaction_detail['payment_tool'] : 'Paypal',
                (int)\Configuration::get('PAYPAL_SANDBOX'),
                isset($this->transaction_detail['date_transaction']) ? $this->transaction_detail['date_transaction'] : null
            );
            ProcessLoggerHandler::closeLogger();
        }

        if (!empty($this->redirectUrl)) {
            $this->redirectWithNotifications($this->redirectUrl);
        }
        if (!empty($this->jsonValues)) {
            $response = new JsonResponse($this->jsonValues);
            return $response->send();
        }
    }

    public function init()
    {
        parent::init();

        if (\Tools::getValue('ajax')) {
            $this->ajax = true;
        } else {
            $this->ajax = false;
        }
    }

    public function ajaxProcess()
    {
        if (\Tools::isSubmit('action') == false) {
            return false;
        }

        $method = 'displayAjax' . \Tools::getValue('action');

        if (method_exists($this, $method)) {
            return call_user_func(array($this, $method));
        }
    }
}
