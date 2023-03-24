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

namespace PaypalAddons\services\Core;

use Configuration;

class PaypalMerchantId
{
    const SANDBOX = 'PAYPAL_MERCHANT_ID_SANDBOX';

    const LIVE = 'PAYPAL_MERCHANT_ID_LIVE';

    public function get($sandboxMode = null)
    {
        if ($sandboxMode instanceof SandboxMode == false) {
            $sandboxMode = $this->getSandboxMode();
        }

        if ($sandboxMode->isSandbox()) {
            return Configuration::get(self::SANDBOX);
        } else {
            return Configuration::get(self::LIVE);
        }
    }

    public function set($id, $sandboxMode = null)
    {
        if ($sandboxMode instanceof SandboxMode == false) {
            $sandboxMode = $this->getSandboxMode();
        }

        if ($sandboxMode->isSandbox()) {
            Configuration::updateValue(self::SANDBOX, $id);
        } else {
            Configuration::updateValue(self::LIVE, $id);
        }

        return $this;
    }

    protected function getSandboxMode()
    {
        return new SandboxMode();
    }
}
