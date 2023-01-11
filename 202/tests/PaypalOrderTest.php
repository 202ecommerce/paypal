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

require_once dirname(__FILE__) . '/TotTestCase.php';
require_once _PS_MODULE_DIR_.'paypal/vendor/autoload.php';
require_once _PS_MODULE_DIR_.'paypal/classes/PaypalOrder.php';

class PaypalOrderTest extends \TotTestCase
{
    protected function setUp()
    {
        parent::setUp();
    }

    /**
     * @dataProvider getDataForLoadByOrderId
     */
    public function testLoadByOrderId($id_order)
    {
        $paypalOrder = \PaypalOrder::loadByOrderId($id_order);
        $this->assertInstanceOf(\PaypalOrder::class, $paypalOrder);
    }

    /**
     * @dataProvider getDataForGetIdOrderByTransactionId
     */
    public function testGetIdOrderByTransactionId($id_transaction)
    {
        $id_order = \PaypalOrder::getIdOrderByTransactionId($id_transaction);
        $this->assertTrue(is_int($id_order));
    }

    /**
     * @dataProvider getDataForGetOrderById
     */
    public function testGetOrderById($id_order)
    {
        $paypalOrder = \PaypalOrder::getOrderById($id_order);
        $this->assertTrue(is_array($paypalOrder));
    }

    public function testGetPaypalBtOrdersIds()
    {
        $orderBtIds = \PaypalOrder::getPaypalBtOrdersIds();
        $this->assertTrue(is_array($orderBtIds));
    }

    public function getDataForLoadByOrderId()
    {
        $data = array(
            array(1),
            array(0),
            array('string'),
            array(00),
            array(null),
        );
        return $data;
    }

    public function getDataForGetIdOrderByTransactionId()
    {
        $data = $this->getDataForLoadByOrderId();
        return $data;
    }

    public function getDataForGetOrderById()
    {
        $data = $this->getDataForLoadByOrderId();
        return $data;
    }
}
