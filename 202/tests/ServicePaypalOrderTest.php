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

namespace PayPalTest;

use Order;
use PaypalAddons\services\ServicePaypalOrder;
use PaypalCapture;
use PaypalLog;
use PaypalOrder;

require_once dirname(__FILE__) . '/TotTestCase.php';
require_once _PS_MODULE_DIR_.'paypal/vendor/autoload.php';


class ServicePaypalOrderTest extends \TotTestCase
{
    /** @var ServicePaypalOrder*/
    protected $servicePaypalOrder;

    protected function setUp()
    {
        parent::setUp();
        $this->servicePaypalOrder = new ServicePaypalOrder();
    }

    public function testSetOrderStatus()
    {
        $serviceMock = $this
            ->getMockBuilder(ServicePaypalOrder::class)
            ->setMethods(array('getPsOrders'))
            ->getMock();

        $serviceMock->method('getPsOrders')->willReturn(array(new Order(1)));
        $paypalOrder = new PaypalOrder();
        $this->assertTrue(is_bool($serviceMock->setOrderStatus($paypalOrder, 1)));
    }

    public function testGetCapture()
    {
        $paypalOrder = new PaypalOrder();
        $paypalOrder->id = 1;
        $capture = $this->servicePaypalOrder->getCapture($paypalOrder);
        $this->assertTrue($capture instanceof PaypalCapture || $capture === false);
    }

    public function testGetPaypalOrderByTransaction()
    {
        $paypalOrder = $this->servicePaypalOrder->getPaypalOrderByTransaction('test');
        $this->assertTrue(false === $paypalOrder);
    }

    public function testGetPsOrders()
    {
        $paypalOrder = new PaypalOrder();
        $paypalOrder->id_cart = 1;
        $psOrders = $this->servicePaypalOrder->getPsOrders($paypalOrder);
        $this->assertTrue(is_array($psOrders));
    }
}
