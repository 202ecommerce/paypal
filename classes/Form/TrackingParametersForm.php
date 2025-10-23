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

use PaypalAddons\classes\Constants\TrackingParameters as TrackingParametersMap;
use PaypalAddons\services\TrackingParameters;

if (!defined('_PS_VERSION_')) {
    exit;
}

class TrackingParametersForm implements FormInterface
{
    protected $module;

    protected $className;

    protected $context;

    public function __construct()
    {
        $this->module = \Module::getInstanceByName('paypal');
        $this->className = 'TrackingParametersForm';
        $this->context = \Context::getContext();
    }

    /**
     * @return array
     */
    public function getDescription()
    {
        return [
            'legend' => [
                'title' => $this->module->l('Tracking', $this->className),
            ],
            'fields' => [
                TrackingParametersMap::STATUS => [
                    'type' => 'select',
                    'label' => $this->module->l('PayPal checkout', $this->className),
                    'name' => TrackingParametersMap::STATUS,
                    'options' => $this->getPaypalStatusList(),
                    'value' => $this->initTrackingParametersService()->getStatus(),
                    'variant' => 'primary',
                ],
                'carrier_map' => [
                    'type' => 'variable-set',
                    'label' => $this->module->l('Carrier map', $this->className),
                    'set' => [
                        'mapService' => $this->initTrackingParametersService(),
                        'carriers' => \Carrier::getCarriers($this->context->language->id, true, false, false, null, \Carrier::ALL_CARRIERS),
                    ],
                ],
            ],
            'submit' => [
                'title' => $this->module->l('Save', $this->className),
                'name' => 'trackingParametersForm',
            ],
            'id_form' => 'pp_tracking_form',
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

        if (empty($data['trackingParametersForm']) || empty($data['carrier_map'])) {
            return false;
        }

        $carrierMap = [];
        $service = $this->initTrackingParametersService();

        foreach ($data['carrier_map'] as $map) {
            if ($map == '0') {
                continue;
            }

            list($psCarrierRef, $paypalCarrierKey) = explode(',', $map);

            if (empty($psCarrierRef) || empty($paypalCarrierKey)) {
                continue;
            }

            $carrierMap[$psCarrierRef] = $paypalCarrierKey;
        }

        $service->setCarrierMap($carrierMap);

        if (isset($data[TrackingParametersMap::STATUS])) {
            $service->setStatus($data[TrackingParametersMap::STATUS]);
        }

        return true;
    }

    protected function initTrackingParametersService()
    {
        return new TrackingParameters();
    }

    protected function getPaypalStatusList()
    {
        $list = [];

        foreach ($this->initTrackingParametersService()->getStatusList() as $status) {
            $list[] = [
                'value' => $status['key'],
                'title' => $status['key'],
            ];
        }

        return $list;
    }

    protected function getHelpInfo()
    {
        return \Context::getContext()->smarty->fetch(_PS_MODULE_DIR_ . $this->module->name . '/views/templates/admin/_partials/messages/form-help-info/tracking.tpl');
    }
}
