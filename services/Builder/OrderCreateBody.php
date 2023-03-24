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

namespace PaypalAddons\services\Builder;

use Configuration;
use Context;
use Customer;
use Module;
use Paypal;
use PaypalAddons\classes\AbstractMethodPaypal;
use PaypalAddons\services\FormatterPaypal;

class OrderCreateBody implements BuilderInterface
{
    /** @var Context */
    protected $context;

    /** @var Paypal */
    protected $module;

    /** @var AbstractMethodPaypal */
    protected $method;

    /** @var FormatterPaypal */
    protected $formatter;

    protected $items = [];

    protected $wrappings = [];

    protected $products = [];

    protected $useTax = null;

    public function __construct($context = null, $method = null)
    {
        if ($context instanceof Context) {
            $this->context = $context;
        } else {
            $this->context = Context::getContext();
        }

        if ($method instanceof AbstractMethodPaypal) {
            $this->method = $method;
        } else {
            $this->method = AbstractMethodPaypal::load();
        }

        $this->module = Module::getInstanceByName('paypal');
        $this->formatter = new FormatterPaypal();
    }

    public function build()
    {
        $currency = $this->getCurrency();
        $items = $this->getItems($currency);
        $payer = $this->getPayer();
        $shippingInfo = $this->getShippingInfo();

        $body = [
            'intent' => $this->getIntent(),
            'application_context' => $this->getApplicationContext(),
            'purchase_units' => [
                [
                    'amount' => $this->getAmount($currency),
                    'items' => $items,
                    'custom_id' => $this->formatter->formatPaypalString($this->getCustomId()),
                ],
            ],
        ];

        if (empty($payer) == false) {
            $body['payer'] = $payer;
        }

        if (empty($shippingInfo) == false && $this->isShortcut() == false) {
            $body['purchase_units'][0]['shipping'] = $shippingInfo;
        }

        return $body;
    }

    /**
     * @return string
     */
    protected function getCurrency()
    {
        return $this->module->getPaymentCurrencyIso();
    }

    protected function getItems($currency, $cache = false)
    {
        if ($cache && false === empty($this->items)) {
            return $this->items;
        }

        $this->items = array_merge(
            $this->getProductItems($currency, $cache),
            $this->getWrappingItems($currency, $cache)
        );

        return $this->items;
    }

    protected function isUseTax()
    {
        if (is_null($this->useTax) == false) {
            return $this->useTax;
        }

        $this->useTax = (int) Configuration::get('PS_TAX') == 1;

        return $this->useTax;
    }

    /**
     * @param $currency string Iso code
     *
     * @return array
     */
    protected function getProductItems($currency, $cache = false)
    {
        if ($cache && false === empty($this->products)) {
            return $this->products;
        }

        $items = [];
        $products = $this->context->cart->getProducts();

        foreach ($products as $product) {
            $item = [];
            $priceExcl = $this->method->formatPrice($product['price']);
            $priceIncl = $this->method->formatPrice($product['price_wt']);

            if ($this->isUseTax()) {
                $productTax = $this->method->formatPrice($priceIncl - $priceExcl, null, false);
            } else {
                $productTax = 0;
            }

            if (isset($product['attributes']) && (empty($product['attributes']) === false)) {
                $product['name'] .= ' - ' . $product['attributes'];
            }

            if (isset($product['reference']) && false === empty($product['reference'])) {
                $product['name'] .= ' Ref: ' . $product['reference'];
            }

            $item['name'] = $this->formatter->formatPaypalString($product['name']);
            $item['sku'] = $product['id_product'];
            $item['unit_amount'] = [
                'currency_code' => $currency,
                'value' => $priceExcl,
            ];
            $item['tax'] = [
                'currency_code' => $currency,
                'value' => $productTax,
            ];
            $item['quantity'] = $product['quantity'];

            $items[] = $item;
        }

        $this->products = $items;

        return $items;
    }

    protected function getPayer()
    {
        $payer = [];
        $customer = new Customer($this->context->cart->id_customer);

        if (\Validate::isLoadedObject($customer) == false) {
            return $payer;
        }

        $payer['name'] = [
            'given_name' => $this->formatter->formatPaypalString($customer->firstname),
            'surname' => $this->formatter->formatPaypalString($customer->lastname),
        ];
        $payer['email'] = $customer->email;

        if ($this->context->cart->isVirtualCart() === false) {
            $payer['address'] = $this->getAddress();
        }

        if ($this->method instanceof \MethodMB) {
            $taxInfo = $this->method->getPayerTaxInfo();

            if (empty($taxInfo) == false) {
                $payer['tax_info'] = $taxInfo;
            }
        }

        return $payer;
    }

