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

use Context;
use Currency;
use PayPal\Rest\ApiContext;
use PayPal\Api\CreateProfileResponse;
use PaypalAddons\classes\API\PaypalApiManager;
use PaypalAddons\classes\API\Request\PaypalAccessTokenRequest;
use PaypalAddons\classes\API\Response\Error;
use PaypalAddons\classes\API\Response\PaypalResponseAccessToken;

require_once dirname(__FILE__) . '/TotTestCase.php';
require_once _PS_MODULE_DIR_.'paypal/vendor/autoload.php';
require_once _PS_MODULE_DIR_.'paypal/classes/MethodPPP.php';

class MethodPPPTest extends \TotTestCase
{
    /* @var \MethodPPP*/
    protected $method;

    protected function setUp()
    {
        parent::setUp();
        $this->method = new \MethodPPP();
    }

    /**
     * @dataProvider getDataForCheckCredentials
     */
    public function testCheckCredentials($response, $result)
    {
        $apiManagerMock = $this->getMockBuilder(PaypalApiManager::class)
            ->setMethods(array('getAccessTokenRequest'))
            ->disableOriginalConstructor()
            ->getMock();

        $requestMock = $this->getMockBuilder(PaypalAccessTokenRequest::class)
            ->setMethods(array('execute'))
            ->disableOriginalConstructor()
            ->getMock();

        $requestMock->method('execute')->willReturn($response);

        $apiManagerMock->method('getAccessTokenRequest')->willReturn($requestMock);

        $reflection = new \ReflectionClass($this->method);
        $reflectionProperty = $reflection->getProperty('paypalApiManager');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->method, $apiManagerMock);

        $this->method->checkCredentials();
        $this->assertTrue($this->method->isConfigured() == $result);
    }

    /**
     * @dataProvider getDataForFormatPrice
     */
    public function testFormatPrice($price)
    {
        $context = Context::getContext();
        $context->currency = new Currency(1);
        Context::setInstanceForTesting($context);
        $priceFormated = $this->method->formatPrice($price);
        $this->assertTrue(is_string($priceFormated));
    }

    public function testGetAdvancedFormInputs()
    {
        $this->assertTrue(is_array($this->method->getAdvancedFormInputs()));
    }

    public function testGetCancelUrl()
    {
        $this->assertTrue(is_string($this->method->getCancelUrl()));
    }

    public function testGetClientId()
    {
        $this->assertTrue(is_string($this->method->getClientId()));
    }

    public function testGetIntent()
    {
        $this->assertTrue(is_string($this->method->getIntent()));
    }

    public function testGetOrderStatus()
    {
        $this->assertTrue(is_int($this->method->getOrderStatus()));
    }

    public function testGetPaymentId()
    {
        $this->assertTrue(is_string($this->method->getPaymentId()));
    }

    public function testGetPaypalPartnerId()
    {
        $this->assertTrue(is_string($this->method->getPaypalPartnerId()));
    }

    public function testGetReturnUrl()
    {
        $this->assertTrue(is_string($this->method->getReturnUrl()));
    }

    public function testGetSecret()
    {
        $this->assertTrue(is_string($this->method->getSecret()));
    }

    public function testGetShortCut()
    {
        $this->assertTrue(is_bool($this->method->getShortCut()));
    }

    public function testGetTplVars()
    {
        $this->assertTrue(is_array($this->method->getTplVars()));
    }

    public function testIsConfigured()
    {
        $this->assertTrue(is_bool($this->method->isConfigured()));
    }

    public function testInit()
    {
        $this->assertTrue(is_string($this->method->init()));
    }

    public function testLogOut()
    {
        $this->method->logOut();
        $this->assertFalse($this->method->isConfigured());
    }

    /**
     * @dataProvider getDataForRenderExpressCheckoutShortCut
     */
    public function testRenderExpressCheckoutShortCut($context, $type, $page_source)
    {
        $this->assertTrue(is_string($this->method->renderExpressCheckoutShortCut($context, $type, $page_source)));
    }

    /**
     * @dataProvider getDataForSetPaymentId
     */
    public function testSetPaymentId($paymentId)
    {
        $this->method->setPaymentId($paymentId);
        $this->assertTrue(is_string($this->method->getPaymentId()));
    }

    /**
     * @dataProvider getDataForSetPaymentId
     */
    public function testSetShortCut($shortCut)
    {
        $this->method->setShortCut($shortCut);
        $this->assertTrue(is_bool($this->method->getShortCut()));
    }

    public function getDataForFormatPrice()
    {
        $data = array(
            array(1),
            array(023),
            array('123'),
            array(00),
            array(null),
        );
        return $data;
    }

    public function getDataForRenderExpressCheckoutShortCut()
    {
        $context = \Context::getContext();
        $data = array(
            array($context, 'EC', 'product'),
            array($context, 'PPP', 'product'),
            array($context, 'BT', 'order'),
            array($context, 'EC', 'order'),
            array($context, 'PPP', 'order'),
            array($context, 'string', 'string'),
            array($context, null, null)
        );
        return $data;
    }

    public function getDataForCheckCredentials()
    {
        $responseSuccess = new PaypalResponseAccessToken();
        $responseFailed = new PaypalResponseAccessToken();
        $error = new Error();
        $responseFailed->setSuccess(false)->setError($error->setMessage('error message'));
        $responseSuccess->setSuccess(true);


        $data = array(
            array($responseSuccess, true),
            array($responseFailed, false)
        );

        return $data;
    }

    public function getDataForSetPaymentId()
    {
        return [
            'integer' => [1],
            'null' => [null],
            'bool' => [false],
            'array' => [['value']],
            'object' => [new \stdClass()],
            'string' => [1],
        ];
    }
}
