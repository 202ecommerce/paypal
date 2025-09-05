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

use PaypalAddons\classes\Shortcut\ShortcutConfiguration;
use PaypalAddons\classes\Shortcut\ShortcutPreview;

if (!defined('_PS_VERSION_')) {
    exit;
}

class ShortcutConfigurationForm implements FormInterface
{
    /** @var \PayPal */
    protected $module;

    public function __construct()
    {
        /* @phpstan-ignore-next-line */
        $this->module = \Module::getInstanceByName('paypal');
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
            'value' => \Configuration::get(ShortcutConfiguration::CUSTOMIZE_STYLE),
        ];

        if (\Configuration::get(ShortcutConfiguration::SHOW_ON_CART_PAGE)) {
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
                'value' => \Configuration::get(ShortcutConfiguration::CART_PAGE_HOOK),
            ];
            $fields[ShortcutConfiguration::STYLE_COLOR_CART] = $this->getButtonColorSelect(ShortcutConfiguration::STYLE_COLOR_CART);
            $fields[ShortcutConfiguration::STYLE_SHAPE_CART] = $this->getButtonShapeSelect(ShortcutConfiguration::STYLE_SHAPE_CART);
            $fields[ShortcutConfiguration::STYLE_HEIGHT_CART] = [
                'type' => 'text',
                'label' => $this->module->l('Size', 'AdminPayPalCustomizeCheckoutController'),
                'name' => ShortcutConfiguration::STYLE_HEIGHT_CART,
                'placeholder' => $this->module->l('value from 25 to 55', 'AdminPayPalCustomizeCheckoutController'),
                'required' => true,
                'value' => ((int) \Configuration::get(ShortcutConfiguration::STYLE_HEIGHT_CART) ? (int) \Configuration::get(ShortcutConfiguration::STYLE_HEIGHT_CART) : 35),
                'data_type' => 'height',
                'message_error' => $this->module->l('Value from 25 to 55 is required'),
            ];
            $fields[ShortcutConfiguration::STYLE_WIDTH_CART] = [
                'type' => 'text',
                'label' => $this->module->l('Size', 'AdminPayPalCustomizeCheckoutController'),
                'name' => ShortcutConfiguration::STYLE_WIDTH_CART,
                'placeholder' => $this->module->l('min 150', 'AdminPayPalCustomizeCheckoutController'),
                'required' => true,
                'value' => ((int) \Configuration::get(ShortcutConfiguration::STYLE_WIDTH_CART) ? (int) \Configuration::get(ShortcutConfiguration::STYLE_WIDTH_CART) : 200),
                'data_type' => 'width',
                'message_error' => $this->module->l('Minimum 150 px is required'),
            ];
            $fields[ShortcutConfiguration::STYLE_LABEL_CART] = $this->getButtonLabelSelect(ShortcutConfiguration::STYLE_LABEL_CART);
            $fields['cart_shortcut_preview'] = [
                'type' => 'variable-set',
                'set' => [
                    'shortcut' => (new ShortcutPreview(
                        \Configuration::get(ShortcutConfiguration::STYLE_LABEL_CART) ? \Configuration::get(ShortcutConfiguration::STYLE_LABEL_CART) : ShortcutConfiguration::STYLE_LABEL_CHECKOUT,
                        (int) \Configuration::get(ShortcutConfiguration::STYLE_HEIGHT_CART) ? (int) \Configuration::get(ShortcutConfiguration::STYLE_HEIGHT_CART) : 35,
                        (int) \Configuration::get(ShortcutConfiguration::STYLE_WIDTH_CART) ? (int) \Configuration::get(ShortcutConfiguration::STYLE_WIDTH_CART) : 200,
                        \Configuration::get(ShortcutConfiguration::STYLE_COLOR_CART) ? \Configuration::get(ShortcutConfiguration::STYLE_COLOR_CART) : ShortcutConfiguration::STYLE_COLOR_GOLD,
                        \Configuration::get(ShortcutConfiguration::STYLE_SHAPE_CART) ? \Configuration::get(ShortcutConfiguration::STYLE_SHAPE_CART) : ShortcutConfiguration::STYLE_SHAPE_RECT
                    ))->render(),
                ],
            ];
        }

        if (\Configuration::get(ShortcutConfiguration::SHOW_ON_PRODUCT_PAGE)) {
            $fields[ShortcutConfiguration::DISPLAY_MODE_PRODUCT] = $this->getDisplayModeSelect(ShortcutConfiguration::DISPLAY_MODE_PRODUCT);
            $hooks = [];

            if (version_compare(_PS_VERSION_, '1.7.6', '>=')) {
                $hooks[] = [
                    'value' => ShortcutConfiguration::HOOK_PRODUCT_ACTIONS,
                    'title' => $this->module->l('displayProductActions (recommended) - This hook allows additional actions to be triggered, near the add to cart button.', 'AdminPayPalCustomizeCheckoutController'),
                    'preview' => '/modules/paypal/views/img/shortcut-preview/product-displayProductActions.jpg',
                ];

                $hooks[] = [
                    'value' => ShortcutConfiguration::HOOK_REASSURANCE,
                    'title' => $this->module->l('displayReassurance - This hook adds new elements just next to the reassurance block.', 'AdminPayPalCustomizeCheckoutController'),
                    'preview' => '/modules/paypal/views/img/shortcut-preview/product-displayReassurance.jpg',
                ];
            } else {
                $hooks[] = [
                    'value' => ShortcutConfiguration::HOOK_REASSURANCE,
                    'title' => $this->module->l('displayReassurance - This hook adds new elements just next to the reassurance block.', 'AdminPayPalCustomizeCheckoutController'),
                    'preview' => '/modules/paypal/views/img/shortcut-preview/product-displayReassurance.jpg',
                ];
            }

            if (version_compare(_PS_VERSION_, '1.7.1', '>=')) {
                $hooks[] = [
                    'value' => ShortcutConfiguration::HOOK_AFTER_PRODUCT_ADDITIONAL_INFO,
                    'title' => $this->module->l('displayProductAdditionalInfo - This hook adds additional information before the reassurance block and product description.'),
                    'preview' => '/modules/paypal/views/img/shortcut-preview/product-displayProductAdditionalInfo.jpg',
                ];

                $hooks[] = [
                    'value' => ShortcutConfiguration::HOOK_AFTER_PRODUCT_THUMBS,
                    'title' => $this->module->l('displayAfterProductThumbs - This hook displays new elements below product images.'),
                    'preview' => '/modules/paypal/views/img/shortcut-preview/product-displayAfterProductThumbs.jpg',
                ];
            }

            $hooks[] = [
                'value' => ShortcutConfiguration::HOOK_FOOTER_PRODUCT,
                'title' => $this->module->l('displayFooterProduct - This hook adds new blocks on the product page just before global site footer.'),
                'preview' => '/modules/paypal/views/img/shortcut-preview/product-displayProductFooter.jpg',
            ];

            $fields[ShortcutConfiguration::PRODUCT_PAGE_HOOK] = [
                'type' => 'select',
                'label' => $this->module->l('Display position', 'ShortcutConfigurationForm'),
                'name' => ShortcutConfiguration::PRODUCT_PAGE_HOOK,
                'hint' => $this->module->l('By default, PayPal shortcut is displayed on your web site via PrestaShop native hook. If you choose to use PrestaShop widgets, you will be able to copy widget code and insert it wherever you want in the product template.', 'AdminPayPalCustomizeCheckoutController'),
                'options' => $hooks,
                'value' => \Configuration::get(ShortcutConfiguration::PRODUCT_PAGE_HOOK),
            ];
            $fields[ShortcutConfiguration::STYLE_COLOR_PRODUCT] = $this->getButtonColorSelect(ShortcutConfiguration::STYLE_COLOR_PRODUCT);
            $fields[ShortcutConfiguration::STYLE_SHAPE_PRODUCT] = $this->getButtonShapeSelect(ShortcutConfiguration::STYLE_SHAPE_PRODUCT);
            $fields[ShortcutConfiguration::STYLE_HEIGHT_PRODUCT] = [
                'type' => 'text',
                'label' => $this->module->l('Size', 'AdminPayPalCustomizeCheckoutController'),
                'name' => ShortcutConfiguration::STYLE_HEIGHT_PRODUCT,
                'placeholder' => $this->module->l('Height (value from 25 to 55)', 'AdminPayPalCustomizeCheckoutController'),
                'required' => true,
                'value' => ((int) \Configuration::get(ShortcutConfiguration::STYLE_HEIGHT_PRODUCT) ? (int) \Configuration::get(ShortcutConfiguration::STYLE_HEIGHT_PRODUCT) : 35),
            ];
            $fields[ShortcutConfiguration::STYLE_WIDTH_PRODUCT] = [
                'type' => 'text',
                'label' => $this->module->l('Size', 'AdminPayPalCustomizeCheckoutController'),
                'name' => ShortcutConfiguration::STYLE_WIDTH_PRODUCT,
                'placeholder' => $this->module->l('Width (min 150)', 'AdminPayPalCustomizeCheckoutController'),
                'required' => true,
                'value' => ((int) \Configuration::get(ShortcutConfiguration::STYLE_WIDTH_PRODUCT) ? (int) \Configuration::get(ShortcutConfiguration::STYLE_WIDTH_PRODUCT) : 200),
            ];
            $fields[ShortcutConfiguration::STYLE_LABEL_PRODUCT] = $this->getButtonLabelSelect(ShortcutConfiguration::STYLE_LABEL_PRODUCT);
            $fields['product_shortcut_preview'] = [
                'type' => 'variable-set',
                'set' => [
                    'shortcut' => (new ShortcutPreview(
                        \Configuration::get(ShortcutConfiguration::STYLE_LABEL_PRODUCT) ? \Configuration::get(ShortcutConfiguration::STYLE_LABEL_PRODUCT) : ShortcutConfiguration::STYLE_LABEL_BUYNOW,
                        (int) \Configuration::get(ShortcutConfiguration::STYLE_HEIGHT_PRODUCT) ? (int) \Configuration::get(ShortcutConfiguration::STYLE_HEIGHT_PRODUCT) : 35,
                        (int) \Configuration::get(ShortcutConfiguration::STYLE_WIDTH_PRODUCT) ? (int) \Configuration::get(ShortcutConfiguration::STYLE_WIDTH_PRODUCT) : 200,
                        \Configuration::get(ShortcutConfiguration::STYLE_COLOR_PRODUCT) ? \Configuration::get(ShortcutConfiguration::STYLE_COLOR_PRODUCT) : ShortcutConfiguration::STYLE_COLOR_GOLD,
                        \Configuration::get(ShortcutConfiguration::STYLE_SHAPE_PRODUCT) ? \Configuration::get(ShortcutConfiguration::STYLE_SHAPE_PRODUCT) : ShortcutConfiguration::STYLE_SHAPE_RECT
                    ))->render(),
                ],
            ];
        }

        if (\Configuration::get(ShortcutConfiguration::SHOW_ON_SIGNUP_STEP)) {
            $fields[ShortcutConfiguration::DISPLAY_MODE_SIGNUP] = $this->getDisplayModeSelect(ShortcutConfiguration::DISPLAY_MODE_SIGNUP);
            $fields[ShortcutConfiguration::STYLE_COLOR_SIGNUP] = $this->getButtonColorSelect(ShortcutConfiguration::STYLE_COLOR_SIGNUP);
            $fields[ShortcutConfiguration::STYLE_SHAPE_SIGNUP] = $this->getButtonShapeSelect(ShortcutConfiguration::STYLE_SHAPE_SIGNUP);
            $fields[ShortcutConfiguration::STYLE_HEIGHT_SIGNUP] = [
                'type' => 'text',
                'label' => $this->module->l('Size', 'AdminPayPalCustomizeCheckoutController'),
                'name' => ShortcutConfiguration::STYLE_HEIGHT_SIGNUP,
                'placeholder' => $this->module->l('Height (value from 25 to 55)', 'AdminPayPalCustomizeCheckoutController'),
                'required' => true,
                'value' => ((int) \Configuration::get(ShortcutConfiguration::STYLE_HEIGHT_SIGNUP) ? (int) \Configuration::get(ShortcutConfiguration::STYLE_HEIGHT_SIGNUP) : 35),
            ];
            $fields[ShortcutConfiguration::STYLE_WIDTH_SIGNUP] = [
                'type' => 'text',
                'label' => $this->module->l('Size', 'AdminPayPalCustomizeCheckoutController'),
                'name' => ShortcutConfiguration::STYLE_WIDTH_SIGNUP,
                'placeholder' => $this->module->l('Width (min 150)', 'AdminPayPalCustomizeCheckoutController'),
                'required' => true,
                'value' => ((int) \Configuration::get(ShortcutConfiguration::STYLE_WIDTH_SIGNUP) ? (int) \Configuration::get(ShortcutConfiguration::STYLE_WIDTH_SIGNUP) : 200),
            ];
            $fields[ShortcutConfiguration::STYLE_LABEL_SIGNUP] = $this->getButtonLabelSelect(ShortcutConfiguration::STYLE_LABEL_SIGNUP);
            $fields['signup_shortcut_preview'] = [
                'type' => 'variable-set',
                'set' => [
                    'shortcut' => (new ShortcutPreview(
                        \Configuration::get(ShortcutConfiguration::STYLE_LABEL_SIGNUP) ? \Configuration::get(ShortcutConfiguration::STYLE_LABEL_SIGNUP) : ShortcutConfiguration::STYLE_LABEL_CHECKOUT,
                        (int) \Configuration::get(ShortcutConfiguration::STYLE_HEIGHT_SIGNUP) ? (int) \Configuration::get(ShortcutConfiguration::STYLE_HEIGHT_SIGNUP) : 35,
                        (int) \Configuration::get(ShortcutConfiguration::STYLE_WIDTH_SIGNUP) ? (int) \Configuration::get(ShortcutConfiguration::STYLE_WIDTH_SIGNUP) : 200,
                        \Configuration::get(ShortcutConfiguration::STYLE_COLOR_SIGNUP) ? \Configuration::get(ShortcutConfiguration::STYLE_COLOR_SIGNUP) : ShortcutConfiguration::STYLE_COLOR_GOLD,
                        \Configuration::get(ShortcutConfiguration::STYLE_SHAPE_SIGNUP) ? \Configuration::get(ShortcutConfiguration::STYLE_SHAPE_SIGNUP) : ShortcutConfiguration::STYLE_SHAPE_RECT
                    ))->render(),
                ],
            ];
        }

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
            'help' => $this->getHelpInfo(),
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
                ],
            ],
            'value' => \Configuration::get($name),
        ];
    }

    public function save($data = null)
    {
        if (is_null($data)) {
            $data = \Tools::getAllValues();
        }

        if (false === isset($data['SubmitShortcutConfigurationForm'])) {
            return false;
        }

        \Configuration::updateValue(
            ShortcutConfiguration::CUSTOMIZE_STYLE,
            isset($data[ShortcutConfiguration::CUSTOMIZE_STYLE]) ? 1 : 0
        );

        // Cart page button settings
        if (isset($data[ShortcutConfiguration::DISPLAY_MODE_CART])) {
            \Configuration::updateValue(
                ShortcutConfiguration::DISPLAY_MODE_CART,
                (int) $data[ShortcutConfiguration::DISPLAY_MODE_CART]
            );
        }
        if (isset($data[ShortcutConfiguration::CART_PAGE_HOOK])) {
            \Configuration::updateValue(
                ShortcutConfiguration::CART_PAGE_HOOK,
                pSQL($data[ShortcutConfiguration::CART_PAGE_HOOK])
            );
        }
        if (isset($data[ShortcutConfiguration::STYLE_COLOR_CART])) {
            \Configuration::updateValue(
                ShortcutConfiguration::STYLE_COLOR_CART,
                pSQL($data[ShortcutConfiguration::STYLE_COLOR_CART])
            );
        }
        if (isset($data[ShortcutConfiguration::STYLE_SHAPE_CART])) {
            \Configuration::updateValue(
                ShortcutConfiguration::STYLE_SHAPE_CART,
                pSQL($data[ShortcutConfiguration::STYLE_SHAPE_CART])
            );
        }
        if (isset($data[ShortcutConfiguration::STYLE_HEIGHT_CART])) {
            if ($data[ShortcutConfiguration::STYLE_HEIGHT_CART] > 55) {
                $data[ShortcutConfiguration::STYLE_HEIGHT_CART] = 55;
            } elseif ($data[ShortcutConfiguration::STYLE_HEIGHT_CART] < 25) {
                $data[ShortcutConfiguration::STYLE_HEIGHT_CART] = 25;
            }
            \Configuration::updateValue(
                ShortcutConfiguration::STYLE_HEIGHT_CART,
                (int) $data[ShortcutConfiguration::STYLE_HEIGHT_CART]
            );
        }
        if (isset($data[ShortcutConfiguration::STYLE_WIDTH_CART])) {
            if ($data[ShortcutConfiguration::STYLE_WIDTH_CART] < 150) {
                $data[ShortcutConfiguration::STYLE_WIDTH_CART] = 150;
            }
            \Configuration::updateValue(
                ShortcutConfiguration::STYLE_WIDTH_CART,
                (int) $data[ShortcutConfiguration::STYLE_WIDTH_CART]
            );
        }
        if (isset($data[ShortcutConfiguration::STYLE_LABEL_CART])) {
            \Configuration::updateValue(
                ShortcutConfiguration::STYLE_LABEL_CART,
                pSQL($data[ShortcutConfiguration::STYLE_LABEL_CART])
            );
        }

        // Product page button settings
        if (isset($data[ShortcutConfiguration::DISPLAY_MODE_PRODUCT])) {
            \Configuration::updateValue(
                ShortcutConfiguration::DISPLAY_MODE_PRODUCT,
                (int) $data[ShortcutConfiguration::DISPLAY_MODE_PRODUCT]
            );
        }
        if (isset($data[ShortcutConfiguration::PRODUCT_PAGE_HOOK])) {
            \Configuration::updateValue(
                ShortcutConfiguration::PRODUCT_PAGE_HOOK,
                pSQL($data[ShortcutConfiguration::PRODUCT_PAGE_HOOK])
            );
        }
        if (isset($data[ShortcutConfiguration::STYLE_COLOR_PRODUCT])) {
            \Configuration::updateValue(
                ShortcutConfiguration::STYLE_COLOR_PRODUCT,
                pSQL($data[ShortcutConfiguration::STYLE_COLOR_PRODUCT])
            );
        }
        if (isset($data[ShortcutConfiguration::STYLE_SHAPE_PRODUCT])) {
            \Configuration::updateValue(
                ShortcutConfiguration::STYLE_SHAPE_PRODUCT,
                pSQL($data[ShortcutConfiguration::STYLE_SHAPE_PRODUCT])
            );
        }
        if (isset($data[ShortcutConfiguration::STYLE_HEIGHT_PRODUCT])) {
            if ($data[ShortcutConfiguration::STYLE_HEIGHT_PRODUCT] > 55) {
                $data[ShortcutConfiguration::STYLE_HEIGHT_PRODUCT] = 55;
            } elseif ($data[ShortcutConfiguration::STYLE_HEIGHT_PRODUCT] < 25) {
                $data[ShortcutConfiguration::STYLE_HEIGHT_PRODUCT] = 25;
            }
            \Configuration::updateValue(
                ShortcutConfiguration::STYLE_HEIGHT_PRODUCT,
                (int) $data[ShortcutConfiguration::STYLE_HEIGHT_PRODUCT]
            );
        }
        if (isset($data[ShortcutConfiguration::STYLE_WIDTH_PRODUCT])) {
            if ($data[ShortcutConfiguration::STYLE_WIDTH_PRODUCT] < 150) {
                $data[ShortcutConfiguration::STYLE_WIDTH_PRODUCT] = 150;
            }
            \Configuration::updateValue(
                ShortcutConfiguration::STYLE_WIDTH_PRODUCT,
                (int) $data[ShortcutConfiguration::STYLE_WIDTH_PRODUCT]
            );
        }
        if (isset($data[ShortcutConfiguration::STYLE_LABEL_PRODUCT])) {
            \Configuration::updateValue(
                ShortcutConfiguration::STYLE_LABEL_PRODUCT,
                pSQL($data[ShortcutConfiguration::STYLE_LABEL_PRODUCT])
            );
        }

        // Signup page button settings
        if (isset($data[ShortcutConfiguration::DISPLAY_MODE_SIGNUP])) {
            \Configuration::updateValue(
                ShortcutConfiguration::DISPLAY_MODE_SIGNUP,
                (int) $data[ShortcutConfiguration::DISPLAY_MODE_SIGNUP]
            );
        }
        if (isset($data[ShortcutConfiguration::STYLE_COLOR_SIGNUP])) {
            \Configuration::updateValue(
                ShortcutConfiguration::STYLE_COLOR_SIGNUP,
                pSQL($data[ShortcutConfiguration::STYLE_COLOR_SIGNUP])
            );
        }
        if (isset($data[ShortcutConfiguration::STYLE_SHAPE_SIGNUP])) {
            \Configuration::updateValue(
                ShortcutConfiguration::STYLE_SHAPE_SIGNUP,
                pSQL($data[ShortcutConfiguration::STYLE_SHAPE_SIGNUP])
            );
        }
        if (isset($data[ShortcutConfiguration::STYLE_HEIGHT_SIGNUP])) {
            if ($data[ShortcutConfiguration::STYLE_HEIGHT_SIGNUP] > 55) {
                $data[ShortcutConfiguration::STYLE_HEIGHT_SIGNUP] = 55;
            } elseif ($data[ShortcutConfiguration::STYLE_HEIGHT_SIGNUP] < 25) {
                $data[ShortcutConfiguration::STYLE_HEIGHT_SIGNUP] = 25;
            }
            \Configuration::updateValue(
                ShortcutConfiguration::STYLE_HEIGHT_SIGNUP,
                (int) $data[ShortcutConfiguration::STYLE_HEIGHT_SIGNUP]
            );
        }
        if (isset($data[ShortcutConfiguration::STYLE_WIDTH_SIGNUP])) {
            if ($data[ShortcutConfiguration::STYLE_WIDTH_SIGNUP] < 150) {
                $data[ShortcutConfiguration::STYLE_WIDTH_SIGNUP] = 150;
            }
            \Configuration::updateValue(
                ShortcutConfiguration::STYLE_WIDTH_SIGNUP,
                (int) $data[ShortcutConfiguration::STYLE_WIDTH_SIGNUP]
            );
        }
        if (isset($data[ShortcutConfiguration::STYLE_LABEL_SIGNUP])) {
            \Configuration::updateValue(
                ShortcutConfiguration::STYLE_LABEL_SIGNUP,
                pSQL($data[ShortcutConfiguration::STYLE_LABEL_SIGNUP])
            );
        }

        return true;
    }

    protected function getButtonColorSelect($name)
    {
        return [
            'type' => 'select',
            'label' => $this->module->l('Color', 'AdminPayPalCustomizeCheckoutController'),
            'name' => $name,
            'options' => [
                [
                    'value' => ShortcutConfiguration::STYLE_COLOR_GOLD,
                    'title' => $this->module->l('Gold (recommended)', 'AdminPayPalCustomizeCheckoutController'),
                ],
                [
                    'value' => ShortcutConfiguration::STYLE_COLOR_BLUE,
                    'title' => $this->module->l('Blue (first alternative)', 'AdminPayPalCustomizeCheckoutController'),
                ],
                [
                    'value' => ShortcutConfiguration::STYLE_COLOR_SILVER,
                    'title' => $this->module->l('Silver (second alternative)', 'AdminPayPalCustomizeCheckoutController'),
                ],
                [
                    'value' => ShortcutConfiguration::STYLE_COLOR_WHITE,
                    'title' => $this->module->l('White (second alternative)', 'AdminPayPalCustomizeCheckoutController'),
                ],
                [
                    'value' => ShortcutConfiguration::STYLE_COLOR_BLACK,
                    'title' => $this->module->l('Black (second alternative)', 'AdminPayPalCustomizeCheckoutController'),
                ],
            ],
            'value' => \Configuration::get($name),
            'data_type' => 'color',
        ];
    }

    protected function getButtonShapeSelect($name)
    {
        return [
            'type' => 'select',
            'label' => $this->module->l('Shape', 'AdminPayPalCustomizeCheckoutController'),
            'name' => $name,
            'options' => [
                [
                    'value' => ShortcutConfiguration::STYLE_SHAPE_RECT,
                    'title' => $this->module->l('Rectangle (recommended) - default button shape', 'AdminPayPalCustomizeCheckoutController'),
                ],
                [
                    'value' => ShortcutConfiguration::STYLE_SHAPE_PILL,
                    'title' => $this->module->l('Pill - secondary button shape option', 'AdminPayPalCustomizeCheckoutController'),
                ],
            ],
            'value' => \Configuration::get($name),
            'data_type' => 'shape',
        ];
    }

    protected function getButtonLabelSelect($name)
    {
        return [
            'type' => 'select',
            'label' => $this->module->l('Button', 'ShortcutConfigurationForm'),
            'name' => $name,
            'options' => [
                [
                    'value' => ShortcutConfiguration::STYLE_LABEL_BUYNOW,
                    'title' => ($name === ShortcutConfiguration::STYLE_LABEL_PRODUCT ? $this->module->l('PayPal Buy Now button (recommended)', 'AdminPayPalCustomizeCheckoutController') : $this->module->l('PayPal Buy Now button', 'AdminPayPalCustomizeCheckoutController')),
                ],
                [
                    'value' => ShortcutConfiguration::STYLE_LABEL_PAYPAL,
                    'title' => $this->module->l('PayPal logo', 'AdminPayPalCustomizeCheckoutController'),
                ],
                [
                    'value' => ShortcutConfiguration::STYLE_LABEL_CHECKOUT,
                    'title' => ($name === ShortcutConfiguration::STYLE_LABEL_PRODUCT ? $this->module->l('Checkout button', 'AdminPayPalCustomizeCheckoutController') : $this->module->l('Checkout button (recomended)', 'AdminPayPalCustomizeCheckoutController')),
                ],
                [
                    'value' => ShortcutConfiguration::STYLE_LABEL_PAY,
                    'title' => $this->module->l('Pay With PayPal button', 'AdminPayPalCustomizeCheckoutController'),
                ],
            ],
            'value' => \Configuration::get($name),
            'data_type' => 'label',
        ];
    }

    protected function getHelpInfo()
    {
        return \Context::getContext()->smarty->fetch(_PS_MODULE_DIR_ . $this->module->name . '/views/templates/admin/_partials/messages/form-help-info/shortcut-configuration.tpl');
    }
}
