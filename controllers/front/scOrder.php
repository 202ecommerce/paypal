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

use PaypalAddons\classes\AbstractMethodPaypal;
use PaypalAddons\services\ToolKit;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Update PrestaShop Order after return from PayPal
 */
class PaypalScOrderModuleFrontController extends PaypalAbstarctModuleFrontController
{
    protected $paymentData;

    /** @var AbstractMethodPaypal */
    protected $method;

    protected $fileName;
    /** @var ToolKit */
    protected $toolKit;

    public function init()
    {
        parent::init();
        $this->fileName = pathinfo(__FILE__)['filename'];
        $this->setPaymentData(json_decode(Tools::getValue('paymentData')));
        $this->method = AbstractMethodPaypal::load();
        $this->toolKit = new ToolKit();
    }

    /**
     * @see FrontController::postProcess()
     */
    public function postProcess()
    {
        try {
            $this->redirectUrl = $this->context->link->getPageLink('order', null, null, ['step' => 2]);
            $this->method->setPaymentId($this->paymentData->orderID);
            $info = $this->method->getInfo();
            $this->prepareOrder($info);

            if (!empty($this->_errors)) {
                return;
            }
        } catch (PaypalAddons\classes\PaypalException $e) {
            $this->_errors['error_code'] = $e->getCode();
            $this->_errors['error_msg'] = $e->getMessage();
            $this->_errors['msg_long'] = $e->getMessageLong();
        } catch (Exception $e) {
            $this->_errors['error_code'] = $e->getCode();
            $this->_errors['error_msg'] = $e->getMessage();
        }

        if (!empty($this->_errors)) {
            $this->redirectUrl = Context::getContext()->link->getModuleLink($this->name, 'error', $this->_errors);
        }
    }

