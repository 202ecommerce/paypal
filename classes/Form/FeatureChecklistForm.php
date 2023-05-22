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

namespace PaypalAddons\classes\Form;

use Configuration;
use Country;
use MethodMB;
use MethodPPP;
use PaypalAddons\classes\AbstractMethodPaypal;
use PaypalAddons\classes\Constants\PaypalConfigurations;
use PaypalAddons\classes\InstallmentBanner\BNPL\BNPLOption;
use PaypalAddons\classes\InstallmentBanner\ConfigurationMap;
use PaypalAddons\classes\Shortcut\ShortcutConfiguration;
use Tools;

class FeatureChecklistForm implements FormInterface
{
    protected $bnplOption;

    protected $method;

    public function __construct()
    {
        $this->bnplOption = new BNPLOption();
        $this->method = AbstractMethodPaypal::load();
    }
    /**
     * @return array
     */
    public function getDescription()
    {
        $isoCountryDefault = Tools::strtolower(Country::getIsoById(Configuration::get('PS_COUNTRY_DEFAULT')));
        $vars = [];

        if (in_array($isoCountryDefault, ConfigurationMap::getBnplAvailableCountries())) {
            $vars['isBnplEnabled'] = $this->bnplOption->isEnable();
        }

        $vars['isShortcutCustomized'] = (int) Configuration::get(ShortcutConfiguration::CUSTOMIZE_STYLE);

        if ($this->method instanceof MethodPPP) {
            $vars['isPuiEnabled'] = (int) Configuration::get(PaypalConfigurations::PUI_ENABLED);
        }

        $vars['isOrderStatusCustomized'] = (int) Configuration::get(PaypalConfigurations::CUSTOMIZE_ORDER_STATUS);
        $vars['isShowPaypalBenefits'] = (int) Configuration::get(PaypalConfigurations::API_ADVANTAGES);
        $vars['isCreditCardEnabled'] = $this->isCreditCardEnabled();

        return [
            'legend' => [
                'title' => '',
            ],
            'fields' => [
                'featureChecklist' => [
                    'type' => 'varialble-set',
                    'set' => $vars
                ]
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
    }

    protected function isCreditCardEnabled()
    {
        if ($this->method instanceof MethodPPP) {
            return (int) Configuration::get(PaypalConfigurations::ACDC_OPTION);
        }

        if ($this->method instanceof MethodMB) {
            return true;
        }

        return false;
    }
}
