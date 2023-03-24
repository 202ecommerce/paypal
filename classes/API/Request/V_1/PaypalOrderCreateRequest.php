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

use Address;
use Cart;
use Context;
use Country;
use Exception;
use Module;
use PayPal;
use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\PayerInfo;
use PayPal\Api\Payment;
use PayPal\Api\RedirectUrls;
use PayPal\Api\ShippingAddress;
use PayPal\Api\Transaction;
use PaypalAddons\classes\API\Response\Error;
use PaypalAddons\classes\API\Response\ResponseOrderCreate;
use State;
use Throwable;
use Tools;

class PaypalOrderCreateRequest extends RequestAbstractMB
{
    /** @var Amount */
    protected $_amount;

    /** @var ItemList */
    protected $_itemList;

    /** @var Item[] */
    protected $_items;

    /** @var float */
    protected $_itemTotalValue;

    /** @var float */
    protected $_taxTotalValue;

    /**
     * @return ResponseOrderCreate
     */
    public function execute()
    {
        $response = new ResponseOrderCreate();

        if ($this->method->isConfigured() == false) {
            $error = new Error();
            $error->setMessage('Module is not configured');

            return $response->setSuccess(false)->setError($error);
        }

        $payer = new Payer();
        $payer->setPaymentMethod('paypal');
        $payer->setPayerInfo($this->getPayerInfo());
        // ### Itemized information
        // (Optional) Lets you specify item wise information

        $this->_itemList = new ItemList();
        $this->_amount = new Amount();

        $this->_getPaymentDetails();

        if (Context::getContext()->cart->isVirtualCart() === false) {
            $this->_itemList->setShippingAddress($this->getPayerShippingAddress());
        }

        // ### Transaction
        // A transaction defines the contract of a
        // payment - what is the payment for and who
        // is fulfilling it.

        $transaction = new Transaction();
        $transaction->setAmount($this->_amount)
            ->setItemList($this->_itemList)
            ->setDescription('Payment description')
            ->setInvoiceNumber(uniqid());

        if (is_callable([get_class($this->method), 'getIpnUrl'], true)) {
            $transaction->setNotifyUrl($this->method->getIpnUrl());
        }

        // ### Redirect urls
        // Set the urls that the buyer must be redirected to after
        // payment approval/ cancellation.

        $redirectUrls = new RedirectUrls();
        $redirectUrls
            ->setReturnUrl($this->method->getReturnUrl())
            ->setCancelUrl($this->method->getCancelUrl());

        // ### Payment
        // A Payment Resource; create one using
        // the above types and intent set to 'sale'

        $payment = new Payment();
        $payment->setIntent('sale')
            ->setPayer($payer)
            ->setRedirectUrls($redirectUrls)
            ->setTransactions([$transaction]);

        // Set application_context
        $payment->application_context = $this->getApplicationContext();

        // ### Create Payment
        // Create a payment by calling the 'create' method
        // passing it a valid apiContext.
        // The return object contains the state and the
        // url to which the buyer must be redirected to
        // for payment approval

        try {
            $payment->create($this->getApiContext());
        } catch (Throwable $e) {
            $error = new Error();
            $error
                ->setErrorCode($e->getCode())
                ->setMessage($e->getMessage());

            return $response->setError($error)->setSuccess(false);
        } catch (Exception $e) {
            $error = new Error();
            $error
                ->setErrorCode($e->getCode())
                ->setMessage($e->getMessage());

            return $response->setError($error)->setSuccess(false);
        }

        return $response
            ->setApproveLink($payment->getApprovalLink())
            ->setPaymentId($payment->getId())
            ->setSuccess(true);
    }

    protected function getApplicationContext()
    {
        if (Context::getContext()->cart->isVirtualCart()) {
            $applicationContext = [
                'shipping_preference' => 'NO_SHIPPING',
            ];
        } else {
            $applicationContext = [
                'shipping_preference' => 'SET_PROVIDED_ADDRESS',
            ];
        }

        return $applicationContext;
    }

    /**
     * @return PayerInfo
     */
    protected function getPayerInfo()
    {
        $customer = Context::getContext()->customer;
        $addressCustomer = new Address(Context::getContext()->cart->id_address_delivery);
        $countryCustomer = new Country($addressCustomer->id_country);
        $payerInfo = new PayerInfo();
        $payerInfo->setEmail($customer->email);
        $payerInfo->setFirstName($this->formatter->formatPaypalString($customer->firstname));
        $payerInfo->setLastName($this->formatter->formatPaypalString($customer->lastname));

        if ($countryCustomer->iso_code == 'BR') {
            $payerTaxId = str_replace(['.', '-', '/'], '', $addressCustomer->vat_number);
            $payerInfo->setTaxId($payerTaxId);
            $payerInfo->setTaxIdType($this->method->getTaxIdType($payerTaxId));
        }

        return $payerInfo;
    }

