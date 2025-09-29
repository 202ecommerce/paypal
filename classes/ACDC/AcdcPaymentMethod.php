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

namespace PaypalAddons\classes\ACDC;

use PaypalAddons\classes\AbstractMethodPaypal;
use PaypalAddons\classes\Constants\PaypalConfigurations;

if (!defined('_PS_VERSION_')) {
    exit;
}

class AcdcPaymentMethod
{
    /** @var \MethodEC|\MethodPPP|\MethodMB */
    protected $method;

    /** @var \Context */
    protected $context;

    public function __construct($method = null)
    {
        if ($method instanceof AbstractMethodPaypal) {
            /* @phpstan-ignore-next-line */
            $this->method = $method;
        } else {
            $this->method = AbstractMethodPaypal::load();
        }

        $this->context = \Context::getContext();
    }

    public function render()
    {
        $tmp = $this->context->smarty->createTemplate($this->getTemplatePath());
        $tmp->assign($this->getTplVars());

        return $tmp->fetch();
    }

    protected function getTemplatePath()
    {
        return 'module:paypal/views/templates/acdc/payment-option.tpl';
    }

    protected function getTplVars()
    {
        $vars = [
            'psPaypalDir' => _PS_MODULE_DIR_ . 'paypal',
            'JSvars' => [
                PaypalConfigurations::MOVE_BUTTON_AT_END => (int) \Configuration::get(PaypalConfigurations::MOVE_BUTTON_AT_END),
            ],
            'JSscripts' => $this->getScripts(),
        ];

        return $vars;
    }

    protected function getScripts()
    {
        $scripts = [];
        $srcLib = $this->method->getUrlJsSdkLib(['components' => 'card-fields,marks']);
        $scripts['tot-paypal-acdc-sdk'] = [
            'src' => $srcLib,
            'data-namespace' => 'totPaypalAcdcSdk',
            'data-partner-attribution-id' => $this->getPartnerId(),
        ];
        $scripts['acdc'] = [
            'src' => __PS_BASE_URI__ . 'modules/paypal/views/js/acdc.js',
        ];

        return $scripts;
    }

    protected function getPartnerId()
    {
        return 'PRESTASHOP_Cart_SPB';
    }

    protected function getClientToken()
    {
        $response = $this->method->acdcGenerateToken();

        if ($response->isSuccess()) {
            return $response->getToken();
        }

        return '';
    }
}
