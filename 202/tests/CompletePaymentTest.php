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

namespace PaypalTests;

use PaypalAddons\classes\AbstractMethodPaypal;
use PaypalAddons\classes\API\Environment\PaypalEnvironment;
use PaypalAddons\classes\API\HttpAdoptedResponse;
use PaypalAddons\classes\API\HttpResponse;
use PaypalAddons\classes\API\PaypalApiManager;
use PaypalAddons\classes\API\Response\ResponseOrderCapture;
use PaypalAddons\classes\PaypalException;
use PaypalTests\Stub\HttpClientStub;

class CompletePaymentTest extends AbstractTest
{
    /** @var string|false */
    protected $intent;

    protected function setUp(): void
    {
        $this->intent = \Configuration::get('PAYPAL_API_INTENT');
        \Configuration::updateValue('PAYPAL_API_INTENT', 'sale');
    }

    protected function tearDown(): void
    {
        \Configuration::updateValue('PAYPAL_API_INTENT', $this->intent);
    }

    public function testCaptureTimeoutWhenOrderIsCompleted()
    {
        $method = AbstractMethodPaypal::load('EC');
        $client = $this->initClient($method)
            ->addResponse($this->initHttpResponse(200, 'order-get-approved.json'))
            ->addResponse($this->initTransportErrorResponse())
            ->addResponse($this->initHttpResponse(200, 'order-get-completed.json'));

        $response = $this->completePayment($method);

        $this->assertTrue($response->isSuccess(), (string) $response->getError()->getMessage());
        $this->assertSame('test-transaction-id-524', $response->getTransactionId());
        $this->assertSame(
            [
                'GET /v2/checkout/orders/test-payment-id-524',
                'POST /v2/checkout/orders/test-payment-id-524/capture',
                'GET /v2/checkout/orders/test-payment-id-524',
            ],
            $client->getCalls()
        );
    }

    public function testCaptureTimeoutWhenOrderIsNotCompleted()
    {
        $method = AbstractMethodPaypal::load('EC');
        $client = $this->initClient($method)
            ->addResponse($this->initHttpResponse(200, 'order-get-approved.json'))
            ->addResponse($this->initTransportErrorResponse())
            ->addResponse($this->initHttpResponse(200, 'order-get-approved.json'));

        $response = $this->completePayment($method);

        $this->assertFalse($response->isSuccess());
        $this->assertSame(
            [
                'GET /v2/checkout/orders/test-payment-id-524',
                'POST /v2/checkout/orders/test-payment-id-524/capture',
                'GET /v2/checkout/orders/test-payment-id-524',
            ],
            $client->getCalls()
        );
    }

    public function testCaptureTimeoutWhenOrderGetTimesOut()
    {
        $method = AbstractMethodPaypal::load('EC');
        $this->initClient($method)
            ->addResponse($this->initHttpResponse(200, 'order-get-approved.json'))
            ->addResponse($this->initTransportErrorResponse())
            ->addResponse($this->initTransportErrorResponse());

        $response = $this->completePayment($method);

        $this->assertFalse($response->isSuccess());
    }

    public function testCapturePayerActionRequired()
    {
        $method = AbstractMethodPaypal::load('EC');
        $this->initClient($method)
            ->addResponse($this->initHttpResponse(200, 'order-get-approved.json'))
            ->addResponse($this->initHttpResponse(422, 'order-capture-payer-action-required.json'))
            ->addResponse($this->initHttpResponse(200, 'order-get-payer-action-required.json'));

        $response = $this->completePayment($method);

        $this->assertFalse($response->isSuccess());
        $this->assertSame(PaypalException::PAYER_ACTION_REQUIRED, $response->getError()->getCode());
        $this->assertSame('https://www.sandbox.paypal.com/checkoutnow?token=test-payment-id-524', $response->getPayerAction());
    }

    public function testAdoptedResponseWithTransportError()
    {
        $this->expectException(PaypalException::class);
        $this->expectExceptionMessage('Operation timed out');

        (new HttpAdoptedResponse($this->initTransportErrorResponse()))->getAdoptedResponse();
    }

    /**
     * @return HttpClientStub
     */
    protected function initClient(AbstractMethodPaypal $method)
    {
        $client = new HttpClientStub(new PaypalEnvironment($method));

        $apiManagerProperty = new \ReflectionProperty(AbstractMethodPaypal::class, 'paypalApiManager');
        $apiManagerProperty->setAccessible(true);
        $clientProperty = new \ReflectionProperty(PaypalApiManager::class, 'client');
        $clientProperty->setAccessible(true);
        $clientProperty->setValue($apiManagerProperty->getValue($method), $client);

        return $client;
    }

    /**
     * @return ResponseOrderCapture
     */
    protected function completePayment(AbstractMethodPaypal $method)
    {
        $method->setPaymentId('test-payment-id-524');
        $completePayment = new \ReflectionMethod(AbstractMethodPaypal::class, 'completePayment');
        $completePayment->setAccessible(true);

        return $completePayment->invoke($method);
    }

    /**
     * @return HttpResponse
     */
    protected function initHttpResponse($code, $file)
    {
        return (new HttpResponse())
            ->setCode($code)
            ->setContent(file_get_contents(__DIR__ . '/dataset/' . $file));
    }

    /**
     * @return HttpResponse
     */
    protected function initTransportErrorResponse()
    {
        return (new HttpResponse())->setContent([
            'errorCode' => 28,
            'errorMessage' => 'Operation timed out after 10001 milliseconds with 0 bytes received',
        ]);
    }
}
