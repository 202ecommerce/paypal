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
use PaypalAddons\classes\Form\Field\Select;
use PaypalAddons\classes\Form\Field\SelectOption;
use PaypalAddons\classes\Form\FormInterface;
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

        $reflection = new \ReflectionClass($this);
        $this->className = $reflection->getShortName();
    }

    /**
     * @return array
     */
    public function getDesciption()
    {
        $isoCountryDefault = Tools::strtolower(Country::getIsoById(Configuration::get('PS_COUNTRY_DEFAULT')));
        $fields = [];

        if (in_array($isoCountryDefault, ConfigurationMap::getBnplAvailableCountries())) {
            $fields[ConfigurationMap::ENABLE_BNPL] = [
                'type' => 'switch',
                'label' => $this->module->l('Enable \'Pay in X times\' in your checkout', $this->className),
                'name' => ConfigurationMap::ENABLE_BNPL,
                'is_bool' => true,
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
                'label' => $this->module->l('Product Pages', $this->className),
                'value' => Configuration::get(ConfigurationMap::BNPL_PRODUCT_PAGE),
            ];
            $fields[ConfigurationMap::BNPL_PAYMENT_STEP_PAGE] = [
                'type' => 'checkbox',
                'name' => ConfigurationMap::BNPL_PAYMENT_STEP_PAGE,
                'label' => $this->module->l('Step payment in checkout', $this->className),
                'value' => Configuration::get(ConfigurationMap::BNPL_PAYMENT_STEP_PAGE),
            ];
            $fields[ConfigurationMap::BNPL_CART_PAGE] = [
                'type' => 'checkbox',
                'name' => ConfigurationMap::BNPL_CART_PAGE,
                'label' => $this->module->l('Cart Page', $this->className),
                'value' => Configuration::get(ConfigurationMap::BNPL_CART_PAGE),
            ];
            $fields[ConfigurationMap::BNPL_CHECKOUT_PAGE] = [
                'type' => 'checkbox',
                'name' => ConfigurationMap::BNPL_CHECKOUT_PAGE,
                'label' => $this->module->l('Sign up step in checkout', $this->className),
                'value' => Configuration::get(ConfigurationMap::BNPL_CHECKOUT_PAGE),
            ];
        }

        $fields[ConfigurationMap::ENABLE_INSTALLMENT] = [
            'type' => 'switch',
            'label' => $this->module->l('Enable the display of banners', $this->className),
            'name' => ConfigurationMap::ENABLE_INSTALLMENT,
            'is_bool' => true,
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
        ];
        $fields[ConfigurationMap::PRODUCT_PAGE] = [
            'type' => 'checkbox',
            'value' => Configuration::get(ConfigurationMap::PRODUCT_PAGE),
            'name' => ConfigurationMap::PRODUCT_PAGE,
            'label' => $this->module->l('Product Page', $this->className),
        ];
        $fields[ConfigurationMap::HOME_PAGE] = [
            'type' => 'checkbox',
            'value' => Configuration::get(ConfigurationMap::HOME_PAGE),
            'name' => ConfigurationMap::HOME_PAGE,
            'label' => $this->module->l('Home Page', $this->className),
        ];
        $fields[ConfigurationMap::CATEGORY_PAGE] = [
            'type' => 'checkbox',
            'value' => Configuration::get(ConfigurationMap::CATEGORY_PAGE),
            'name' => ConfigurationMap::CATEGORY_PAGE,
            'label' => $this->module->l('Category Page', $this->className),
        ];
        $fields[ConfigurationMap::CART_PAGE] = [
            'type' => 'checkbox',
            'value' => Configuration::get(ConfigurationMap::CART_PAGE),
            'name' => ConfigurationMap::CART_PAGE,
            'label' => $this->module->l('Cart', $this->className),
        ];
        $fields[ConfigurationMap::CHECKOUT_PAGE] = [
            'type' => 'checkbox',
            'value' => Configuration::get(ConfigurationMap::CHECKOUT_PAGE),
            'name' => ConfigurationMap::CART_PAGE,
            'label' => $this->module->l('Checkout', $this->className),
        ];
        $fields[ConfigurationMap::ADVANCED_OPTIONS_INSTALLMENT] = [
            'type' => 'switch',
            'label' => $this->module->l('Advanced options', $this->className),
            'name' => ConfigurationMap::ADVANCED_OPTIONS_INSTALLMENT,
            'is_bool' => true,
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
            'label' => $this->module->l('Color', $this->className),
        ];

        $description = [
            'legend' => [
                'title' => $this->module->l('Settings', $this->className),
                'icon' => 'icon-cogs',
            ],
            'fields' => $fields,
            'submit' => [
                'title' => $this->module->l('Save', $this->className),
                'name' => 'installmentForm',
            ],
            'id_form' => 'pp_config_installment',
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
                'name' => ConfigurationMap::COLOR_GRAY,
                'title' => $this->module->l('gray', $this->className)
            ],
            [
                'name' => ConfigurationMap::COLOR_BLUE,
                'title' => $this->module->l('blue', $this->className)
            ],
            [
                'name' => ConfigurationMap::COLOR_BLACK,
                'title' => $this->module->l('black', $this->className)
            ],
            [
                'name' => ConfigurationMap::COLOR_WHITE,
                'title' => $this->module->l('white', $this->className)
            ],
        ];

        if ($isoCountryDefault !== 'de') {
            $colorOptions[] = [
                'name' => ConfigurationMap::COLOR_MONOCHROME,
                'title' => $this->module->l('monochrome', $this->className)
            ];
            $colorOptions[] = [
                'name' => ConfigurationMap::COLOR_GRAYSCALE,
                'title' => $this->module->l('grayscale', $this->className)
            ];
        }

        return $colorOptions;
    }
}
