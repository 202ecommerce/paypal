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

namespace PaypalPPBTlib;

use Exception;
use PaypalOrder;

abstract class AbstractMethod
{
    /** @var string module name */
    public $name = 'paypal';

    // Force les classes filles à définir cette méthode

    /** @var string payment method */
    protected $payment_method;

    /** @var  array An array of transaction information : method, currency, transaction_id, payment_status, payment_method, id_payment, capture, payment_tool, date_transaction. */
    protected $transaction_detail = array();

    /** @return string*/
    protected function getPaymentMethod()
    {
        if ((int)\Configuration::get('PAYPAL_SANDBOX')) {
            return $this->payment_method . ' - SANDBOX';
        } else {
            return $this->payment_method;
        }
    }

    abstract public function init();

    abstract public function validation();

    abstract public function confirmCapture($orderPayPal);

    abstract public function refund($orderPaypal);

    abstract public function setConfig($params);

    abstract public function getConfig(\PayPal $module);

    abstract public function void($orderPaypal);

    abstract public function partialRefund($params);

    public static function load($method)
    {
        if (preg_match('/^[a-zA-Z0-9_-]+$/', $method) && file_exists(_PS_MODULE_DIR_.'paypal/classes/Method'.$method.'.php')) {
            include_once _PS_MODULE_DIR_.'paypal/classes/Method'.$method.'.php';
            $method_class = 'Method'.$method;
            return new $method_class();
        }
    }

    /**
     * Get Transaction details for order
     * @return array
     */
    public function getDetailsTransaction()
    {
        return $this->transaction_detail;
    }

    abstract function setDetailsTransaction($transaction);

    abstract public function getLinkToTransaction($log);
}