    /**
     * @param PaypalAddons\classes\API\Response\ResponseOrderGet $info
     */
    public function prepareOrder($info)
    {
        if (false === $this->method->isCorrectCart($this->context->cart, $this->paymentData->orderID)) {
            $this->redirectUrl = Context::getContext()->link->getPageLink('order');
            $this->_errors[] = $this->module->l('The elements in the shopping cart were changed. Please try to pay again.', $this->fileName);
            $this->module->resetCookiePaymentInfo();

            return;
        }

        if ($this->context->cookie->__get('logged')) {
            $customer = $this->context->customer;
        } elseif ($id_customer = Customer::customerExists($info->getClient()->getEmail(), true)) {
            /* @phpstan-ignore-next-line */
            $customer = new Customer($id_customer);
        } else {
            $customer = new Customer();
            $customer->email = $info->getClient()->getEmail();
            $customer->firstname = $info->getClient()->getFirstName();
            $customer->lastname = $info->getClient()->getLastName();
            $customer->passwd = $this->toolKit->hash(Tools::passwdGen());

            $customer->add();
        }
        $id_cart = $this->context->cart->id; // save id cart

        // Login Customer
        $this->context->updateCustomer($customer);

        $this->context->cart = new Cart($id_cart); // Reload cart
        $this->context->cart->id_customer = $customer->id;
        $this->context->cart->update();

        Hook::exec('actionAuthentication', ['customer' => $this->context->customer]);
        // Login information have changed, so we check if the cart rules still apply
        CartRule::autoRemoveFromCart($this->context);
        CartRule::autoAddToCart($this->context);
        // END Login
        if ($this->method instanceof MethodPPP) {
            $this->context->cookie->__set('paypal_pSc', $this->paymentData->orderID);
            $this->context->cookie->__set('paypal_pSc_email', $info->getClient()->getEmail());
        } else {
            $this->context->cookie->__set('paypal_ecs', $this->paymentData->orderID);
            $this->context->cookie->__set('paypal_ecs_email', $info->getClient()->getEmail());
        }

        $addresses = $this->context->customer->getAddresses($this->context->language->id);
        $address_exist = false;
        $count = 1;
        $id_address = 0;
        $id_state = PayPal::getIdStateByPaypalCode($info->getAddress()->getStateCode(), $info->getAddress()->getCountryCode());

        foreach ($addresses as $address) {
            if ($address['firstname'] . ' ' . $address['lastname'] == $info->getAddress()->getFullName()
                && $address['address1'] == $info->getAddress()->getAddress1()
                && $address['address2'] == $info->getAddress()->getAddress2()
                && $address['id_country'] == Country::getByIso($info->getAddress()->getCountryCode())
                && $address['city'] == $info->getAddress()->getCity()
                && (empty($info->getAddress()->getStateCode()) || $address['id_state'] == $id_state)
                && $address['postcode'] == $info->getAddress()->getPostCode()
                && $address['phone'] == $info->getAddress()->getPhone()
            ) {
                $address_exist = true;
                $id_address = $address['id_address'];
                break;
            } else {
                if (strrpos($address['alias'], 'Paypal_Address') !== false) {
                    $count = (int) Tools::substr($address['alias'], -1) + 1;
                }
            }
        }
        if (!$address_exist) {
            $nameArray = explode(' ', $info->getAddress()->getFullName());
            $firstName = implode(' ', array_slice($nameArray, 0, count($nameArray) - 1));
            $lastName = $nameArray[count($nameArray) - 1];

            $orderAddress = new Address();
            $orderAddress->firstname = $firstName;
            $orderAddress->lastname = $lastName;
            $orderAddress->address1 = $info->getAddress()->getAddress1();
            $orderAddress->address2 = $info->getAddress()->getAddress2();
            $orderAddress->id_country = Country::getByIso($info->getAddress()->getCountryCode());
            $orderAddress->city = $info->getAddress()->getCity();

            if ($id_state) {
                $orderAddress->id_state = $id_state;
            }

            $orderAddress->postcode = $info->getAddress()->getPostCode();
            $orderAddress->phone = $info->getAddress()->getPhone();

            $orderAddress->id_customer = $customer->id;
            $orderAddress->alias = 'Paypal_Address ' . $count;
            $validationMessage = $orderAddress->validateFields(false, true);
            if (Country::containsStates($orderAddress->id_country) && $orderAddress->id_state == false) {
                $validationMessage = $this->module->l('State is required in order to process payment. Please fill in state field.', $this->fileName);
            }
            $country = new Country($orderAddress->id_country);
            if ($country->active == false) {
                $validationMessage = $this->module->l('Country is not active', $this->fileName);
            }

            if (is_string($validationMessage)) {
                $vars = [
                    'newAddress' => 'delivery',
                    'address1' => $orderAddress->address1,
                    'firstname' => $orderAddress->firstname,
                    'lastname' => $orderAddress->lastname,
                    'postcode' => $orderAddress->postcode,
                    'id_country' => $orderAddress->id_country,
                    'city' => $orderAddress->city,
                    'phone' => $orderAddress->phone,
                    'address2' => $orderAddress->address2,
                    'id_state' => $orderAddress->id_state,
                ];

                $this->_errors[] = $validationMessage;
                $url = Context::getContext()->link->getPageLink('order', null, null, $vars);
                $this->redirectUrl = $url;

                return;
            }
            $orderAddress->save();
            $id_address = $orderAddress->id;
        }

        $this->context->cart->updateAddressId($this->context->cart->id_address_delivery, $id_address);
        $invalidAddressIds = [];

        if (version_compare(_PS_VERSION_, '1.7.3.0', '>=')) {
            $addressValidator = new AddressValidator();
            $invalidAddressIds = $addressValidator->validateCartAddresses($this->context->cart);
        }

        if (empty($invalidAddressIds) == false) {
            $vars = [
                'id_address' => $id_address,
                'editAddress' => 'delivery',
            ];

            $this->_errors[] = $this->l('Your address is incomplete, please update it.');
            $url = Context::getContext()->link->getPageLink('order', null, null, $vars);
            $this->redirectUrl = $url;
        }
    }

    public function setPaymentData($paymentData)
    {
        $this->paymentData = $paymentData;

        return $this;
    }
}
