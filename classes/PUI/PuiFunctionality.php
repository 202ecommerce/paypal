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

namespace PaypalAddons\classes\PUI;

use Address;
use Configuration;
use Context;
use Country;
use PaypalAddons\classes\AbstractMethodPaypal;
use PaypalAddons\classes\Constants\PUI;
use Tools;

class PuiFunctionality
{
    protected $method;

    public function __construct()
    {
        $this->method = AbstractMethodPaypal::load();
    }

    public function isAvailable($refresh = true)
    {
        $isAvailable = (int) Configuration::get(PUI::CONFIGURATION_IS_AVAILABLE);

        if ($refresh == false && in_array($isAvailable, [PUI::IS_AVAILABLE, PUI::IS_UNAVAILABLE])) {
            return $isAvailable == PUI::IS_AVAILABLE;
        }

        $sellerStatus = $this->method->getSellerStatus();

        if ($sellerStatus->isSuccess() == false) {
            Configuration::updateValue(PUI::CONFIGURATION_IS_AVAILABLE, PUI::IS_UNAVAILABLE);

            return false;
        }

        if (empty($sellerStatus->getCapabilities())) {
            Configuration::updateValue(PUI::CONFIGURATION_IS_AVAILABLE, PUI::IS_UNAVAILABLE);

            return false;
        }

        foreach ($sellerStatus->getCapabilities() as $capability) {
            if (Tools::strtoupper($capability) == 'PAY_UPON_INVOICE') {
                Configuration::updateValue(PUI::CONFIGURATION_IS_AVAILABLE, PUI::IS_AVAILABLE);

                return true;
            }
        }

        Configuration::updateValue(PUI::CONFIGURATION_IS_AVAILABLE, PUI::IS_UNAVAILABLE);

        return false;
    }

    public function isEligibleContext(Context $context)
    {
        if (empty($context->cart->id_address_delivery)) {
            return false;
        }

        $address = new Address($context->cart->id_address_delivery);

        return 'de' == Tools::strtolower(Country::getIsoById($address->id_country));
    }
}
