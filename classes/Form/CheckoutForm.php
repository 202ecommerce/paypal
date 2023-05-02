<?php

namespace PaypalAddons\classes\Form;

use Configuration;
use Context;
use Country;
use Module;
use PaypalAddons\classes\ACDC\AcdcFunctionality;
use PaypalAddons\classes\Constants\PaypalConfigurations;
use PaypalAddons\classes\Shortcut\ShortcutConfiguration;
use Tools;

class CheckoutForm implements FormInterface
{
    /** @var \Paypal */
    protected $module;

    protected $method;

    protected $acdcFunctionality;

    public function __construct()
    {
        $this->module = Module::getInstanceByName('paypal');
        $countryDefault = new Country(Configuration::get('PS_COUNTRY_DEFAULT'), Context::getContext()->language->id);
        $this->acdcFunctionality = new AcdcFunctionality();

        switch ($countryDefault->iso_code) {
            case 'DE':
                $this->method = 'PPP';
                break;
            case 'BR':
                $this->method = 'MB';
                break;
            case 'MX':
                $this->method = 'MB';
                break;
            default:
                $this->method = 'EC';
        }
    }

    public function getDescription()
    {
        $fields = [];

        if (in_array($this->method, ['EC', 'MB'])) {
            $fields[PaypalConfigurations::INTENT] = [
                'type' => 'select',
                'label' => $this->module->l('Payment action', 'AdminPayPalSetupController'),
                'name' => PaypalConfigurations::INTENT,
                'options' => [
                    [
                        'value' => 'sale',
                        'title' => $this->module->l('Sale', 'AdminPayPalSetupController'),
                    ],
                    [
                        'value' => 'authorize',
                        'title' => $this->module->l('Authorize', 'AdminPayPalSetupController'),
                    ],
                ],
                'value' => Configuration::get(PaypalConfigurations::INTENT),
                'variant' => 'primary',
            ];

            if ($this->method == 'MB') {
                $fields[PaypalConfigurations::INTENT]['label'] = $this->module->l('Payment action (for PayPal Express Checkout only)', 'AdminPayalSetupController');
            }
        }

        $fields[PaypalConfigurations::EXPRESS_CHECKOUT_IN_CONTEXT] = [
            'type' => 'select',
            'label' => $this->module->l('PayPal checkout', 'AdminPayPalCustomizeCheckoutController'),
            'name' => PaypalConfigurations::EXPRESS_CHECKOUT_IN_CONTEXT,
            'hint' => $this->module->l('PayPal opens in a pop-up window, allowing your buyers to finalize their payment without leaving your website. Optimized, modern and reassuring experience which benefits from the same security standards than during a redirection to the PayPal website.', 'AdminPayPalCustomizeCheckoutController'),
            'options' => [
                [
                    'value' => '1',
                    'title' => $this->module->l('IN-CONTEXT', 'AdminPayPalCustomizeCheckoutController'),
                ],
                [
                    'value' => '0',
                    'title' => $this->module->l('REDIRECT', 'AdminPayPalCustomizeCheckoutController'),
                ]
            ],
            'value' => Configuration::get(PaypalConfigurations::EXPRESS_CHECKOUT_IN_CONTEXT),
            'variant' => 'primary',
        ];
        $fields[PaypalConfigurations::BRAND_NAME] = [
            'type' => 'text',
            'label' => $this->module->l('Brand name shown on bottom right during PayPal checkout', 'AdminPayPalCustomizeCheckoutController'),
            'name' => PaypalConfigurations::BRAND_NAME,
            'value' => Configuration::get(PaypalConfigurations::BRAND_NAME),
            'placeholder' => $this->module->l('Leave it empty to use your Shop name setup on your PayPal account', 'AdminPayPalCustomizeCheckoutController'),
            'hint' => $this->module->l('A label that overrides the business name in the PayPal account on the PayPal pages. If logo is set, then brand name won\'t be shown.', 'AdminPayPalCustomizeCheckoutController'),
        ];

        if ($this->method == 'PPP') {
            $fields[PaypalConfigurations::PUI_CUSTOMER_SERVICE_INSTRUCTIONS] = [
                'type' => 'text',
                'label' => $this->module->l('Customer service instructions', 'AdminPayPalCustomizeCheckoutController'),
                'name' => PaypalConfigurations::PUI_CUSTOMER_SERVICE_INSTRUCTIONS,
                'placeholder' => $this->module->l('Example: Customer service phone is +49 6912345678', 'AdminPayPalCustomizeCheckoutController'),
                'required' => true,
                'hint' => $this->module->l('Required message for using Pay upon invoice payment method', 'AdminPayPalCustomizeCheckoutController'),
                'value' => Configuration::get(PaypalConfigurations::PUI_CUSTOMER_SERVICE_INSTRUCTIONS),
            ];
        }

        $fields[PaypalConfigurations::API_ADVANTAGES] = [
            'type' => 'switch',
            'label' => $this->module->l('Show PayPal benefits to your customers', 'AdminPayPalCustomizeCheckoutController'),
            'name' => PaypalConfigurations::API_ADVANTAGES,
            'hint' => $this->module->l('You can increase your conversion rate by presenting PayPal benefits to your customers on payment methods selection page.', 'AdminPayPalCustomizeCheckoutController'),
            'values' => [
                [
                    'id' => PaypalConfigurations::API_ADVANTAGES . '_on',
                    'value' => 1,
                    'label' => $this->module->l('Enabled', 'AdminPayPalCustomizeCheckoutController'),
                ],
                [
                    'id' => PaypalConfigurations::API_ADVANTAGES . '_off',
                    'value' => 0,
                    'label' => $this->module->l('Disabled', 'AdminPayPalCustomizeCheckoutController'),
                ],
            ],
            'value' => (int) Configuration::get(PaypalConfigurations::API_ADVANTAGES),
            'variant' => 'secondary',
        ];
        $fields[ShortcutConfiguration::SHOW_ON_PRODUCT_PAGE] = [
            'type' => 'checkbox',
            'name' => ShortcutConfiguration::SHOW_ON_PRODUCT_PAGE,
            'label' => $this->module->l('Product Pages', 'blockpreviewbuttoncontext'),
            'value' => 1,
            'checked' => (bool) Configuration::get(ShortcutConfiguration::SHOW_ON_PRODUCT_PAGE),
        ];
        $fields[ShortcutConfiguration::SHOW_ON_CART_PAGE] = [
            'type' => 'checkbox',
            'name' => ShortcutConfiguration::SHOW_ON_CART_PAGE,
            'label' => $this->module->l('Cart Page', 'blockpreviewbuttoncontext'),
            'value' => 1,
            'checked' => (bool) Configuration::get(ShortcutConfiguration::SHOW_ON_CART_PAGE),
        ];
        $fields[ShortcutConfiguration::SHOW_ON_SIGNUP_STEP] = [
            'type' => 'checkbox',
            'name' => ShortcutConfiguration::SHOW_ON_SIGNUP_STEP,
            'label' => $this->module->l('Sign up step in checkout', 'blockpreviewbuttoncontext'),
            'value' => 1,
            'checked' => (bool) Configuration::get(ShortcutConfiguration::SHOW_ON_SIGNUP_STEP),
        ];

        $fields[PaypalConfigurations::MOVE_BUTTON_AT_END] = [
            'type' => 'switch',
            'label' => $this->module->l('Location', 'CheckoutForm'),
            'desc' => $this->module->l('Put the PayPal button at the end of the order page', 'AdminPayPalCustomizeCheckoutController'),
            'name' => PaypalConfigurations::MOVE_BUTTON_AT_END,
            'value' => (int) Configuration::get(PaypalConfigurations::MOVE_BUTTON_AT_END),
            'values' => [
                [
                    'id' => PaypalConfigurations::MOVE_BUTTON_AT_END . '_on',
                    'value' => 1,
                    'label' => $this->module->l('Bottom'),
                ],
                [
                    'id' => PaypalConfigurations::MOVE_BUTTON_AT_END . '_off',
                    'value' => 0,
                    'label' => $this->module->l('Radio button'),
                ],
            ],
        ];

        if ($this->acdcFunctionality->isAvailable()) {
            $fields[PaypalConfigurations::ACDC_OPTION] = [
                'type' => 'switch',
                'label' => $this->module->l('Credit/Debit card', 'AdminPayPalCustomizeCheckoutController'),
                'name' => PaypalConfigurations::ACDC_OPTION,
                'values' => [
                    [
                        'id' => PaypalConfigurations::ACDC_OPTION . '_on',
                        'value' => 1,
                        'label' => $this->module->l('Enabled', 'AdminPayPalCustomizeCheckoutController'),
                    ],
                    [
                        'id' => PaypalConfigurations::ACDC_OPTION . '_off',
                        'value' => 0,
                        'label' => $this->module->l('Disabled', 'AdminPayPalCustomizeCheckoutController'),
                    ],
                ],
                'value' => (int) Configuration::get(PaypalConfigurations::ACDC_OPTION),
            ];
        }

        if ($this->method === 'PPP') {
            $fields[PaypalConfigurations::PUI_ENABLED] = [
                'type' => 'switch',
                'label' => $this->module->l('Pay upon invoice', 'CheckoutForm'),
                'name' => PaypalConfigurations::PUI_ENABLED,
                'values' => [
                    [
                        'id' => PaypalConfigurations::PUI_ENABLED . '_on',
                        'value' => 1,
                        'label' => $this->module->l('Enabled', 'AdminPayPalCustomizeCheckoutController'),
                    ],
                    [
                        'id' => PaypalConfigurations::PUI_ENABLED . '_off',
                        'value' => 0,
                        'label' => $this->module->l('Disabled', 'AdminPayPalCustomizeCheckoutController'),
                    ],
                ],
                'value' => (int) Configuration::get(PaypalConfigurations::PUI_ENABLED),
            ];
            $fields[PaypalConfigurations::SEPA_ENABLED] = [
                'type' => 'switch',
                'label' => $this->module->l('SEPA', 'CheckoutForm'),
                'name' => PaypalConfigurations::SEPA_ENABLED,
                'values' => [
                    [
                        'id' => PaypalConfigurations::SEPA_ENABLED . '_on',
                        'value' => 1,
                        'label' => $this->module->l('Enabled', 'AdminPayPalCustomizeCheckoutController'),
                    ],
                    [
                        'id' => PaypalConfigurations::SEPA_ENABLED . '_off',
                        'value' => 0,
                        'label' => $this->module->l('Disabled', 'AdminPayPalCustomizeCheckoutController'),
                    ],
                ],
                'value' => (int) Configuration::get(PaypalConfigurations::SEPA_ENABLED),
            ];
            $fields[PaypalConfigurations::GIROPAY_ENABLED] = [
                'type' => 'switch',
                'label' => $this->module->l('Giropay', 'CheckoutForm'),
                'name' => PaypalConfigurations::GIROPAY_ENABLED,
                'values' => [
                    [
                        'id' => PaypalConfigurations::GIROPAY_ENABLED . '_on',
                        'value' => 1,
                        'label' => $this->module->l('Enabled', 'AdminPayPalCustomizeCheckoutController'),
                    ],
                    [
                        'id' => PaypalConfigurations::GIROPAY_ENABLED . '_off',
                        'value' => 0,
                        'label' => $this->module->l('Disabled', 'AdminPayPalCustomizeCheckoutController'),
                    ],
                ],
                'value' => (int) Configuration::get(PaypalConfigurations::GIROPAY_ENABLED),
            ];
            $fields[PaypalConfigurations::SOFORT_ENABLED] = [
                'type' => 'switch',
                'label' => $this->module->l('Sofort', 'CheckoutForm'),
                'name' => PaypalConfigurations::SOFORT_ENABLED,
                'values' => [
                    [
                        'id' => PaypalConfigurations::SOFORT_ENABLED . '_on',
                        'value' => 1,
                        'label' => $this->module->l('Enabled', 'AdminPayPalCustomizeCheckoutController'),
                    ],
                    [
                        'id' => PaypalConfigurations::SOFORT_ENABLED . '_off',
                        'value' => 0,
                        'label' => $this->module->l('Disabled', 'AdminPayPalCustomizeCheckoutController'),
                    ],
                ],
                'value' => (int) Configuration::get(PaypalConfigurations::SOFORT_ENABLED),
            ];
        }

        return [
            'legend' => [
                'title' => $this->module->l('Checkout', 'AdminPayPalCustomizeCheckoutController'),
            ],
            'fields' => $fields,
            'submit' => [
                'title' => $this->module->l('Save', 'AdminPayPalCustomizeCheckoutController'),
                'name' => 'checkoutForm',
            ],
            'id_form' => 'pp_checkout_form',
        ];
    }

