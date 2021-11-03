<?php
/**
 * 2007-2021 PayPal
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
 *  @author 2007-2021 PayPal
 *  @author 202 ecommerce <tech@202-ecommerce.com>
 *  @copyright PayPal
 *  @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace PayPalTest;

require_once dirname(__FILE__) . '/TotTestCase.php';
require_once _PS_MODULE_DIR_.'paypal/vendor/autoload.php';
require_once _PS_MODULE_DIR_.'paypal/paypal.php';

class PaypalTest extends \TotTestCase
{
    public $moduleManagerBuilder;

    public $moduleManager;

    public $moduleNames;

    /* @var \PayPal*/
    protected $paypal;

    protected function setUp()
    {
        parent::setUp();
        $this->paypal = new \PayPal();
    }

    public function testGetDecimal()
    {
        $this->assertTrue(is_int(\PayPal::getDecimal()));
    }

    public function testGetPaymentCurrencyIso()
    {
        $this->assertTrue(is_string($this->paypal->getPaymentCurrencyIso()));
    }

    public function testGetUrl()
    {
        $this->assertTrue(is_string($this->paypal->getUrl()));
    }

    public function testNeedConvert()
    {
        $needConvert = $this->paypal->needConvert();
        $this->assertTrue(is_bool($needConvert) || is_int($needConvert));
    }

    public function testShowWarningForUserBraintree()
    {
        $this->assertTrue(is_bool($this->paypal->showWarningForUserBraintree()));
    }

    public function testGetHooksUnregistered()
    {
        $this->assertInternalType('array', $this->paypal->getHooksUnregistered());
    }
}
