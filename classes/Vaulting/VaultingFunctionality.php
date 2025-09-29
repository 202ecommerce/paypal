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

namespace PaypalAddons\classes\Vaulting;

use PaypalAddons\classes\AbstractMethodPaypal;
use PaypalAddons\classes\API\Response\ResponseGetSellerStatus;
use PaypalAddons\classes\Constants\PaypalConfigurations;
use PaypalAddons\classes\Constants\Vaulting;

if (!defined('_PS_VERSION_')) {
    exit;
}

class VaultingFunctionality
{
    /** @var \MethodEC */
    protected $method;

    protected $_cache = [];

    protected $module;

    public function __construct()
    {
        $this->method = AbstractMethodPaypal::load('EC');
        $this->module = \Module::getInstanceByName('paypal');
    }

    public function isAvailable()
    {
        $iso = \Country::getIsoById((int) \Configuration::get('PS_COUNTRY_DEFAULT'));

        return \Tools::strtolower($iso) == 'us';
    }

    public function isEnabled()
    {
        return Vaulting::ENABLED === (int) \Configuration::get(PaypalConfigurations::ACCOUNT_VAULTING) && (int) \Configuration::get(PaypalConfigurations::EXPRESS_CHECKOUT_IN_CONTEXT);
    }

    public function enable($state)
    {
        \Configuration::updateValue(PaypalConfigurations::ACCOUNT_VAULTING, (int) $state);

        return $this;
    }

    public function isCapabilityAvailable($refresh = true)
    {
        if ($this->method->isSandbox()) {
            if (false === defined('_PS_ADMIN_DIR_')) {
                return true;
            }
        }

        $isAvailable = (int) \Configuration::get(Vaulting::ACCOUNT_VAULTING_STATE);

        if ($refresh == false && in_array($isAvailable, [Vaulting::IS_AVAILABLE, Vaulting::IS_UNAVAILABLE])) {
            return $isAvailable == Vaulting::IS_AVAILABLE;
        }

        if (empty($this->_cache['sellerStatus'])) {
            $this->_cache['sellerStatus'] = $this->method->getSellerStatus();
        }

        $sellerStatus = $this->_cache['sellerStatus'];

        if ($sellerStatus->isSuccess() == false) {
            \Configuration::updateValue(Vaulting::ACCOUNT_VAULTING_STATE, Vaulting::IS_UNAVAILABLE);

            return false;
        }

        if (empty($sellerStatus->getCapabilities())) {
            \Configuration::updateValue(Vaulting::ACCOUNT_VAULTING_STATE, Vaulting::IS_UNAVAILABLE);

            return false;
        }

        foreach ($sellerStatus->getCapabilities() as $capability) {
            if (\Tools::strtoupper($capability) == Vaulting::CAPABILITY) {
                \Configuration::updateValue(Vaulting::ACCOUNT_VAULTING_STATE, Vaulting::IS_AVAILABLE);

                return true;
            }
        }

        \Configuration::updateValue(Vaulting::ACCOUNT_VAULTING_STATE, Vaulting::IS_UNAVAILABLE);

        return false;
    }

    public function getStatusMessage()
    {
        if ($this->isEnabled() === false) {
            return $this->module->l('PayPal account vaulting/ save payments disabled', 'VaultingFunctionality');
        }

        if (empty($this->_cache['sellerStatus'])) {
            $this->_cache['sellerStatus'] = $this->method->getSellerStatus();
        }
        /** @var ResponseGetSellerStatus $sellerStatus */
        $sellerStatus = $this->_cache['sellerStatus'];
        $defaultMessage = $this->module->l('PayPal account vaulting/save payments disabled : Please contact PayPal to activate this feature', 'VaultingFunctionality');

        if ($sellerStatus->isSuccess() == false) {
            return $defaultMessage;
        }

        foreach ($sellerStatus->getCapabilitiesFull() as $capability) {
            if ($capability['name'] !== Vaulting::CAPABILITY) {
                continue;
            }

            $status = \Tools::strtoupper($capability['status']);

            if (in_array($status, [Vaulting::PRODUCT_STATUS_ACTIVE, Vaulting::PRODUCT_STATUS_APPROVED, Vaulting::PRODUCT_STATUS_SUBSCRIBED])) {
                return $this->module->l('PayPal account vaulting/save payments enabled', 'VaultingFunctionality');
            }
            if (in_array($status, [Vaulting::PRODUCT_STATUS_NEED_MORE_DATA, Vaulting::PRODUCT_STATUS_NEED_DATA, Vaulting::PRODUCT_STATUS_PENDING, Vaulting::PRODUCT_STATUS_IN_REVIEW])) {
                return $this->module->l('PayPal account vaulting/ save payments capability is pending PayPal approval, click [b]here[/b] to update status. PayPal may contact you by email if more information is needed.', 'VaultingFunctionality');
            }
            if (in_array($status, [Vaulting::PRODUCT_STATUS_DECLINED, Vaulting::PRODUCT_STATUS_DENIED, Vaulting::PRODUCT_STATUS_REFUSED])) {
                return $this->module->l('PayPal account vaulting/ save payments capability has been refused by PayPal, you could re-apply within 90 days by disconnect-reconnect your PayPal account', 'VaultingFunctionality');
            }
        }

        return $defaultMessage;
    }
}
