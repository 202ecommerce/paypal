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

use PaypalAddons\classes\AbstractMethodPaypal;
use PaypalAddons\classes\Constants\PaypalConfigurations;
use PaypalAddons\classes\InstallmentBanner\BNPL\BNPLOption;
use PaypalAddons\classes\InstallmentBanner\ConfigurationMap;
use PaypalAddons\classes\Shortcut\ShortcutConfiguration;
use PaypalAddons\classes\Vaulting\VaultingFunctionality;

if (!defined('_PS_VERSION_')) {
    exit;
}

class FeatureChecklistForm implements FormInterface
{
    protected $bnplOption;

    protected $method;

    protected $module;
    /** @var VaultingFunctionality */
    protected $vaultingFunctionality;

    public function __construct()
    {
        $this->bnplOption = new BNPLOption();
        $this->method = AbstractMethodPaypal::load();
        $this->module = \Module::getInstanceByName('paypal');
        $this->vaultingFunctionality = new VaultingFunctionality();
    }

    /**
     * @return array
     */
    public function getDescription()
    {
        $isoCountryDefault = \Tools::strtolower(\Country::getIsoById((int) \Configuration::get('PS_COUNTRY_DEFAULT')));
        $vars = [];

        if (in_array($isoCountryDefault, ConfigurationMap::getBnplAvailableCountries())) {
            $vars['isBnplEnabled'] = $this->bnplOption->isEnable();
        }

        $vars['isShortcutCustomized'] = (int) \Configuration::get(ShortcutConfiguration::CUSTOMIZE_STYLE);

        if ($this->method instanceof \MethodPPP) {
            $vars['isPuiEnabled'] = (int) \Configuration::get(PaypalConfigurations::PUI_ENABLED);
        }

        $vars['isOrderStatusCustomized'] = (int) \Configuration::get(PaypalConfigurations::CUSTOMIZE_ORDER_STATUS);
        $vars['isShowPaypalBenefits'] = (int) \Configuration::get(PaypalConfigurations::API_ADVANTAGES);

        if (false === in_array(\Tools::strtoupper($isoCountryDefault), $this->module->countriesApiCartUnavailable)) {
            $vars['isCreditCardEnabled'] = $this->isCreditCardEnabled();
        }

        if ($this->vaultingFunctionality->isAvailable()) {
            $vars['isVaultingCapabilityAvailable'] = $this->vaultingFunctionality->isEnabled() && $this->vaultingFunctionality->isCapabilityAvailable();
            $vars['vaultingStatusMessage'] = $this->vaultingFunctionality->getStatusMessage();
        }

        if ($this->method instanceof \MethodMB) {
            if (\Configuration::get(PaypalConfigurations::MERCHANT_INSTALLMENT)) {
                $vars['isInstallmentMbEnabled'] = true;
            } else {
                $vars['isInstallmentMbEnabled'] = false;
            }
        }

        return [
            'legend' => [
                'title' => '',
            ],
            'fields' => [
                'featureChecklist' => [
                    'type' => 'varialble-set',
                    'set' => $vars,
                ],
            ],
            'submit' => [
                'title' => '',
                'name' => '',
            ],
            'id_form' => 'pp_feature_checklist_form',
        ];
    }

    /**
     * @return bool
     */
    public function save($data = null)
    {
        return true;
    }

    protected function isCreditCardEnabled()
    {
        $isoCountryDefault = \Tools::strtoupper(\Country::getIsoById((int) \Configuration::get('PS_COUNTRY_DEFAULT')));

        if ($this->method instanceof \MethodPPP) {
            return (int) \Configuration::get(PaypalConfigurations::ACDC_OPTION);
        } elseif (false === in_array($isoCountryDefault, $this->module->countriesApiCartUnavailable)) {
            return (int) \Configuration::get(PaypalConfigurations::API_CARD);
        }

        return false;
    }
}
