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

use PaypalAddons\classes\Constants\PaypalConfigurations;
use PaypalAddons\classes\PrestaShopCloudSync\CloudSyncView;
use PaypalPPBTlib\Extensions\ProcessLogger\ProcessLoggerHandler;
use PrestaShop\PrestaShop\Adapter\Configuration;
use PrestaShop\PrestaShop\Core\ConfigurationInterface;

if (!defined('_PS_VERSION_')) {
    exit;
}

class PrestaShopCloudSyncForm implements FormInterface
{
    protected $module;

    protected $className;

    protected $context;
    /** @var ConfigurationInterface */
    protected $configuration;

    public function __construct()
    {
        $this->module = \Module::getInstanceByName('paypal');
        $this->className = 'PrestaShopCloudSyncForm';
        $this->context = \Context::getContext();
        $this->configuration = new Configuration();
    }

    /**
     * @return array
     */
    public function getDescription()
    {
        return [
            'legend' => [
                'title' => $this->module->l('PrestaShop CouldSync', $this->className),
            ],
            'fields' => [
                PaypalConfigurations::CLOUDSYNC_ENABLED => [
                    'type' => 'switch',
                    'label' => $this->module->l('Enable PrestaShop CloudSync', $this->className),
                    'name' => PaypalConfigurations::CLOUDSYNC_ENABLED,
                    'values' => [
                        [
                            'id' => PaypalConfigurations::CLOUDSYNC_ENABLED . '_on',
                            'value' => 1,
                            'label' => $this->module->l('Enabled', $this->className),
                        ],
                        [
                            'id' => PaypalConfigurations::CLOUDSYNC_ENABLED . '_off',
                            'value' => 0,
                            'label' => $this->module->l('Disabled', $this->className),
                        ],
                    ],
                    'value' => (int) $this->configuration->get(PaypalConfigurations::CLOUDSYNC_ENABLED),
                ],
                'cloudSyncSection' => [
                    'name' => 'cloudSyncSection',
                    'type' => 'variable-set',
                    'set' => [
                        'html' => $this->initCloudSync(),
                    ],
                ],
            ],
            'submit' => [
                'title' => $this->module->l('Save', $this->className),
                'name' => 'cloudSyncForm',
            ],
            'id_form' => 'pp_cloudsync_form',
            'help' => $this->getHelpInfo(),
        ];
    }

    /**
     * @return bool
     */
    public function save($data = null)
    {
        if (is_null($data)) {
            $data = \Tools::getAllValues();
        }

        if (empty($data['cloudSyncForm'])) {
            return false;
        }

        if (empty($data[PaypalConfigurations::CLOUDSYNC_ENABLED])) {
            $this->configuration->set(PaypalConfigurations::CLOUDSYNC_ENABLED, 0);
        } else {
            $this->configuration->set(PaypalConfigurations::CLOUDSYNC_ENABLED, 1);
        }

        return true;
    }

    protected function getHelpInfo()
    {
        return \Context::getContext()->smarty->fetch('module:paypal/views/templates/admin/_partials/messages/form-help-info/cloud-sync.tpl');
    }

    protected function initCloudSync()
    {
        $output = '';

        if (!$this->configuration->get(PaypalConfigurations::CLOUDSYNC_ENABLED)) {
            return $output;
        }

        try {
            $output .= (new CloudSyncView())->render();
        } catch (\Throwable $e) {
            ProcessLoggerHandler::openLogger();
            ProcessLoggerHandler::logError(
                '[PrestaShopCloudSyncForm] ' . $e->getMessage()
            );
            ProcessLoggerHandler::closeLogger();
        }

        return $output;
    }
}
