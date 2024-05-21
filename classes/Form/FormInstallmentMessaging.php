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

if (!defined('_PS_VERSION_')) {
    exit;
}

use Configuration;
use Module;
use PaypalAddons\classes\InstallmentBanner\ConfigurationMap;
use Tools;

class FormInstallmentMessaging implements FormInterface
{
    /** @var \Paypal */
    protected $module;

    protected $className;

    public function __construct()
    {
        $this->module = Module::getInstanceByName('paypal');
        $this->className = 'FormInstallmentMessaging';
    }

    /**
     * @return array
     */
    public function getDescription()
    {
        $fields = [];

        $fields[ConfigurationMap::MESSENGING_CONFIG] = [
            'type' => 'hidden',
            'label' => '',
            'value' => Configuration::get(ConfigurationMap::MESSENGING_CONFIG),
            'name' => ConfigurationMap::MESSENGING_CONFIG,
        ];

        $description = [
            'legend' => [
                'title' => $this->module->l('PayPal Pay Later Messaging', $this->className),
            ],
            'fields' => $fields,
            'submit' => [
                'title' => $this->module->l('Save', $this->className),
                'name' => 'installmentMessengingForm',
            ],
            'id_form' => 'pp_installment_messenging_form',
            'help' => '',
        ];

        return $description;
    }

    /**
     * @return bool
     */
    public function save($data = null)
    {
        if (is_null($data)) {
            $data = Tools::getAllValues();
        }

        $return = true;

        if (empty($data['installmentMessengingForm'])) {
            return $return;
        }

        $config = isset($data[ConfigurationMap::MESSENGING_CONFIG]) ? $data[ConfigurationMap::MESSENGING_CONFIG] : '{}';
        $return &= $this->saveDecodedConf($config);

        $return &= Configuration::updateValue(ConfigurationMap::MESSENGING_CONFIG, $config);

        return $return;
    }

    /**
     * Save decoded configuration returned by messenging configuration
     * Will save if placements are enabled (for retro compatibility with previous version)
     *
     * @param string $config JSON string returned by configurator
     *
     * @return string same string if not an error on decoding part
     */
    private function saveDecodedConf($config)
    {
        $decodedConfig = json_decode($config, true);
        $return = true;
        if ($decodedConfig !== false && empty($decodedConfig) === false) {
            foreach ($decodedConfig as $key => $values) {
                $allConfigMap = ConfigurationMap::getParameterConfMap();
                if (isset($allConfigMap[$key])) {
                    $enabled = isset($values['status']) && $values['status'] == 'enabled';
                    $return &= Configuration::updateValue($allConfigMap[$key], $enabled);
                }
            }
        }

        return $return;
    }
}
