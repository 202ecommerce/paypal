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
require_once _PS_MODULE_DIR_.'paypal/controllers/front/ecInit.php';
require_once _PS_MODULE_DIR_.'paypal/classes/MethodEC.php';
require_once _PS_MODULE_DIR_.'paypal/paypal.php';

class EcInitTest extends \TotTestCase
{
    protected $methodMock;

    protected function setUp()
    {
        parent::setUp();
    }


    public function testPostProcessFailureRedirect()
    {
        $methodMock = $this->getMockBuilder(\MethodEC::class)
            ->setMethods(array('init'))
            ->getMock();

        $methodMock->method('init')->willThrowException(new \Exception('test exception'));

        $ecInit = new \PaypalEcInitModuleFrontController();
        $ecInit->values = array('getToken' => false);
        $ecInit->setMethod($methodMock);
        $ecInit->postProcess();
        $this->assertNotEmpty($ecInit->errors);
        $this->assertEquals(
            \Context::getContext()->link->getModuleLink($ecInit->name, 'error', $ecInit->errors),
            $ecInit->redirectUrl
        );
    }

    public function testPostProcessFailureJson()
    {
        $methodMock = $this->getMockBuilder(\MethodEC::class)
            ->setMethods(array('init'))
            ->getMock();

        $methodMock->method('init')->willThrowException(new \Exception('test exception'));

        $ecInit = new \PaypalEcInitModuleFrontController();
        $ecInit->values = array('getToken' => true);
        $ecInit->setMethod($methodMock);
        $ecInit->postProcess();
        $this->assertNotEmpty($ecInit->errors);
        $this->assertFalse($ecInit->jsonValues['success']);
        $this->assertArrayHasKey('redirect_link', $ecInit->jsonValues);
    }

    public function testPostProcessSuccessRedirect()
    {
        $methodMock = $this->getMockBuilder(\MethodEC::class)
            ->setMethods(array('init'))
            ->getMock();

        $methodMock->method('init')->willReturn(new ResponseOrderCreate());

        $ecInit = new \PaypalEcInitModuleFrontController();
        $ecInit->values = array('getToken' => false);
        $ecInit->setMethod($methodMock);
        $ecInit->postProcess();
        $this->assertEmpty($ecInit->errors);
        $this->assertTrue(is_string($ecInit->redirectUrl));
    }

    public function testPostProcessSuccessJson()
    {
        $methodMock = $this->getMockBuilder(\MethodEC::class)
            ->setMethods(array('init'))
            ->getMock();

        $methodMock->method('init')->willReturn(new ResponseOrderCreate());

        $ecInit = new \PaypalEcInitModuleFrontController();
        $ecInit->values = array('getToken' => true);
        $ecInit->setMethod($methodMock);
        $ecInit->postProcess();
        $this->assertEmpty($ecInit->errors);
        $this->assertTrue($ecInit->jsonValues['success']);
        $this->assertArrayHasKey('token', $ecInit->jsonValues);
    }
}
