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

namespace PaypalAddons\classes\API\Request;

use Exception;
use PayPal;
use PaypalAddons\classes\API\ExtensionSDK\GetSellerStatus;
use PaypalAddons\classes\API\Response\Error;
use PaypalAddons\services\Core\PaypalMerchantId;
use Throwable;
use Tools;

class PaypalGetSellerStatusRequest extends RequestAbstract
{
    public function execute()
    {
        $response = $this->getResponse();
        $getSellerStatus = new GetSellerStatus($this->getPartnerMerchantId(), $this->getSellerMerchantId());
        $getSellerStatus->headers = array_merge($getSellerStatus->headers, $this->getHeaders());

        try {
            $exec = $this->client->execute($getSellerStatus);
        } catch (Throwable $e) {
            $error = new Error();
            $error->setMessage($e->getMessage())
                ->setErrorCode($e->getCode());

            return $response->setSuccess(false)->setError($error);
        } catch (Exception $e) {
            $error = new Error();
            $error->setMessage($e->getMessage())
                ->setErrorCode($e->getCode());

            return $response->setSuccess(false)->setError($error);
        }

        $response->setSuccess(true);
        $response->setCapabilities($this->getCapabilities($exec));
        $response->setProducts($this->getProducts($exec));
        $response->setData($exec);

        return $response;
    }

    /** @return ResponsePartnerReferrals*/
    protected function getResponse()
    {
        return new \PaypalAddons\classes\API\Response\ResponseGetSellerStatus();
    }

    protected function getPartnerMerchantId()
    {
        if ($this->method->isSandbox()) {
            return PayPal::PAYPAL_PARTNER_ID_SANDBOX;
        } else {
            return PayPal::PAYPAL_PARTNER_ID_LIVE;
        }
    }

    protected function getSellerMerchantId()
    {
        return $this->initPaypalMerchantId()->get();
    }

    protected function initPaypalMerchantId()
    {
        return new PaypalMerchantId();
    }

    protected function getCapabilities(\PayPalHttp\HttpResponse $data)
    {
        $capabilities = [];

        if (empty($data->result->capabilities)) {
            return $capabilities;
        }

        foreach ($data->result->capabilities as $capability) {
            if (empty($capability->name)) {
                continue;
            }

            if (empty($capability->status)) {
                continue;
            }

            if (Tools::strtoupper($capability->status) != 'ACTIVE') {
                continue;
            }

            $capabilities[] = $capability->name;
        }

        return $capabilities;
    }

    protected function getProducts(\PayPalHttp\HttpResponse $data)
    {
        $products = [];

        if (empty($data->result->products)) {
            return $products;
        }

        foreach ($data->result->products as $product) {
            if (empty($product->name)) {
                continue;
            }

            $products[] = $product->name;
        }

        return $products;
    }
}
