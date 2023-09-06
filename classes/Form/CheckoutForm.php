<?php

namespace PaypalAddons\classes\Form;

use Configuration;
use Context;
use Country;
use Module;
use PaypalAddons\classes\ACDC\AcdcFunctionality;
use PaypalAddons\classes\Constants\PaypalConfigurations;
use PaypalAddons\classes\Shortcut\ShortcutConfiguration;
use PaypalAddons\classes\Venmo\VenmoFunctionality;
use Tools;

if (!defined('_PS_VERSION_')) {
    exit;
}

class CheckoutForm implements FormInterface
{
    /** @var \Paypal */
    protected $module;

    protected $method;

    protected $acdcFunctionality;

    protected $venmoFunctionality;

    public function __construct()
    {
        $this->module = Module::getInstanceByName('paypal');
        $countryDefault = new Country(Configuration::get('PS_COUNTRY_DEFAULT'), Context::getContext()->language->id);
        $this->acdcFunctionality = new AcdcFunctionality();
        $this->venmoFunctionality = new VenmoFunctionality();

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
        $countryDefault = new Country(Configuration::get('PS_COUNTRY_DEFAULT'), Context::getContext()->language->id);
        $fields = [];

        if ($this->method == 'MB') {
            $fields[PaypalConfigurations::MB_EC_ENABLED] = [
                'type' => 'switch',
                'label' => $this->module->l('Accept PayPal payments', 'AdminPayPalCustomizeCheckoutController'),
                'name' => PaypalConfigurations::MB_EC_ENABLED,
                'values' => [
                    [
                        'id' => PaypalConfigurations::MB_EC_ENABLED . '_on',
                        'value' => 1,
                        'label' => $this->module->l('Enabled', 'AdminPayPalCustomizeCheckoutController'),
                    ],
                    [
                        'id' => PaypalConfigurations::MB_EC_ENABLED . '_off',
                        'value' => 0,
                        'label' => $this->module->l('Disabled', 'AdminPayPalCustomizeCheckoutController'),
                    ],
                ],
                'value' => (int) Configuration::get(PaypalConfigurations::MB_EC_ENABLED),
            ];
        }

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
                ],
            ],
            'value' => Configuration::get(PaypalConfigurations::EXPRESS_CHECKOUT_IN_CONTEXT),
            'variant' => 'primary',
        ];
        $fields[PaypalConfigurations::BRAND_NAME] = [
            'type' => 'text',
            'label' => $this->module->l('Brand name', 'CheckoutForm'),
            'name' => PaypalConfigurations::BRAND_NAME,
            'value' => Configuration::get(PaypalConfigurations::BRAND_NAME),
            'placeholder' => $this->module->l('Leave it empty to use your shop name', 'AdminPayPalCustomizeCheckoutController'),
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
            'label' => $this->module->l('Product Page', 'blockpreviewbuttoncontext'),
            'value' => 1,
            'checked' => (bool) Configuration::get(ShortcutConfiguration::SHOW_ON_PRODUCT_PAGE),
            'image' => _MODULE_DIR_ . $this->module->name . '/views/img/product_page_button.png',
        ];
        $fields[ShortcutConfiguration::SHOW_ON_CART_PAGE] = [
            'type' => 'checkbox',
            'name' => ShortcutConfiguration::SHOW_ON_CART_PAGE,
            'label' => $this->module->l('Cart Page', 'blockpreviewbuttoncontext'),
            'value' => 1,
            'checked' => (bool) Configuration::get(ShortcutConfiguration::SHOW_ON_CART_PAGE),
            'image' => _MODULE_DIR_ . $this->module->name . '/views/img/cart_page_button.png',
        ];
        $fields[ShortcutConfiguration::SHOW_ON_SIGNUP_STEP] = [
            'type' => 'checkbox',
            'name' => ShortcutConfiguration::SHOW_ON_SIGNUP_STEP,
            'label' => $this->module->l('Sign up step in checkout', 'blockpreviewbuttoncontext'),
            'value' => 1,
            'checked' => (bool) Configuration::get(ShortcutConfiguration::SHOW_ON_SIGNUP_STEP),
            'image' => _MODULE_DIR_ . $this->module->name . '/views/img/signin-checkout-button.png',
        ];

        $fields[PaypalConfigurations::MOVE_BUTTON_AT_END] = [
            'type' => 'switch',
            'label' => $this->module->l('Put the PayPal button at the end of the order page', 'CheckoutForm'),
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

        if ($this->venmoFunctionality->isAvailable()) {
            $fields[PaypalConfigurations::VENMO_OPTION] = [
                'type' => 'switch',
                'label' => $this->module->l('Venmo', 'AdminPayPalCustomizeCheckoutController'),
                'name' => PaypalConfigurations::VENMO_OPTION,
                'is_bool' => true,
                'values' => [
                    [
                        'id' => PaypalConfigurations::VENMO_OPTION . '_on',
                        'value' => 1,
                        'label' => $this->module->l('Enabled', 'AdminPayPalCustomizeCheckoutController'),
                    ],
                    [
                        'id' => PaypalConfigurations::VENMO_OPTION . '_off',
                        'value' => 0,
                        'label' => $this->module->l('Disabled', 'AdminPayPalCustomizeCheckoutController'),
                    ],
                ],
                'value' => (int) Configuration::get(PaypalConfigurations::VENMO_OPTION),
            ];
        }

        if (in_array($this->method, ['MB', 'EC'])) {
            if (in_array($countryDefault->iso_code, $this->module->countriesApiCartUnavailable) == false) {
                $fields[PaypalConfigurations::API_CARD] = [
                    'type' => 'switch',
                    'label' => $this->module->l('Accept credit and debit card payment', 'AdminPayPalCustomizeCheckoutController'),
                    'name' => PaypalConfigurations::API_CARD,
                    'values' => [
                        [
                            'id' => PaypalConfigurations::API_CARD . '_on',
                            'value' => 1,
                            'label' => $this->module->l('Enabled', 'AdminPayPalCustomizeCheckoutController'),
                        ],
                        [
                            'id' => PaypalConfigurations::API_CARD . '_off',
                            'value' => 0,
                            'label' => $this->module->l('Disabled', 'AdminPayPalCustomizeCheckoutController'),
                        ],
                    ],
                    'value' => (int) Configuration::get(PaypalConfigurations::API_CARD),
                ];
            }
        }

        if ($this->method == 'MB') {
            $fields[PaypalConfigurations::VAULTING] = [
                'type' => 'switch',
                'label' => $this->module->l('Enable "Remember my cards" feature', 'AdminPayPalCustomizeCheckoutController'),
                'name' => PaypalConfigurations::VAULTING,
                'hint' => $this->module->l('The Vault is used to process payments so your customers don\'t need to re-enter their information each time they make a purchase from you.', 'AdminPayPalCustomizeCheckoutController'),
                'values' => [
                    [
                        'id' => PaypalConfigurations::VAULTING . '_on',
                        'value' => 1,
                        'label' => $this->module->l('Enabled', 'AdminPayPalCustomizeCheckoutController'),
                    ],
                    [
                        'id' => PaypalConfigurations::VAULTING . '_off',
                        'value' => 0,
                        'label' => $this->module->l('Disabled', 'AdminPayPalCustomizeCheckoutController'),
                    ],
                ],
                'value' => (int) Configuration::get(PaypalConfigurations::VAULTING),
            ];

            $fields[PaypalConfigurations::MERCHANT_INSTALLMENT] = [
                'type' => 'switch',
                'label' => $this->module->l('Payments with installments', 'AdminPayPalCustomizeCheckoutController'),
                'name' => PaypalConfigurations::MERCHANT_INSTALLMENT,
                'hint' => $this->module->l('Enable this option if you want to enable installments. If enabled, your clients will be able to change the number of installments (by default, 1x payment will be offered). This option can be available only for registered users.', 'AdminPayPalCustomizeCheckoutController'),
                'values' => [
                    [
                        'id' => PaypalConfigurations::MERCHANT_INSTALLMENT . '_on',
                        'value' => 1,
                        'label' => $this->module->l('Enabled', 'AdminPayPalCustomizeCheckoutController'),
                    ],
                    [
                        'id' => PaypalConfigurations::MERCHANT_INSTALLMENT . '_off',
                        'value' => 0,
                        'label' => $this->module->l('Disabled', 'AdminPayPalCustomizeCheckoutController'),
                    ],
                ],
                'value' => (int) Configuration::get(PaypalConfigurations::MERCHANT_INSTALLMENT),
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
            'help' => $this->getHelpInfo(),
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
            PaypalConfigurations::MB_EC_ENABLED,
            isset($data[PaypalConfigurations::MB_EC_ENABLED]) ? 1 : 0
        );

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
            PaypalConfigurations::VENMO_OPTION,
            isset($data[PaypalConfigurations::VENMO_OPTION]) ? 1 : 0
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

        Configuration::updateValue(
            PaypalConfigurations::API_CARD,
            isset($data[PaypalConfigurations::API_CARD]) ? 1 : 0
        );

        Configuration::updateValue(
            PaypalConfigurations::VAULTING,
            isset($data[PaypalConfigurations::VAULTING]) ? 1 : 0
        );

        Configuration::updateValue(
            PaypalConfigurations::MERCHANT_INSTALLMENT,
            isset($data[PaypalConfigurations::MERCHANT_INSTALLMENT]) ? 1 : 0
        );

        return true;
    }

    protected function getHelpInfo()
    {
        return Context::getContext()->smarty
            ->assign('isShowCustomerInstruction', $this->method == 'PPP')
            ->fetch(_PS_MODULE_DIR_ . $this->module->name . '/views/templates/admin/_partials/messages/form-help-info/checkout.tpl');
    }
}
