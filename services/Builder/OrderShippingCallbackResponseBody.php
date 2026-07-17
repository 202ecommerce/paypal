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

namespace PaypalAddons\services\Builder;

if (!defined('_PS_VERSION_')) {
    exit;
}

use PaypalAddons\classes\AbstractMethodPaypal;

/**
 * Builds the JSON body {@see \PaypalOrdershippingcallbackModuleFrontController} responds with
 * for a `SHIPPING_ADDRESS`/`SHIPPING_OPTIONS` callback: which carrier is selected (matching the
 * buyer's choice if still eligible, otherwise the cheapest one) and the re-priced amount/options.
 */
class OrderShippingCallbackResponseBody implements BuilderInterface
{
    /** @var \Cart */
    protected $cart;

    /** @var AbstractMethodPaypal */
    protected $method;

    /** @var array List of carriers eligible for the buyer's address, as returned by Carrier::getCarriersForOrder() */
    protected $carriers;

    /** @var array Decoded request body */
    protected $requestData;

    /** @var array|null The carrier applied to the response/cart, or null if none are eligible */
    protected $selectedCarrier;

    public function __construct(\Cart $cart, AbstractMethodPaypal $method, array $requestData)
    {
        $this->cart = $cart;
        $this->method = $method;
        $this->requestData = $requestData;
        $this->carriers = $this->getEligibleCarriers();
        $this->selectedCarrier = $this->pickCarrier();
    }

    /**
     * @return array|null The carrier applied to the response/cart - keys as returned by
     *                    Carrier::getCarriersForOrder() - or null if none are eligible for
     *                    the buyer's address (caller should respond 422 in that case)
     */
    public function getSelectedCarrier()
    {
        return $this->selectedCarrier;
    }

    /**
     * @return array List of carriers eligible for the buyer's address, as returned by Carrier::getCarriersForOrder()
     */
    protected function getEligibleCarriers()
    {
        $countryIsoCode = isset($this->requestData['shipping_address']['country_code']) ? $this->requestData['shipping_address']['country_code'] : '';

        if (\Validate::isLanguageIsoCode($countryIsoCode) === false) {
            return [];
        }

        $idCountry = \Country::getByIso($countryIsoCode);

        if (empty($idCountry)) {
            return [];
        }

        $idZone = (int) \Country::getIdZone($idCountry);
        $groups = $this->cart->id_customer ? (new \Customer((int) $this->cart->id_customer))->getGroups() : null;
        $error = [];

        return \Carrier::getCarriersForOrder($idZone, $groups, $this->cart, $error);
    }

    public function build()
    {
        $amount = $this->getOrderCreateBody()->getAmountForCarrier(
            (float) $this->selectedCarrier['price'],
            (float) $this->selectedCarrier['price_tax_exc']
        );

        return [
            'id' => $this->requestData['id'],
            'purchase_units' => [
                [
                    'reference_id' => $this->getReferenceId(),
                    'amount' => $amount,
                    'shipping_options' => $this->buildShippingOptions($amount['currency_code']),
                ],
            ],
        ];
    }

    /**
     * @return array The requested carrier if still eligible, otherwise the cheapest eligible one
     */
    protected function pickCarrier()
    {
        $requestedCarrierId = isset($this->requestData['shipping_option']['id']) ? (int) $this->requestData['shipping_option']['id'] : 0;

        if ($requestedCarrierId) {
            foreach ($this->carriers as $carrier) {
                if ((int) $carrier['id_carrier'] === $requestedCarrierId) {
                    return $carrier;
                }
            }
        }

        $cheapest = null;

        foreach ($this->carriers as $carrier) {
            if ($cheapest === null || (float) $carrier['price'] < (float) $cheapest['price']) {
                $cheapest = $carrier;
            }
        }

        return $cheapest;
    }

    /**
     * @return string
     */
    protected function getReferenceId()
    {
        if (empty($this->requestData['purchase_units'][0]['reference_id'])) {
            return 'default';
        }

        return $this->requestData['purchase_units'][0]['reference_id'];
    }

    /**
     * @param string $currency
     *
     * @return array
     */
    protected function buildShippingOptions($currency)
    {
        $selectedCarrierId = (int) $this->selectedCarrier['id_carrier'];
        $options = [];

        foreach ($this->carriers as $carrier) {
            $options[] = [
                'id' => (string) $carrier['id_carrier'],
                'label' => $carrier['name'],
                'type' => 'SHIPPING',
                'selected' => (int) $carrier['id_carrier'] === $selectedCarrierId,
                'amount' => [
                    'currency_code' => $currency,
                    'value' => $this->method->formatPrice($carrier['price']),
                ],
            ];
        }

        return $options;
    }

    /**
     * @return OrderCreateBody
     */
    protected function getOrderCreateBody()
    {
        $context = clone \Context::getContext();
        $context->cart = $this->cart;

        return new OrderCreateBody($context, $this->method);
    }
}
