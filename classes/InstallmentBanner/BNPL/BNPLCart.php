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

namespace PaypalAddons\classes\InstallmentBanner\BNPL;

use Configuration;
use Tools;

class BNPLCart extends BNPLAbstract
{
    protected function getTemplatePath()
    {
        return 'module:paypal/views/templates/bnpl/bnpl-cart.tpl';
    }

    protected function getTplVars()
    {
        $environment = ($this->method->isSandbox() ? 'sandbox' : 'live');
        $shop_url = $this->context->link->getBaseLink($this->context->shop->id, true);

        $return = [
            'shop_url' => $shop_url,
            'PayPal_payment_type' => $this->getMethodType(),
            'action_url' => $this->context->link->getModuleLink($this->module->name, 'ScInit', [], true),
            'ec_sc_in_context' => Configuration::get('PAYPAL_EXPRESS_CHECKOUT_IN_CONTEXT'),
            'merchant_id' => Configuration::get('PAYPAL_MERCHANT_ID_' . Tools::strtoupper($environment)),
            'environment' => $environment,
        ];

        return $return;
    }
}
