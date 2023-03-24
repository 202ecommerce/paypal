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
 */

namespace PaypalAddons\classes\API\Request\V_1;

use Exception;
use PayPal\Api\VerifyWebhookSignature;
use PaypalAddons\classes\AbstractMethodPaypal;
use PaypalAddons\classes\API\Response\Error as PaypalError;
use PaypalAddons\classes\API\Response\Response;
use PaypalAddons\classes\Webhook\WebhookId;
use PaypalPPBTlib\Extensions\ProcessLogger\ProcessLoggerHandler;
use Throwable;

class ValidateWebhookEventRequest extends RequestAbstract
{
    /** @var array */
    protected $headers = [];

    /** @var string */
    protected $request;

    /**
     * @param AbstractMethodPaypal
     * @param array $headers
     * @param string $request
     */
    public function __construct(AbstractMethodPaypal $method, $headers, $request)
    {
        parent::__construct($method);

        $this->headers = array_change_key_case($headers, CASE_UPPER);
        $this->request = $request;
    }

    public function execute()
    {
        $response = $this->getResponse();

        try {
            $signatureVerification = new VerifyWebhookSignature();

            $signatureVerification->setAuthAlgo($this->headers['PAYPAL-AUTH-ALGO']);
            $signatureVerification->setTransmissionId($this->headers['PAYPAL-TRANSMISSION-ID']);
            $signatureVerification->setCertUrl($this->headers['PAYPAL-CERT-URL']);
            $signatureVerification->setTransmissionSig($this->headers['PAYPAL-TRANSMISSION-SIG']);
            $signatureVerification->setTransmissionTime($this->headers['PAYPAL-TRANSMISSION-TIME']);
            $signatureVerification->setRequestBody($this->request);
            $signatureVerification->setWebhookId($this->getWebhookId());
            $execute = $signatureVerification->post($this->getApiContext());

            if ($execute->getVerificationStatus() == 'SUCCESS') {
                $response->setSuccess(true);
            } else {
                $response->setSuccess(false);
            }

            $response->setData($execute);
            ProcessLoggerHandler::openLogger();
            ProcessLoggerHandler::logInfo(
                sprintf(
                    '[ValidateWebhookEventRequest::execut()] Result: %s. Correlation-Id: %s',
                    $execute->getVerificationStatus(),
                    (isset($this->headers['CORRELATION-ID']) ? $this->headers['CORRELATION-ID'] : '')
                ),
                null,
                null,
                null,
                null,
                null,
                $this->method->isSandbox()
            );
            ProcessLoggerHandler::closeLogger();
        } catch (Throwable $e) {
            $message = implode(
                '; ',
                [
                    'Message: ' . $e->getMessage(),
                    'File: ' . $e->getFile(),
                    'Line: ' . $e->getLine(),
                    'Correlation-Id: ' . (isset($this->headers['CORRELATION-ID']) ? $this->headers['CORRELATION-ID'] : ''),
                ]
            );
            ProcessLoggerHandler::openLogger();
            ProcessLoggerHandler::logError(
                '[ValidateWebhookEventRequest::execut()]: ' . $message,
                null,
                null,
                null,
                null,
                null,
                $this->method->isSandbox()
            );
            ProcessLoggerHandler::closeLogger();
            $error = new PaypalError();
            $error
                ->setMessage($message)
                ->setErrorCode($e->getCode());

            $response
                ->setSuccess(false)
                ->setError($error);
        } catch (Exception $e) {
            $message = implode(
                '; ',
                [
                    'Message: ' . $e->getMessage(),
                    'File: ' . $e->getFile(),
                    'Line: ' . $e->getLine(),
                    'Correlation-Id: ' . (isset($this->headers['CORRELATION-ID']) ? $this->headers['CORRELATION-ID'] : ''),
                ]
            );
            ProcessLoggerHandler::openLogger();
            ProcessLoggerHandler::logError(
                '[ValidateWebhookEventRequest::execut()]: ' . $message,
                null,
                null,
                null,
                null,
                null,
                $this->method->isSandbox()
            );
            ProcessLoggerHandler::closeLogger();
            $error = new PaypalError();
            $error
                ->setMessage($message)
                ->setErrorCode($e->getCode());

            $response
                ->setSuccess(false)
                ->setError($error);
        }

        return $response;
    }

    protected function getResponse()
    {
        return new Response();
    }

    protected function getWebhookId()
    {
        try {
            return (new WebhookId($this->method))->get();
        } catch (Throwable $e) {
            return '';
        } catch (Exception $e) {
            return '';
        }
    }
}
