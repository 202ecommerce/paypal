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

namespace PaypalAddons\classes\Form;

use Configuration;
use Context;
use Country;
use Module;
use PaypalAddons\classes\InstallmentBanner\ConfigurationMap;
use Tools;

class FormInstallment implements FormInterface
{
    /** @var \Paypal */
    protected $module;

    protected $className;

    public function __construct()
    {
        $this->module = Module::getInstanceByName('paypal');
        $this->className = 'FormInstallment';
    }

    /**
     * @return array
     */
    public function getDescription()
    {
        $isoCountryDefault = Tools::strtolower(Country::getIsoById(Configuration::get('PS_COUNTRY_DEFAULT')));
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
                'value' => (int) Configuration::get(ConfigurationMap::ENABLE_BNPL),
            ];
            $fields[ConfigurationMap::BNPL_PRODUCT_PAGE] = [
                'type' => 'checkbox',
                'name' => ConfigurationMap::BNPL_PRODUCT_PAGE,
                'label' => $this->module->l('Product Page', $this->className),
                'value' => 1,
                'checked' => (bool) Configuration::get(ConfigurationMap::BNPL_PRODUCT_PAGE),
                'image' => _MODULE_DIR_ . $this->module->name . '/views/img/product_page_button.png',
            ];
            $fields[ConfigurationMap::BNPL_PAYMENT_STEP_PAGE] = [
                'type' => 'checkbox',
                'name' => ConfigurationMap::BNPL_PAYMENT_STEP_PAGE,
                'label' => $this->module->l('Step payment in checkout', $this->className),
                'value' => 1,
                'checked' => (bool) Configuration::get(ConfigurationMap::BNPL_PAYMENT_STEP_PAGE),
                'image' => _MODULE_DIR_ . $this->module->name . '/views/img/location.png',
            ];
            $fields[ConfigurationMap::BNPL_CART_PAGE] = [
                'type' => 'checkbox',
                'name' => ConfigurationMap::BNPL_CART_PAGE,
                'label' => $this->module->l('Cart Page', $this->className),
                'value' => 1,
                'checked' => (bool) Configuration::get(ConfigurationMap::BNPL_CART_PAGE),
                'image' => _MODULE_DIR_ . $this->module->name . '/views/img/cart_page_button.png',
            ];
            $fields[ConfigurationMap::BNPL_CHECKOUT_PAGE] = [
                'type' => 'checkbox',
                'name' => ConfigurationMap::BNPL_CHECKOUT_PAGE,
                'label' => $this->module->l('Sign up step in checkout', $this->className),
                'value' => 1,
                'checked' => (bool) Configuration::get(ConfigurationMap::BNPL_CHECKOUT_PAGE),
                'image' => _MODULE_DIR_ . $this->module->name . '/views/img/signin-checkout-button.png',
            ];
        }

        $fields[ConfigurationMap::ENABLE_INSTALLMENT] = [
            'type' => 'switch',
            'label' => $this->module->l('Pay later messaging', $this->className),
            'name' => ConfigurationMap::ENABLE_INSTALLMENT,
            'hint' => $this->module->l('Let your customers know about the option \'Pay 4x PayPal\' by displaying banners on your site.', $this->className),
            'values' => [
                [
                    'id' => ConfigurationMap::ENABLE_INSTALLMENT . '_on',
                    'value' => 1,
                    'label' => $this->module->l('Enabled', $this->className),
                ],
                [
                    'id' => ConfigurationMap::ENABLE_INSTALLMENT . '_off',
                    'value' => 0,
                    'label' => $this->module->l('Disabled', $this->className),
                ],
            ],
            'value' => (int) Configuration::get(ConfigurationMap::ENABLE_INSTALLMENT),
        ];
        $fields[ConfigurationMap::PRODUCT_PAGE] = [
            'type' => 'checkbox',
            'value' => 1,
            'checked' => (bool) Configuration::get(ConfigurationMap::PRODUCT_PAGE),
            'name' => ConfigurationMap::PRODUCT_PAGE,
            'label' => $this->module->l('Product Page', $this->className),
            'image' => _MODULE_DIR_ . $this->module->name . '/views/img/product_page_button.png',
        ];
        $fields[ConfigurationMap::HOME_PAGE] = [
            'type' => 'checkbox',
            'value' => 1,
            'checked' => (bool) Configuration::get(ConfigurationMap::HOME_PAGE),
            'name' => ConfigurationMap::HOME_PAGE,
            'label' => $this->module->l('Home Page', $this->className),
            'image' => _MODULE_DIR_ . $this->module->name . '/views/img/location.png',
        ];
        $fields[ConfigurationMap::CATEGORY_PAGE] = [
            'type' => 'checkbox',
            'value' => 1,
            'checked' => (bool) Configuration::get(ConfigurationMap::CATEGORY_PAGE),
            'name' => ConfigurationMap::CATEGORY_PAGE,
            'label' => $this->module->l('Category Page', $this->className),
            'image' => _MODULE_DIR_ . $this->module->name . '/views/img/location.png',
        ];
        $fields[ConfigurationMap::CART_PAGE] = [
            'type' => 'checkbox',
            'value' => 1,
            'checked' => (bool) Configuration::get(ConfigurationMap::CART_PAGE),
            'name' => ConfigurationMap::CART_PAGE,
            'label' => $this->module->l('Cart', $this->className),
            'image' => _MODULE_DIR_ . $this->module->name . '/views/img/cart_page_button.png',
        ];
        $fields[ConfigurationMap::CHECKOUT_PAGE] = [
            'type' => 'checkbox',
            'value' => 1,
            'checked' => (bool) Configuration::get(ConfigurationMap::CHECKOUT_PAGE),
            'name' => ConfigurationMap::CHECKOUT_PAGE,
            'label' => $this->module->l('Checkout', $this->className),
            'image' => _MODULE_DIR_ . $this->module->name . '/views/img/location.png',
        ];
        $fields[ConfigurationMap::ADVANCED_OPTIONS_INSTALLMENT] = [
            'type' => 'switch',
            'label' => $this->module->l('Advanced settings', $this->className),
            'name' => ConfigurationMap::ADVANCED_OPTIONS_INSTALLMENT,
            'values' => [
                [
                    'id' => ConfigurationMap::ADVANCED_OPTIONS_INSTALLMENT . '_on',
                    'value' => 1,
                    'label' => $this->module->l('Enabled', $this->className),
                ],
                [
                    'id' => ConfigurationMap::ADVANCED_OPTIONS_INSTALLMENT . '_off',
                    'value' => 0,
                    'label' => $this->module->l('Disabled', $this->className),
                ],
            ],
            'value' => Configuration::get(ConfigurationMap::ADVANCED_OPTIONS_INSTALLMENT),
        ];

        $fields[ConfigurationMap::COLOR] = [
            'type' => 'select',
            'options' => $this->getColorListOptions(),
            'name' => ConfigurationMap::COLOR,
            'label' => $this->module->l('Messaging color', $this->className),
            'value' => Configuration::get(ConfigurationMap::COLOR),
        ];

        $fields['widget_code'] = [
            'type' => 'widget-code',
            'code' => '{widget name=\'paypal\' action=\'banner4x\'}',
            'name' => 'banner-widget-code',
            'label' => $this->module->l('Widget code', $this->className),
            'hint' => $this->module->l('By default, PayPal 4x banner is displayed on your web site via PrestaShop native hook. If you choose to use widgets, you will be able to copy widget code and insert it wherever you want in the web site template.', $this->className),
        ];

        $description = [
            'legend' => [
                'title' => $this->module->l('Buy Now Pay Later', $this->className),
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
            $data = Tools::getAllValues();
        }

        $return = true;

        if (empty($data['installmentForm'])) {
            return $return;
        }

        $return &= Configuration::updateValue(
            ConfigurationMap::ENABLE_INSTALLMENT,
            (isset($data[ConfigurationMap::ENABLE_INSTALLMENT]) ? (int) $data[ConfigurationMap::ENABLE_INSTALLMENT] : 0)
        );
        $return &= Configuration::updateValue(
            ConfigurationMap::ADVANCED_OPTIONS_INSTALLMENT,
            (isset($data[ConfigurationMap::ADVANCED_OPTIONS_INSTALLMENT]) ? (int) $data[ConfigurationMap::ADVANCED_OPTIONS_INSTALLMENT] : 0)
        );
        $return &= Configuration::updateValue(
            ConfigurationMap::PRODUCT_PAGE,
            (isset($data[ConfigurationMap::PRODUCT_PAGE]) ? (int) $data[ConfigurationMap::PRODUCT_PAGE] : 0)
        );
        $return &= Configuration::updateValue(
            ConfigurationMap::CART_PAGE,
            (isset($data[ConfigurationMap::CART_PAGE]) ? (int) $data[ConfigurationMap::CART_PAGE] : 0)
        );
        $return &= Configuration::updateValue(
            ConfigurationMap::CHECKOUT_PAGE,
            (isset($data[ConfigurationMap::CHECKOUT_PAGE]) ? (int) $data[ConfigurationMap::CHECKOUT_PAGE] : 0)
        );
        $return &= Configuration::updateValue(
            ConfigurationMap::HOME_PAGE,
            (isset($data[ConfigurationMap::HOME_PAGE]) ? (int) $data[ConfigurationMap::HOME_PAGE] : 0)
        );
        $return &= Configuration::updateValue(
            ConfigurationMap::CATEGORY_PAGE,
            (isset($data[ConfigurationMap::CATEGORY_PAGE]) ? (int) $data[ConfigurationMap::CATEGORY_PAGE] : 0)
        );
        $return &= Configuration::updateValue(
            ConfigurationMap::COLOR,
            (isset($data[ConfigurationMap::COLOR]) ? pSQL($data[ConfigurationMap::COLOR]) : '')
        );

        // BNPL configurations
        $return &= Configuration::updateValue(
            ConfigurationMap::ENABLE_BNPL,
            (isset($data[ConfigurationMap::ENABLE_BNPL]) ? (int) $data[ConfigurationMap::ENABLE_BNPL] : 0)
        );
        $return &= Configuration::updateValue(
            ConfigurationMap::BNPL_CHECKOUT_PAGE,
            (isset($data[ConfigurationMap::BNPL_CHECKOUT_PAGE]) ? (int) $data[ConfigurationMap::BNPL_CHECKOUT_PAGE] : 0)
        );
        $return &= Configuration::updateValue(
            ConfigurationMap::BNPL_CART_PAGE,
            (isset($data[ConfigurationMap::BNPL_CART_PAGE]) ? (int) $data[ConfigurationMap::BNPL_CART_PAGE] : 0)
        );
        $return &= Configuration::updateValue(
            ConfigurationMap::BNPL_PRODUCT_PAGE,
            (isset($data[ConfigurationMap::BNPL_PRODUCT_PAGE]) ? (int) $data[ConfigurationMap::BNPL_PRODUCT_PAGE] : 0)
        );
        $return &= Configuration::updateValue(
            ConfigurationMap::BNPL_PAYMENT_STEP_PAGE,
            (isset($data[ConfigurationMap::BNPL_PAYMENT_STEP_PAGE]) ? (int) $data[ConfigurationMap::BNPL_PAYMENT_STEP_PAGE] : 0)
        );

        return $return;
    }

    protected function getColorListOptions()
    {
        $isoCountryDefault = Tools::strtolower(Country::getIsoById(Configuration::get('PS_COUNTRY_DEFAULT')));
        $colorOptions = [
            [
                'value' => ConfigurationMap::COLOR_GRAY,
                'title' => $this->module->l('gray', $this->className),
                'color' => '#808080',
            ],
            [
                'value' => ConfigurationMap::COLOR_BLUE,
                'title' => $this->module->l('blue', $this->className),
                'color' => '#0070ba',
            ],
            [
                'value' => ConfigurationMap::COLOR_BLACK,
                'title' => $this->module->l('black', $this->className),
                'color' => '#2c2e2f',
            ],
            [
                'value' => ConfigurationMap::COLOR_WHITE,
                'title' => $this->module->l('white', $this->className),
                'color' => '#fff',
            ],
        ];

        if ($isoCountryDefault !== 'de') {
            $colorOptions[] = [
                'value' => ConfigurationMap::COLOR_MONOCHROME,
                'title' => $this->module->l('monochrome', $this->className),
                'color' => '#808080',
            ];
            $colorOptions[] = [
                'value' => ConfigurationMap::COLOR_GRAYSCALE,
                'title' => $this->module->l('grayscale', $this->className),
                'color' => '#808080',
            ];
        }

        return $colorOptions;
    }

    protected function getHelpInfo()
    {
        return Context::getContext()->smarty->fetch(_PS_MODULE_DIR_ . $this->module->name . '/views/templates/admin/_partials/messages/form-help-info/bnpl.tpl');
    }
}
