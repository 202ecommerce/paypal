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

use PaypalAddons\classes\API\PaypalApiManager;
use PaypalAddons\classes\API\Request\PaypalAccessTokenRequest;
use PaypalAddons\classes\API\Request\V_1\CreateProfileExperienceRequest;
use PaypalAddons\classes\API\Response\Error;
use PaypalAddons\classes\API\Response\PaypalResponseAccessToken;
use \Context;
use \Currency;
use PaypalAddons\classes\API\Response\ResponseCreateProfileExperience;

require_once dirname(__FILE__) . '/TotTestCase.php';
require_once _PS_MODULE_DIR_.'paypal/vendor/autoload.php';
require_once _PS_MODULE_DIR_.'paypal/classes/MethodMB.php';

class MethodMBTest extends \TotTestCase
{
    /* @var \MethodEC*/
    protected $method;

    protected function setUp()
    {
        parent::setUp();

        $this->method = new \MethodMB();
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

    public function testIsConfigured()
    {
        $this->assertTrue(is_bool($this->method->isConfigured()));
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

        $requestMock = $this->getMockBuilder(CreateProfileExperienceRequest::class)
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

    public function testGetLandingPage()
    {
        $this->assertTrue(is_string($this->method->getLandingPage()));
    }

    public function testGetOrderStatus()
    {
        $this->assertTrue(is_int($this->method->getOrderStatus()));
    }

    public function testGetPayerId()
    {
        $this->assertTrue(is_string($this->method->getPayerId()));
    }

    public function testGetPayerTaxInfo()
    {
        $this->assertTrue(is_array($this->method->getPayerTaxInfo()));
    }

    public function testGetPaymentId()
    {
        $this->assertTrue(is_string($this->method->getPaymentId()));
    }

    public function testGetPaypalPartnerId()
    {
        $this->assertTrue(is_string($this->method->getPaypalPartnerId()));
    }

    public function testGetRememberedCards()
    {
        $this->assertTrue(is_string($this->method->getRememberedCards()));
    }


    public function testGetReturnUrl()
    {
        $this->assertTrue(is_string($this->method->getReturnUrl()));
    }

    public function testGetSecret()
    {
        $this->assertTrue(is_string($this->method->getSecret()));
    }

    /**
     * @dataProvider  getDataForGetTplVars
     */
    public function testGetTplVars($sandbox)
    {
        $mockMethod = $this->getMockBuilder(\MethodMB::class)
            ->setMethods(array('isSandbox'))
            ->getMock();

        $mockMethod->method('isSandbox')->willReturn($sandbox);

        $this->assertTrue(is_array($mockMethod->getTplVars()));
    }

    public function testLogOut()
    {
        $this->method->logOut();
        $this->assertFalse($this->method->isConfigured());
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
     * @dataProvider getDataForGetRememberedCards
     */
    public function testSetRememberedCards($value)
    {
        $this->method->setRememberedCards($value);
        $this->assertTrue(is_string($this->method->getRememberedCards()));
    }

    /**
     * @dataProvider getDataForGetTaxIdType
     */
    public function testGetTaxIdType($vartNumber, $type)
    {
        $taxIdType = $this->method->getTaxIdType($vartNumber);
        $this->assertTrue($taxIdType == $type);
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
            array($context, 'EC', 'order'),
            array($context, 'PPP', 'order'),
            array($context, 'string', 'string'),
            array($context, null, null)
        );
        return $data;
    }

    public function getDataForCheckCredentials()
    {
        $responseSuccess = new ResponseCreateProfileExperience();
        $responseFailed = new ResponseCreateProfileExperience();
        $error = new Error();
        $responseFailed->setSuccess(false)->setError($error->setMessage('error message'));
        $responseSuccess->setSuccess(true)->setIdProfileExperience('IdProfileExperience');


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

    public function getDataForGetTaxIdType()
    {
        $data = array(
            array('123456', \MethodMB::BR_CNPJ),
            array('12345678912', \MethodMB::BR_CPF),
        );

        return $data;
    }

    public function getDataForGetTplVars()
    {
        $data = array(
            array(true),
            array(false)
        );

        return $data;
    }

    public function getDataForGetRememberedCards()
    {
        $data = array(
            array(12334),
            array('1234'),
            array(null),
            array(new \stdClass())
        );

        return $data;
    }
}
