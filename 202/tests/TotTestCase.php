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

use PHPUnit\Framework\TestCase;

require_once _PS_MODULE_DIR_.'paypal/vendor/autoload.php';
require_once _PS_MODULE_DIR_.'paypal/classes/PaypalOrder.php';
require_once _PS_MODULE_DIR_.'paypal/classes/PaypalCapture.php';
require_once _PS_MODULE_DIR_.'paypal/classes/PaypalLog.php';
require_once _PS_MODULE_DIR_.'paypal/paypal.php';


class TotTestCase extends TestCase
{
    protected function setUp()
    {
        if (\Module::isInstalled('paypal') == false) {
            $module = \Module::getInstanceByName('paypal');
            $module->install();

            $this->createOrderEC();
            $this->createOrderPPP();
            $this->createLog();
            $this->setConfiguration();
        }

        $testContext = \Context::getContext();
        $testContext->cart = new \Cart(1);
        \Context::setInstanceForTesting($testContext);
    }

    protected function createOrderEC()
    {
        $paypalOrder = new \PaypalOrder();
        $paypalOrder->id = 1;
        $paypalOrder->sandbox = true;
        $paypalOrder->id_order = 1;
        $paypalOrder->method = 'EC';
        $paypalOrder->id_transaction = 'id_transaction';
        $paypalOrder->id_cart = 1;
        $paypalOrder->add();

        $paypalCapture = new \PaypalCapture();

        $paypalCapture->id_paypal_order = 1;
        $paypalCapture->save();
    }

    protected function createOrderPPP()
    {
        $paypalOrder = new \PaypalOrder();
        $paypalOrder->sandbox = true;
        $paypalOrder->id_order = 2;
        $paypalOrder->method = 'PPP';
        $paypalOrder->id_transaction = 'id_transaction';
        $paypalOrder->id_cart = 2;
        $paypalOrder->save();
    }

    protected function createLog()
    {
        $paypalLog = new \PaypalLog();
        $paypalLog->id_cart =1;
        $paypalLog->id_transaction = 'id_transaction';
        $paypalLog->id_order = 1;
        $paypalLog->sandbox = true;
        $paypalLog->date_add = date("Y-m-d H:i:s");
        $paypalLog->date_transaction = date("Y-m-d H:i:s");
        $paypalLog->log = 'message';
        $paypalLog->save();
    }

    protected function setConfiguration()
    {
        \Configuration::updateValue('PAYPAL_SANDBOX', 1);
        \Configuration::updateValue('PAYPAL_USERNAME_SANDBOX', 'claloum-facilitator-1_api1.202-ecommerce.com');
        \Configuration::updateValue('PAYPAL_PSWD_SANDBOX', 'YD6V7EBRZHK89PVM');
        \Configuration::updateValue('PAYPAL_SIGNATURE_SANDBOX', 'AFcWxV21C7fd0v3bYYYRCpSSRl31Aea4kIVlIfIvPh7w5ycxBXM.KcaL');
        \Configuration::updateValue('PAYPAL_SANDBOX_CLIENTID', 'ASY0PPD9m_iTYj3qVRYuUI484zJmxV9KGVHksm2Eqvp4w3J8cpWGXkwfHP0fqTSZI10o147UsgFEMkSd');
        \Configuration::updateValue('PAYPAL_SANDBOX_SECRET', 'EMfCLrLpZ6cN8j1vQ4OyFKVkk_anFcSJWxYFZZJvnTwEYNIROLpDpw4f08lj5YAmsn21MVrywRzbhu6n');
        \Configuration::updateValue('PAYPAL_CONNECTION_EC_CONFIGURED', 1);
        \Configuration::updateValue('PAYPAL_CONNECTION_PPP_CONFIGURED', 1);
    }
}
