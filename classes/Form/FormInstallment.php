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

namespace PaypalAddons\classes\Form;

use PaypalAddons\classes\InstallmentBanner\ConfigurationMap;

if (!defined('_PS_VERSION_')) {
    exit;
}

class FormInstallment implements FormInterface
{
    /** @var \PayPal */
    protected $module;

    protected $className;

    public function __construct()
    {
        /* @phpstan-ignore-next-line */
        $this->module = \Module::getInstanceByName('paypal');
        $this->className = 'FormInstallment';
    }

    /**
     * @return array
     */
    public function getDescription()
    {
        $isoCountryDefault = \Tools::strtolower(\Country::getIsoById((int) \Configuration::get('PS_COUNTRY_DEFAULT')));
        $fields = [];

        if (in_array($isoCountryDefault, ConfigurationMap::getBnplAvailableCountries())) {
            $fields[ConfigurationMap::ENABLE_BNPL] = [
                'type' => 'switch',
                'label' => $this->module->l('Pay Later button', $this->className),
                'name' => ConfigurationMap::ENABLE_BNPL,
                'values' => [
                    [
                        'id' => ConfigurationMap::ENABLE_BNPL . '_on',
                        'value' => 1,
                        'label' => $this->module->l('Enabled', $this->className),
                    ],
                    [
                        'id' => ConfigurationMap::ENABLE_BNPL . '_off',
                        'value' => 0,
                        'label' => $this->module->l('Disabled', $this->className),
                    ],
                ],
                'value' => (int) \Configuration::get(ConfigurationMap::ENABLE_BNPL),
            ];
            $fields[ConfigurationMap::BNPL_PRODUCT_PAGE] = [
                'type' => 'checkbox',
                'name' => ConfigurationMap::BNPL_PRODUCT_PAGE,
                'label' => $this->module->l('Product Page', $this->className),
                'value' => 1,
                'checked' => (bool) \Configuration::get(ConfigurationMap::BNPL_PRODUCT_PAGE),
                'image' => _MODULE_DIR_ . $this->module->name . '/views/img/product_page_button.png',
            ];
            $fields[ConfigurationMap::BNPL_PAYMENT_STEP_PAGE] = [
                'type' => 'checkbox',
                'name' => ConfigurationMap::BNPL_PAYMENT_STEP_PAGE,
                'label' => $this->module->l('Step payment in checkout', $this->className),
                'value' => 1,
                'checked' => (bool) \Configuration::get(ConfigurationMap::BNPL_PAYMENT_STEP_PAGE),
                'image' => _MODULE_DIR_ . $this->module->name . '/views/img/location.png',
            ];
            $fields[ConfigurationMap::BNPL_CART_PAGE] = [
                'type' => 'checkbox',
                'name' => ConfigurationMap::BNPL_CART_PAGE,
                'label' => $this->module->l('Cart Page', $this->className),
                'value' => 1,
                'checked' => (bool) \Configuration::get(ConfigurationMap::BNPL_CART_PAGE),
                'image' => _MODULE_DIR_ . $this->module->name . '/views/img/cart_page_button.png',
            ];
            $fields[ConfigurationMap::BNPL_CHECKOUT_PAGE] = [
                'type' => 'checkbox',
                'name' => ConfigurationMap::BNPL_CHECKOUT_PAGE,
                'label' => $this->module->l('Sign up step in checkout', $this->className),
                'value' => 1,
                'checked' => (bool) \Configuration::get(ConfigurationMap::BNPL_CHECKOUT_PAGE),
                'image' => _MODULE_DIR_ . $this->module->name . '/views/img/signin-checkout-button.png',
            ];
        }

        $description = [
            'legend' => [
                'title' => $this->module->l('Buy Now Pay Later Button', $this->className),
            ],
            'fields' => $fields,
            'submit' => [
                'title' => $this->module->l('Save', $this->className),
                'name' => 'installmentForm',
            ],
            'id_form' => 'pp_installment_form',
            'help' => $this->getHelpInfo(),
        ];

        return $description;
    }

    /**
     * @return bool
     */
    public function save($data = null)
    {
        if (is_null($data)) {
            $data = \Tools::getAllValues();
        }

        $return = true;

        if (empty($data['installmentForm'])) {
            return $return;
        }

        // BNPL configurations
        $return &= \Configuration::updateValue(
            ConfigurationMap::ENABLE_BNPL,
            isset($data[ConfigurationMap::ENABLE_BNPL]) ? (int) $data[ConfigurationMap::ENABLE_BNPL] : 0
        );
        $return &= \Configuration::updateValue(
            ConfigurationMap::BNPL_CHECKOUT_PAGE,
            isset($data[ConfigurationMap::BNPL_CHECKOUT_PAGE]) ? (int) $data[ConfigurationMap::BNPL_CHECKOUT_PAGE] : 0
        );
        $return &= \Configuration::updateValue(
            ConfigurationMap::BNPL_CART_PAGE,
            isset($data[ConfigurationMap::BNPL_CART_PAGE]) ? (int) $data[ConfigurationMap::BNPL_CART_PAGE] : 0
        );
        $return &= \Configuration::updateValue(
            ConfigurationMap::BNPL_PRODUCT_PAGE,
            isset($data[ConfigurationMap::BNPL_PRODUCT_PAGE]) ? (int) $data[ConfigurationMap::BNPL_PRODUCT_PAGE] : 0
        );
        $return &= \Configuration::updateValue(
            ConfigurationMap::BNPL_PAYMENT_STEP_PAGE,
            isset($data[ConfigurationMap::BNPL_PAYMENT_STEP_PAGE]) ? (int) $data[ConfigurationMap::BNPL_PAYMENT_STEP_PAGE] : 0
        );

        return (bool) $return;
    }

    protected function getHelpInfo()
    {
        return \Context::getContext()->smarty->fetch(_PS_MODULE_DIR_ . $this->module->name . '/views/templates/admin/_partials/messages/form-help-info/bnpl.tpl');
    }
}
