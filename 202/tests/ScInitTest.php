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

use PaypalAddons\classes\API\Response\ResponseOrderCreate;

require_once dirname(__FILE__) . '/TotTestCase.php';
require_once _PS_MODULE_DIR_.'paypal/vendor/autoload.php';
require_once _PS_MODULE_DIR_.'paypal/controllers/front/ScInit.php';
require_once _PS_MODULE_DIR_.'paypal/classes/MethodEC.php';

class ScInitTest extends \TotTestCase
{
    protected function setUp()
    {
        parent::setUp();
    }

    /**
     * @dataProvider providerCheckAvailability
     */
    public function testCheckAvailability($values, $methodsCart, $return)
    {
        $cartMock = $this->createMock(\Cart::class);
        $scInitMock = $this->getMockBuilder(\PaypalScInitModuleFrontController::class)
            ->setMethods(['getRequest'])
            ->getMock();

        if (empty($methodsCart) == false) {
            foreach ($methodsCart as $methodCart) {
                $cartMock->method($methodCart['methodName'])->willReturn($methodCart['methodReturn']);
            }
        }

        $contextTest = \Context::getContext();
        $contextTest->cart = $cartMock;
        \Context::setInstanceForTesting($contextTest);

        $scInitMock->method('getRequest')->willReturn(json_decode(json_encode($values)));
        $scInitMock->displayAjaxCheckAvailability();
        $this->assertEquals($return, $scInitMock->jsonValues['success']);
    }

    /**
     * @dataProvider providerSuccessProductRedirect
     */
    public function testCreateOrder($values)
    {
        $methodMock = $this->getMockBuilder(\MethodEC::class)
            ->setMethods(array('init', 'getPaymentId'))
            ->getMock();
        $methodMock->method('getPaymentId')->willReturn('paymentID');

        $scInitMock = $this->getMockBuilder(\PaypalScInitModuleFrontController::class)
            ->setMethods(array('getRequest'))
            ->getMock();

        $scInitMock->method('getRequest')->willReturn(json_decode(json_encode($values)));

        \Context::getContext()->customer = new \Customer(1);
        $cart = new \Cart();
        $cart->id_currency = 1;
        $cart->add();
        $cart->updateQty(1, 1);
        \Context::getContext()->cart = $cart;

        $scInitMock->setMethod($methodMock);
        $scInitMock->displayAjaxCreateOrder();

        $this->assertEmpty($scInitMock->errors);
        $this->assertTrue(is_string($scInitMock->jsonValues['idOrder']));
        $this->assertTrue(true == $scInitMock->jsonValues['success']);
    }

    public function providerCheckAvailability()
    {
        $dataProvider = array(
            'cart_1' => array(
                array('page' => 'cart'),
                array(
                    array('methodName' => 'checkQuantities', 'methodReturn' => false),
                    array('methodName' => 'hasProducts', 'methodReturn' => false),
                ),
                false
            ),
            'cart_2' => array(
                array('page' => 'cart'),
                array(
                    array('methodName' => 'checkQuantities', 'methodReturn' => true),
                    array('methodName' => 'hasProducts', 'methodReturn' => false),
                ),
                false
            ),
            'cart_3' => array(
                array('page' => 'cart'),
                array(
                    array('methodName' => 'checkQuantities', 'methodReturn' => false),
                    array('methodName' => 'hasProducts', 'methodReturn' => true),
                ),
                false
            ),
            'cart_4' => array(
                array('page' => 'cart'),
                array(
                    array('methodName' => 'checkQuantities', 'methodReturn' => true),
                    array('methodName' => 'hasProducts', 'methodReturn' => true),
                ),
                true
            ),
            'product_1' => array(
                array('page' => 'product', 'idProduct' => 1, 'id_product_attribute' => 0, 'quantity' => 999999999, 'product_attribute' => 0, 'combination' => '1:1'),
                array(),
                false
            ),
            'product_2' => array(
                array('page' => 'product', 'idProduct' => 1, 'id_product_attribute' => 0, 'quantity' => 1, 'product_attribute' => 0, 'combination' => '1:1'),
                array(),
                true
            ),
        );

        return $dataProvider;
    }

    public function providerSuccessProductRedirect()
    {
        $data = array(
            'source_cart' => array(
                array('page' => 'product', 'idProduct' => 1, 'quantity' => 1, 'combination' => '1:1'),
            ),
            'source_product' => array(
                array('page' => 'product', 'idProduct' => 1, 'quantity' => 1, 'combination' => null)
            )
        );

        return $data;
    }

    public function providerSuccessProductJson()
    {
        $data = array(
            'source_cart' => array(
                array('checkAvailability' => false, 'source_page' => 'cart', 'getToken' => true)
            ),
            'source_product' => array(
                array('checkAvailability' => false, 'source_page' => 'product', 'getToken' => true)
            )
        );

        return $data;
    }

    public function providerFailureCartRedirect()
    {
        $data = array(
            'source_cart' => array(
                array('checkAvailability' => false, 'source_page' => 'cart', 'getToken' => false)
            )
        );

        return $data;
    }

    public function providerFailureCartJson()
    {
        $data = array(
            'source_cart' => array(
                array('checkAvailability' => false, 'source_page' => 'cart', 'getToken' => true)
            )
        );

        return $data;
    }
}
