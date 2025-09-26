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
use PaypalAddons\classes\InstallmentBanner\BuyerCountry;
use PaypalAddons\classes\InstallmentBanner\ConfigurationMap;

class FormInstallmentMessaging implements FormInterface
{
    /** @var \PayPal */
    protected $module;

    protected $className;
    /** @var BuyerCountry */
    protected $buyerCountry;

    public function __construct()
    {
        /* @phpstan-ignore-next-line */
        $this->module = \Module::getInstanceByName('paypal');
        $this->className = 'FormInstallmentMessaging';
        $this->buyerCountry = new BuyerCountry();
    }

    protected function getBuyerCountryOptions()
    {
        $options = [];

        foreach (ConfigurationMap::getAllowedCountries() as $iso) {
            $options[] = [
                'value' => strtolower($iso),
                'title' => \Country::getNameById(\Context::getContext()->language->id, \Country::getByIso($iso)),
            ];
        }

        return $options;
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
            'value' => \Configuration::get(ConfigurationMap::MESSENGING_CONFIG),
            'name' => ConfigurationMap::MESSENGING_CONFIG,
        ];
        $fields[ConfigurationMap::MESSAGING_BUYER_COUNTRY] = [
            'type' => 'select',
            'label' => $this->module->l('Buyer country', $this->className),
            'value' => $this->buyerCountry->get(),
            'name' => ConfigurationMap::MESSAGING_BUYER_COUNTRY,
            'variant' => 'primary',
            'options' => $this->getBuyerCountryOptions(),
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
            $data = \Tools::getAllValues();
        }

        $return = true;

        if (empty($data['installmentMessengingForm'])) {
            return $return;
        }

        $config = isset($data[ConfigurationMap::MESSENGING_CONFIG]) ? $data[ConfigurationMap::MESSENGING_CONFIG] : '{}';
        $return &= $this->saveDecodedConf($config);

        $return &= \Configuration::updateValue(ConfigurationMap::MESSENGING_CONFIG, $config);

        if (isset($data[ConfigurationMap::MESSAGING_BUYER_COUNTRY])) {
            $this->buyerCountry->set($data[ConfigurationMap::MESSAGING_BUYER_COUNTRY]);
        }

        return (bool) $return;
    }

    /**
     * Save decoded configuration returned by messenging configuration
     * Will save if placements are enabled (for retro compatibility with previous version)
     *
     * @param string $config JSON string returned by configurator
     *
     * @return bool
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
                    $return &= \Configuration::updateValue($allConfigMap[$key], $enabled);
                }
            }
        }

        return (bool) $return;
    }
}
