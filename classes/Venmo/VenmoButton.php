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

namespace PaypalAddons\classes\Venmo;

use Context;
use PaypalAddons\classes\AbstractMethodPaypal;

class VenmoButton
{
    protected $context;

    protected $method;

    public function __construct()
    {
        $this->context = Context::getContext();
        $this->method = AbstractMethodPaypal::load();
    }

    public function render()
    {
        $this->context->smarty->assign([
            'JSscripts' => $this->getJSscripts(),
        ]);

        return $this->context->smarty->fetch('module:paypal/views/templates/venmo/venmo-button.tpl');
    }

    protected function getJSscripts()
    {
        $srcLib = $this->method->getUrlJsSdkLib() . '&enable-funding=venmo';

        if (defined('PAYPAL_VENMO_ADD_BUYER_COUNTRY') && PAYPAL_VENMO_ADD_BUYER_COUNTRY) {
            if ($this->method->isSandbox()) {
                $srcLib .= '&buyer-country=US';
            }
        }

        $JSscripts = [
            'tot-paypal-venmo-sdk' => [
                'src' => $srcLib,
                'data-namespace' => 'totVenmoPaypalSdkButtons',
            ],
            'venmo' => [
                'src' => $this->context->shop->getBaseURL(true) . 'modules/paypal/views/js/Venmo.js',
            ],
        ];

        return $JSscripts;
    }
}