    /**
     * @return ShippingAddress
     */
    protected function getPayerShippingAddress()
    {
        $addressCustomer = new Address(Context::getContext()->cart->id_address_delivery);
        $payerShippingAddress = new ShippingAddress();
        $payerShippingAddress->setCountryCode(Tools::strtoupper(Country::getIsoById($addressCustomer->id_country)));
        $payerShippingAddress->setCity($this->formatter->formatPaypalString($addressCustomer->city));
        $payerShippingAddress->setLine1($this->formatter->formatPaypalString($addressCustomer->address1));
        $payerShippingAddress->setPostalCode($addressCustomer->postcode);
        $payerShippingAddress->setRecipientName($this->formatter->formatPaypalString(implode(' ', [$addressCustomer->firstname, $addressCustomer->lastname])));

        if ((int) $addressCustomer->id_state) {
            $state = new State($addressCustomer->id_state);
            $payerShippingAddress->setState($this->formatter->formatPaypalString(Tools::strtoupper($state->iso_code)));
        }

        return $payerShippingAddress;
    }

    protected function _getPaymentDetails()
    {
        $paypal = Module::getInstanceByName($this->method->name);
        $currency = $paypal->getPaymentCurrencyIso();
        $this->_getProductsList($currency);
        $this->_getDiscountsList($currency);
        $this->_getGiftWrapping($currency);
        $this->_getHandling($currency);
        $this->_getPaymentValues($currency);
    }

    protected function _getProductsList($currency)
    {
        $products = Context::getContext()->cart->getProducts();
        foreach ($products as $product) {
            $product['product_tax'] = $this->method->formatPrice($this->method->formatPrice($product['price_wt']) - $this->method->formatPrice($product['price']));
            $item = new Item();
            $item->setName($this->formatter->formatPaypalString($product['name']))
                ->setCurrency($currency)
                ->setDescription(isset($product['attributes']) ? $product['attributes'] : '')
                ->setQuantity($product['quantity'])
                ->setSku($product['id_product']) // Similar to `item_number` in Classic API
                ->setPrice($this->method->formatPrice($product['price']));

            $this->_items[] = $item;
            $this->_itemTotalValue += $this->method->formatPrice($product['price']) * $product['quantity'];
            $this->_taxTotalValue += $this->method->formatPrice($product['product_tax'] * $product['quantity']);
        }
    }

    protected function _getDiscountsList($currency)
    {
        $totalDiscounts = Context::getContext()->cart->getOrderTotal(true, Cart::ONLY_DISCOUNTS);

        if ($totalDiscounts > 0) {
            $module = Module::getInstanceByName($this->method->name);
            $discountItem = new Item();
            $discountItem->setName($module->l('Total discounts', get_class($this)))
                ->setCurrency($currency)
                ->setQuantity(1)
                ->setSku('discounts')
                ->setPrice(-1 * $totalDiscounts);

            $this->_items[] = $discountItem;
            $this->_itemTotalValue += (-1 * $totalDiscounts);
        }
    }

    protected function _getHandling($currency)
    {
        $discounts = Context::getContext()->cart->getCartRules();

        if (empty($discounts)) {
            return;
        }

        $module = Module::getInstanceByName($this->method->name);

        foreach ($discounts as $discount) {
            if ($discount['value_real'] < 0) {
                $handlingValue = abs($this->method->formatPrice($discount['value_real']));
                $handlingItem = new Item();
                $handlingItem->setName($module->l('Handling', get_class($this)))
                    ->setCurrency($currency)
                    ->setQuantity(1)
                    ->setSku('handling')
                    ->setPrice($handlingValue);

                $this->_items[] = $handlingItem;
                $this->_itemTotalValue += $handlingValue;
            }
        }
    }

    protected function _getGiftWrapping($currency)
    {
        $wrapping_price = Context::getContext()->cart->gift ? Context::getContext()->cart->getGiftWrappingPrice() : 0;
        if ($wrapping_price > 0) {
            $wrapping_price = $this->method->formatPrice($wrapping_price);
            $item = new Item();
            $item->setName('Gift wrapping')
                ->setCurrency($currency)
                ->setQuantity(1)
                ->setSku('wrapping') // Similar to `item_number` in Classic API
                ->setPrice($wrapping_price);
            $this->_items[] = $item;
            $this->_itemTotalValue += $wrapping_price;
        }
    }

    private function _getPaymentValues($currency)
    {
        $this->_itemList->setItems($this->_items);
        $context = Context::getContext();
        $cart = $context->cart;
        $shipping_cost_wt = $cart->getTotalShippingCost();
        $shipping = $this->method->formatPrice($shipping_cost_wt);
        $total = $this->method->formatPrice($cart->getOrderTotal(true, Cart::BOTH));
        $summary = $cart->getSummaryDetails();
        $subtotal = $this->method->formatPrice($summary['total_products']);
        $total_tax = number_format($this->_taxTotalValue, Paypal::getDecimal(), '.', '');
        // total shipping amount
        $shippingTotal = $shipping;

        if ($subtotal != $this->_itemTotalValue) {
            $subtotal = $this->_itemTotalValue;
        }
        //total
        $total_cart = $shippingTotal + $this->_itemTotalValue + $this->_taxTotalValue;

        if ($total != $total_cart) {
            $total = $total_cart;
        }

        // ### Additional payment details
        // Use this optional field to set additional
        // payment information such as tax, shipping
        // charges etc.
        $details = new Details();
        $details->setShipping($shippingTotal)
            ->setTax($total_tax)
            ->setSubtotal($subtotal);
        // ### Amount
        // Lets you specify a payment amount.
        // You can also specify additional details
        // such as shipping, tax.
        $this->_amount->setCurrency($currency)
            ->setTotal($total)
            ->setDetails($details);
    }
}
