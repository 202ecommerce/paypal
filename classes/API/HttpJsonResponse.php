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

namespace PaypalAddons\classes\API;

if (!defined('_PS_VERSION_')) {
    exit;
}

class HttpJsonResponse extends HttpResponse
{
    protected $response;

    public function __construct(HttpResponse $response)
    {
        $this->response = $response;
    }

    public function getHeaders()
    {
        return $this->response->getHeaders();
    }

    public function setHeaders(array $headers)
    {
        return $this->response->setHeaders($headers);
    }

    public function getCode()
    {
        return $this->response->getCode();
    }

    public function setCode($code)
    {
        return $this->response->setCode($code);
    }

    public function getContent()
    {
        return $this->response->getContent();
    }

    public function toArray()
    {
        $result = json_decode($this->getContent(), true);

        if (is_array($result)) {
            return $result;
        }

        return [];
    }
}