    protected function getAmount($currency)
    {
        $cartSummary = $this->context->cart->getSummaryDetails();
        $items = $this->getItems($currency, true);
        $subTotalExcl = 0;
        $shippingTotal = $this->method->formatPrice($this->getTotalShipping());
        $subTotalTax = 0;
        $discountTotal = $this->method->formatPrice(abs($this->getDiscount()));
        $handling = $this->getHandling($currency);

        foreach ($items as $item) {
            $subTotalExcl += (float) $item['unit_amount']['value'] * (float) $item['quantity'];
            $subTotalTax += (float) $item['tax']['value'] * (float) $item['quantity'];
        }

        $subTotalExcl = $this->method->formatPrice($subTotalExcl, null, false);
        $subTotalTax = $this->method->formatPrice($subTotalTax, null, false);
        $totalOrder = $this->method->formatPrice(
            $subTotalExcl + $subTotalTax + $shippingTotal + $handling - $discountTotal,
            null,
            false
        );

        $amount = [
            'currency_code' => $currency,
            'value' => $totalOrder,
            'breakdown' => [
                    'item_total' => [
                        'currency_code' => $currency,
                        'value' => $subTotalExcl,
                    ],
                    'shipping' => [
                        'currency_code' => $currency,
                        'value' => $shippingTotal,
                    ],
                    'tax_total' => [
                        'currency_code' => $currency,
                        'value' => $subTotalTax,
                    ],
                    'discount' => [
                        'currency_code' => $currency,
                        'value' => $discountTotal,
                    ],
                    'handling' => [
                        'currency_code' => $currency,
                        'value' => $handling,
                    ],
                ],
        ];

        return $amount;
    }

    protected function getWrappingItems($currency, $cache = false)
    {
        if ($cache && false === empty($this->wrappings)) {
            return $this->wrappings;
        }

        $items = [];

        if ($this->context->cart->gift && $this->context->cart->getGiftWrappingPrice()) {
            $item = [];
            $priceIncl = $this->context->cart->getGiftWrappingPrice(true);
            $priceExcl = $this->context->cart->getGiftWrappingPrice(false);

            if ($this->isUseTax()) {
                $tax = $priceIncl - $priceExcl;
            } else {
                $tax = 0;
            }

            $item['name'] = $this->module->l('Gift wrapping', get_class($this));
            $item['sku'] = $this->context->cart->id;
            $item['unit_amount'] = [
                'currency_code' => $currency,
                'value' => $this->method->formatPrice($priceExcl),
            ];
            $item['tax'] = [
                'currency_code' => $currency,
                'value' => $this->method->formatPrice($tax),
            ];
            $item['quantity'] = 1;

            $items[] = $item;
        }

        $this->wrappings = $items;

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
            'brand_name' => $this->formatter->formatPaypalString($this->getBrandName()),
            'user_action' => 'PAY_NOW',
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
        if ($this->context->cart->id_address_delivery == false || $this->context->cart->isVirtualCart()) {
            return [];
        }

        $shippingInfo = [
            'address' => $this->getAddress(),
        ];

        $name = $this->getShippingName();

        if (false == empty($name)) {
            $shippingInfo['name'] = $name;
        }

        return $shippingInfo;
    }

    protected function getShippingName()
    {
        if (empty($this->context->cart->id_address_delivery)) {
            return [];
        }

        $address = new \Address($this->context->cart->id_address_delivery);

        if (false == \Validate::isLoadedObject($address)) {
            return [];
        }

        return [
            'full_name' => implode(
                ' ',
                [
                    $address->firstname,
                    $address->lastname,
                ]
            ),
        ];
    }

    /**
     * @return array
     */
    protected function getAddress($address = null)
    {
        if (!$address) {
            $address = new \Address($this->context->cart->id_address_delivery);
        }

        $country = new \Country($address->id_country);

        $addressArray = [
            'address_line_1' => $this->formatter->formatPaypalString($address->address1),
            'address_line_2' => $this->formatter->formatPaypalString($address->address2),
            'postal_code' => $address->postcode,
            'country_code' => \Tools::strtoupper($country->iso_code),
            'admin_area_2' => $this->formatter->formatPaypalString($address->city),
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

    protected function getHandling($currency)
    {
        $handling = 0;
        $discounts = $this->context->cart->getCartRules();

        if (empty($discounts)) {
            return $handling;
        }

        foreach ($discounts as $discount) {
            if ($discount['value_real'] < 0) {
                $handling += $this->method->formatPrice(abs($discount['value_real']));
            }
        }

        return $this->method->formatPrice($handling);
    }

    /**
     * @return float
     */
    protected function getDiscount()
    {
        $discountTotal = $this->context->cart->getOrderTotal($this->isUseTax(), \Cart::ONLY_DISCOUNTS);

        if (version_compare(_PS_VERSION_, '1.7.5', '>=') && version_compare(_PS_VERSION_, '1.7.6', '<')) {
            $summaryDetails = $this->context->cart->getSummaryDetails();
            $gifts = isset($summaryDetails['gift_products']) ? $summaryDetails['gift_products'] : [];

            if (is_array($gifts)) {
                foreach ($gifts as $gift) {
                    if (isset($gift['price_with_reduction'])) {
                        $discountTotal += $gift['price_with_reduction'];
                    }
                }
            }
        }

        return $discountTotal;
    }

    protected function getTotalShipping()
    {
        return $this->context->cart->getOrderTotal($this->isUseTax(), \Cart::ONLY_SHIPPING);
    }
}
