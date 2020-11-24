<?php
/**
 * 2007-2020 PayPal
 *
 *  NOTICE OF LICENSE
 *
 *  This source file is subject to the Academic Free License (AFL 3.0)
 *  that is bundled with this package in the file LICENSE.txt.
 *  It is also available through the world-wide-web at this URL:
 *  http://opensource.org/licenses/afl-3.0.php
 *  If you did not receive a copy of the license and are unable to
 *  obtain it through the world-wide-web, please send an email
 *  to license@prestashop.com so we can send you a copy immediately.
 *
 *  DISCLAIMER
 *
 *  Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 *  versions in the future. If you wish to customize PrestaShop for your
 *  needs please refer to http://www.prestashop.com for more information.
 *
 *  @author 2007-2020 PayPal
 *  @author 202 ecommerce <tech@202-ecommerce.com>
 *  @copyright PayPal
 *  @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace PaypalAddons\classes\API\Request;


use PaypalAddons\classes\AbstractMethodPaypal;
use PaypalAddons\classes\API\Request\RequestAbstract;
use PaypalAddons\classes\API\Response\Error;
use PaypalAddons\classes\API\Response\ResponseOrderCreate;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalHttp\HttpException;
use Symfony\Component\VarDumper\VarDumper;

class PaypalOrderCreateRequest extends RequestAbstract
{
    public function execute()
    {
        $response = new ResponseOrderCreate();
        $order = new OrdersCreateRequest();
        $order->body = $this->buildRequestBody();
        $order->headers = array_merge($this->getHeaders(), $order->headers);
        
        Hook::exec('actionPaypalOrderCreateRequest', array(
            'request' => &$this,
            'order' => &$order
        ));

        try {
            $exec = $this->client->execute($order);

            if (in_array($exec->statusCode, [200, 201, 202])) {
                $response->setSuccess(true)
                    ->setData($exec)
                    ->setPaymentId($exec->result->id)
                    ->setStatusCode($exec->statusCode)
                    ->setApproveLink($this->getLink('approve', $exec->result->links));
            } elseif ($exec->statusCode == 204) {
                $response->setSuccess(true);
            } else {
                $error = new Error();
                $resultDecoded = json_decode($exec->message);
                $error->setMessage($resultDecoded->message);
                $response->setSuccess(false)
                    ->setError($error);
            }
        } catch (HttpException $e) {
            $error = new Error();
            $resultDecoded = json_decode($e->getMessage());
            $error->setMessage($resultDecoded->details[0]->description)->setErrorCode($e->getCode());
            $response->setSuccess(false)
                ->setError($error);
        } catch (\Exception $e) {
            $error = new Error();
            $error->setMessage($e->getMessage())
                ->setErrorCode($e->getCode());
            $response->setSuccess(false)
                ->setError($error);
        }
        return $response;
    }

    /**
     * @param $nameLink string
     * @param $links array
     * @return string
     */
    protected function getLink($nameLink, $links)
    {
        foreach ($links as $link) {
            if ($link->rel == $nameLink) {
                return $link->href;
            }
        }

        return '';
    }

    /**
     * @return array
     */
    protected function buildRequestBody()
    {
        $currency = $this->getCurrency();
        $productItmes = $this->getProductItems($currency);
        $wrappingItems = $this->getWrappingItems($currency);
        $items = array_merge($productItmes, $wrappingItems);
        $payer = $this->getPayer();
        $shippingInfo = $this->getShippingInfo();

        $body = [
            'intent' => $this->getIntent(),
            'application_context' => $this->getApplicationContext(),
            'purchase_units' => [
                [
                    'amount' => $this->getAmount($currency),
                    'items' => $items,
                    'custom_id' => $this->getCustomId()
                ],
            ],
        ];

        if (empty($payer) == false) {
            $body['payer'] = $payer;
        }

        if (empty($shippingInfo) == false) {
            $body['purchase_units'][0]['shipping'] = $shippingInfo;
        }

        return $body;
    }

    /**
     * @return array
     */
    protected function getPayer()
    {
        $payer = [];

        if (\Validate::isLoadedObject($this->context->customer) == false) {
            return $payer;
        }

        $payer['name'] = [
            'given_name' => $this->context->customer->firstname,
            'surname' => $this->context->customer->lastname
        ];
        $payer['email'] = $this->context->customer->email;
        $payer['address'] = $this->getAddress();

        if ($this->method instanceof \MethodMB) {
            $taxInfo = $this->method->getPayerTaxInfo();

            if (empty($taxInfo) == false) {
                $payer['tax_info'] = $taxInfo;
            }
        }

        return $payer;
    }

    /**
     * @return string
     */
    protected function getCurrency()
    {
        return $this->module->getPaymentCurrencyIso();
    }

    /**
     * @param $currency string Iso code
     * @return array
     */
    protected function getProductItems($currency)
    {
        $items = [];
        $products = $this->context->cart->getProducts();

        foreach ($products as $product) {
            $item = [];
            $priceExcl = $this->method->formatPrice($product['price']);
            $priceIncl = $this->method->formatPrice($product['price_wt']);
            $productTax = $this->method->formatPrice($priceIncl - $priceExcl);

            if (isset($product['attributes']) && (empty($product['attributes']) === false)) {
                $product['name'] .= ' - '.$product['attributes'];
            }

            $item['name'] = \Tools::substr($product['name'], 0, 126);
            $item['sku'] = $product['id_product'];
            $item['unit_amount'] = [
                'currency_code' => $currency,
                'value' => $priceExcl
            ];
            $item['tax'] = [
                'currency_code' => $currency,
                'value' => $productTax
            ];
            $item['quantity'] = $product['quantity'];

            $items[] = $item;
        }

        return $items;
    }

    /**
     * @param $currency string Iso code
     * @return array
     */
    protected function getAmount($currency)
    {
        $shippingTotal = $this->method->formatPrice($this->context->cart->getTotalShippingCost());
        $productTotalExcl = $this->method->formatPrice($this->context->cart->getOrderTotal(false, \Cart::ONLY_PRODUCTS));
        $productTotalIncl = $this->method->formatPrice($this->context->cart->getOrderTotal(true, \Cart::ONLY_PRODUCTS));
        $wrappingIncl = $this->method->formatPrice($this->context->cart->getOrderTotal(true, \Cart::ONLY_WRAPPING));
        $wrappingExcl = $this->method->formatPrice($this->context->cart->getOrderTotal(false, \Cart::ONLY_WRAPPING));
        $subTotalIncl = $this->method->formatPrice($productTotalIncl + $wrappingIncl);
        $subTotalExcl = $this->method->formatPrice($productTotalExcl + $wrappingExcl);
        $subTotalTax = $this->method->formatPrice($subTotalIncl - $subTotalExcl);
        $totalOrder = $this->method->formatPrice($this->context->cart->getOrderTotal(true, \Cart::BOTH));

        // Some version of Prestashop calcul wrong a total discount if 2 "free shipping" discount are added
        $discountTotal = $this->method->formatPrice($subTotalIncl + $shippingTotal - $totalOrder);

        $amount = array(
            'currency_code' => $currency,
            'value' => $totalOrder,
            'breakdown' =>
                array(
                    'item_total' => array(
                            'currency_code' => $currency,
                            'value' => $subTotalExcl,
                        ),
                    'shipping' => array(
                            'currency_code' => $currency,
                            'value' => $shippingTotal,
                        ),
                    'tax_total' => array(
                            'currency_code' => $currency,
                            'value' => $subTotalTax,
                        ),
                    'discount' => array(
                            'currency_code' => $currency,
                            'value' => $discountTotal
                        )
                ),
        );

        return $amount;
    }

    protected function getWrappingItems($currency)
    {
        $items = [];

        if ($this->context->cart->gift && $this->context->cart->getGiftWrappingPrice()) {
            $item = [];
            $priceIncl = $this->context->cart->getGiftWrappingPrice(true);
            $priceExcl = $this->context->cart->getGiftWrappingPrice(false);
            $tax = $priceIncl - $priceExcl;

            $item['name'] = $this->module->l('Gift wrapping', get_class($this));
            $item['sku'] = $this->context->cart->id;
            $item['unit_amount'] = [
                'currency_code' => $currency,
                'value' => $this->method->formatPrice($priceExcl)
            ];
            $item['tax'] = [
                'currency_code' => $currency,
                'value' => $this->method->formatPrice($tax)
            ];
            $item['quantity'] = 1;

            $items[] = $item;
        }

        return $items;
    }

    /**
     * @return array
     */
    protected function getApplicationContext()
    {
        $applicationContext = [
            'locale' => $this->context->language->locale,
            'landing_page' => $this->method->getLandingPage(),
            'shipping_preference' => 'SET_PROVIDED_ADDRESS',
            'return_url' => $this->method->getReturnUrl(),
            'cancel_url' => $this->method->getCancelUrl(),
            'brand_name' => $this->getBrandName(),
            'user_action' => 'PAY_NOW'
        ];

        if ($this->context->cart->isVirtualCart()) {
            $applicationContext['shipping_preference'] = 'NO_SHIPPING';
        }

        if ($this->isShortcut()) {
            $applicationContext['shipping_preference'] = 'GET_FROM_FILE';
        }

        return $applicationContext;
    }

    /**
     * @return array
     */
    protected function getShippingInfo()
    {
        if ($this->context->cart->id_address_delivery == false) {
            return [];
        }
        $shippingInfo = [
            'address' => $this->getAddress()
        ];

        return $shippingInfo;
    }

    /**
     * @return array
     */
    protected function getAddress()
    {
        $address = new \Address($this->context->cart->id_address_delivery);
        $country = new \Country($address->id_country);

        $addressArray = [
            'address_line_1' => $address->address1,
            'address_line_2' => $address->address2,
            'postal_code' => $address->postcode,
            'country_code' => \Tools::strtoupper($country->iso_code),
            'admin_area_2' => $address->city,
        ];

        if ($address->id_state) {
            $state = new \State($address->id_state);
            $addressArray['admin_area_1'] = \Tools::strtoupper($state->iso_code);
        }

        return $addressArray;
    }

    /**
     * @return string
     */
    protected function getIntent()
    {
        return $this->method->getIntent();
    }

    protected function getCustomId()
    {
        return $this->method->getCustomFieldInformation($this->context->cart);
    }

    protected function getBrandName()
    {
        return $this->method->getBrandName();
    }

    /**
     * @return bool
     */
    protected function isShortcut()
    {
        if (is_callable([$this->method, 'getShortCut']) === false) {
            return false;
        }

        return (bool) $this->method->getShortCut();
    }
}
