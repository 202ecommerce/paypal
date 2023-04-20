<?php

namespace PaypalAddons\classes\Form;

use Configuration;
use Module;
use PaypalAddons\classes\Shortcut\ShortcutConfiguration;
use Tools;

class ShortcutConfigurationForm implements FormInterface
{
    /** @var \Paypal */
    protected $module;

    public function __construct()
    {
        $this->module = Module::getInstanceByName('paypal');
    }

    public function getDescription()
    {
        $fields = [];

        $fields[ShortcutConfiguration::CUSTOMIZE_STYLE] = [
            'type' => 'switch',
            'label' => $this->module->l('Customize buttons', 'ShortcutConfigurationForm'),
            'name' => ShortcutConfiguration::CUSTOMIZE_STYLE,
            'hint' => $this->module->l('You can customize the display options and styles of PayPal shortcuts. The styles and display options can be changed for each button separately depending on its location (Cart Page / Product pages / Sign up step in checkout).', 'AdminPayPalCustomizeCheckoutController'),
            'values' => [
                [
                    'id' => ShortcutConfiguration::CUSTOMIZE_STYLE . '_ON',
                    'value' => 1,
                    'label' => $this->module->l('Enabled', 'AdminPayPalCustomizeCheckoutController'),
                ],
                [
                    'id' => ShortcutConfiguration::CUSTOMIZE_STYLE . '_OFF',
                    'value' => 0,
                    'label' => $this->module->l('Disabled', 'AdminPayPalCustomizeCheckoutController'),
                ],
            ],
            'value' => Configuration::get(ShortcutConfiguration::CUSTOMIZE_STYLE),
        ];

        $fields['widget_code'] = [
            'type' => 'variable-set',
            'label' => $this->module->l('Widget code', 'AdminPayPalCustomizeCheckoutController'),
            'set' => [
                'widgetCode' => '{widget name=\'paypal\' action=\'paymentshortcut\'}'
            ]
        ];

        if (Configuration::get(ShortcutConfiguration::SHOW_ON_CART_PAGE)) {
            $fields[ShortcutConfiguration::DISPLAY_MODE_CART] = $this->getDisplayModeSelect(ShortcutConfiguration::DISPLAY_MODE_CART);
            $fields[ShortcutConfiguration::CART_PAGE_HOOK] = [
                'type' => 'select',
                'label' => $this->module->l('Display position', 'ShortcutConfigurationForm'),
                'name' => ShortcutConfiguration::CART_PAGE_HOOK,
                'hint' => $this->module->l('By default, PayPal shortcut is displayed on your web site via PrestaShop native hook. If you choose to use PrestaShop widgets, you will be able to copy widget code and insert it wherever you want in the product template.', 'AdminPayPalCustomizeCheckoutController'),
                'options' => [
                    [
                        'value' => ShortcutConfiguration::HOOK_EXPRESS_CHECKOUT,
                        'title' => $this->module->l('displayExpressCheckout (recommended) - This hook adds content to the cart view, in the right sidebar, after the cart totals.', 'AdminPayPalCustomizeCheckoutController'),
                        'preview' => '/modules/paypal/views/img/shortcut-preview/cart-displayExpressCheckout.jpg',
                    ],
                    [
                        'value' => ShortcutConfiguration::HOOK_SHOPPING_CART_FOOTER,
                        'title' => $this->module->l('displayShoppingCartFooter - This hook displays some specific information after the list of products in the shopping cart.', 'AdminPayPalCustomizeCheckoutController'),
                        'preview' => '/modules/paypal/views/img/shortcut-preview/cart-displayShoppingCartFooter.jpg',
                    ],
                    [
                        'value' => ShortcutConfiguration::HOOK_REASSURANCE,
                        'title' => $this->module->l('displayReassurance - This hook displays content in the right sidebar, in the block below the cart total.', 'AdminPayPalCustomizeCheckoutController'),
                        'preview' => '/modules/paypal/views/img/shortcut-preview/cart-displayReassurance.jpg',
                    ],
                ],
                'value' => Configuration::get(ShortcutConfiguration::CART_PAGE_HOOK),
            ];
        }

        if (Configuration::get(ShortcutConfiguration::SHOW_ON_PRODUCT_PAGE)) {
            $fields[ShortcutConfiguration::DISPLAY_MODE_CART] = $this->getDisplayModeSelect(ShortcutConfiguration::DISPLAY_MODE_PRODUCT);

        }
        // TODO: finish method
        return [
            'legend' => [
                'title' => $this->module->l('PayPal Express Checkout shortcut', 'ShortcutConfigurationForm'),
            ],
            'fields' => $fields,
            'submit' => [
                'title' => $this->module->l('Save', 'ShortcutConfigurationForm'),
                'name' => 'SubmitShortcutConfigurationForm',
            ],
            'id_form' => 'pp_shortcut_configuration_form',
        ];
    }

    protected function getDisplayModeSelect($name)
    {
        return [
            'type' => 'select',
            'label' => $this->module->l('Display mode', 'AdminPayPalCustomizeCheckoutController'),
            'name' => $name,
            'hint' => $this->module->l('By default, PayPal shortcut is displayed on your web site via PrestaShop native hook. If you choose to use PrestaShop widgets, you will be able to copy widget code and insert it wherever you want in the product template.', 'AdminPayPalCustomizeCheckoutController'),
            'options' => [
                [
                    'value' => ShortcutConfiguration::DISPLAY_MODE_TYPE_HOOK,
                    'title' => $this->module->l('PrestaShop native hook (recommended)', 'AdminPayPalCustomizeCheckoutController'),
                ],
                [
                    'value' => ShortcutConfiguration::DISPLAY_MODE_TYPE_WIDGET,
                    'title' => $this->module->l('PrestaShop widget', 'AdminPayPalCustomizeCheckoutController'),
                ]
            ],
            'value' => Configuration::get($name),
        ];
    }

    public function save($data = null)
    {
        if (is_null($data)) {
            $data = Tools::getAllValues();
        }

        if (false === isset($data['SubmitShortcutConfigurationForm'])) {
            return false;
        }


        return true;
    }
}