    public function save($data = null)
    {
        if (is_null($data)) {
            $data = Tools::getAllValues();
        }

        if (empty($data['checkoutForm'])) {
            return false;
        }

        if (isset($data[PaypalConfigurations::INTENT])) {
            Configuration::updateValue(
                PaypalConfigurations::INTENT,
                pSQL($data[PaypalConfigurations::INTENT])
            );
        }

        if (isset($data[PaypalConfigurations::EXPRESS_CHECKOUT_IN_CONTEXT])) {
            Configuration::updateValue(
                PaypalConfigurations::EXPRESS_CHECKOUT_IN_CONTEXT,
                (int) $data[PaypalConfigurations::EXPRESS_CHECKOUT_IN_CONTEXT]
            );
        }

        if (isset($data[PaypalConfigurations::BRAND_NAME])) {
            Configuration::updateValue(
                PaypalConfigurations::BRAND_NAME,
                pSQL($data[PaypalConfigurations::BRAND_NAME])
            );
        }

        if (isset($data[PaypalConfigurations::PUI_CUSTOMER_SERVICE_INSTRUCTIONS])) {
            Configuration::updateValue(
                PaypalConfigurations::PUI_CUSTOMER_SERVICE_INSTRUCTIONS,
                pSQL($data[PaypalConfigurations::PUI_CUSTOMER_SERVICE_INSTRUCTIONS])
            );
        }

        Configuration::updateValue(
            PaypalConfigurations::API_ADVANTAGES,
            isset($data[PaypalConfigurations::API_ADVANTAGES]) ? 1 : 0
        );

        Configuration::updateValue(
            ShortcutConfiguration::SHOW_ON_PRODUCT_PAGE,
            isset($data[ShortcutConfiguration::SHOW_ON_PRODUCT_PAGE]) ? 1 : 0
        );

        Configuration::updateValue(
            ShortcutConfiguration::SHOW_ON_CART_PAGE,
            isset($data[ShortcutConfiguration::SHOW_ON_CART_PAGE]) ? 1 : 0
        );

        Configuration::updateValue(
            ShortcutConfiguration::SHOW_ON_SIGNUP_STEP,
            isset($data[ShortcutConfiguration::SHOW_ON_SIGNUP_STEP]) ? 1 : 0
        );

        Configuration::updateValue(
            PaypalConfigurations::ACDC_OPTION,
            isset($data[PaypalConfigurations::ACDC_OPTION]) ? 1 : 0
        );

        Configuration::updateValue(
            PaypalConfigurations::PUI_ENABLED,
            isset($data[PaypalConfigurations::PUI_ENABLED]) ? 1 : 0
        );

        Configuration::updateValue(
            PaypalConfigurations::GIROPAY_ENABLED,
            isset($data[PaypalConfigurations::GIROPAY_ENABLED]) ? 1 : 0
        );

        Configuration::updateValue(
            PaypalConfigurations::SOFORT_ENABLED,
            isset($data[PaypalConfigurations::SOFORT_ENABLED]) ? 1 : 0
        );

        Configuration::updateValue(
            PaypalConfigurations::SEPA_ENABLED,
            isset($data[PaypalConfigurations::SEPA_ENABLED]) ? 1 : 0
        );

        Configuration::updateValue(
            PaypalConfigurations::MOVE_BUTTON_AT_END,
            isset($data[PaypalConfigurations::MOVE_BUTTON_AT_END]) ? 1 : 0
        );

        return true;
    }
}
