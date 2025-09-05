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

namespace PaypalAddons\classes\API\Client;

if (!defined('_PS_VERSION_')) {
    exit;
}

use PaypalAddons\classes\API\ClientInterface;
use PaypalAddons\classes\API\EnvironmentInterface;
use PaypalAddons\classes\API\HttpResponse;
use PaypalAddons\classes\API\InjectorInterface;
use PaypalAddons\classes\API\Request\HttpRequestInterface;
use PaypalAddons\classes\API\WrapperInterface;

class HttpClient implements ClientInterface
{
    /** @var EnvironmentInterface */
    protected $environment;
    /** @var array */
    protected $injectors = [];

    public function __construct(EnvironmentInterface $environment)
    {
        $this->setEnvironment($environment);
    }

    /** @return self*/
    public function addInjector(InjectorInterface $injector)
    {
        $this->injectors[] = $injector;

        return $this;
    }

    /** @return self*/
    public function setEnvironment(EnvironmentInterface $environment)
    {
        $this->environment = $environment;

        return $this;
    }

    /**
     * @return HttpResponse
     */
    public function execute(HttpRequestInterface $request)
    {
        /*
         * @var HttpRequestInterface $request
         */
        foreach ($this->injectors as $injector) {
            /* @var InjectorInterface $injector */
            $injector->inject($request);
        }

        $ch = curl_init();
        curl_setopt_array($ch, $this->getOptions($request));
        $response = $this->makeCall($ch);
        curl_close($ch);

        if ($request instanceof WrapperInterface) {
            $response = $request->wrap($response);
        }

        return $response;
    }

    /** @return array*/
    protected function getOptions(HttpRequestInterface $request)
    {
        $options = [];

        $options[CURLOPT_URL] = $this->environment->getBaseUri() . $request->getPath();
        $options[CURLOPT_HTTPHEADER] = $this->serializeHeaders($request->getHeaders());
        $options[CURLOPT_RETURNTRANSFER] = true;
        $options[CURLOPT_HEADER] = false;
        $options[CURLOPT_SSLVERSION] = CURL_SSLVERSION_TLSv1_2;
        $options[CURLOPT_CONNECTTIMEOUT] = 0;
        $options[CURLOPT_TIMEOUT] = 10;

        if (false === empty($request->getBody())) {
            $options[CURLOPT_POSTFIELDS] = $request->getBody();
        }

        if ($request->getMethod() === 'HEAD') {
            $options[CURLOPT_NOBODY] = true;
        } elseif ($request->getMethod() !== 'GET') {
            $options[CURLOPT_CUSTOMREQUEST] = $request->getMethod();
        }

        return $options;
    }

    /** @return array*/
    protected function serializeHeaders($headers)
    {
        $headerArray = [];

        if ($headers) {
            foreach ($headers as $key => $val) {
                $headerArray[] = $key . ': ' . $val;
            }
        }

        return $headerArray;
    }

    /**
     * @param string $header
     *
     * @return array
     */
    protected function deserializeHeader($header)
    {
        if (empty($header)) {
            return [];
        }

        if (strpos($header, ':') === false) {
            return [];
        }

        list($key, $value) = explode(':', $header);
        $key = trim($key);
        $value = trim($value);

        return [$key => $value];
    }

    /** @return HttpResponse*/
    protected function makeCall($ch)
    {
        $response = new HttpResponse();
        $headers = [];
        curl_setopt($ch, CURLOPT_HEADERFUNCTION, function ($ch, $header) use (&$headers) {
            $len = strlen($header);
            $header = $this->deserializeHeader($header);
            $headers = array_merge($headers, $header);

            return $len;
        });
        $data = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $errorCode = curl_errno($ch);
        $errorMsg = curl_error($ch);

        if ($errorCode) {
            $response->setContent([
                'errorCode' => $errorCode,
                'errorMessage' => $errorMsg,
            ]);

            return $response;
        }

        $response->setContent($data);
        $response->setCode($code);
        $response->setHeaders($headers);

        return $response;
    }
}
