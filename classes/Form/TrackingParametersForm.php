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

use Carrier;
use Context;
use Module;
use PaypalAddons\classes\Constants\TrackingParameters as TrackingParametersMap;
use PaypalAddons\services\TrackingParameters;
use Tools;

class TrackingParametersForm implements FormInterface
{
    protected $module;

    protected $className;

    protected $context;

    public function __construct()
    {
        $this->module = Module::getInstanceByName('paypal');
        $this->className = 'TrackingParametersForm';
        $this->context = Context::getContext();
    }

    /**
     * @return array
     */
    public function getFields()
    {
        $input = [
            [
                'type' => 'html',
                'html_content' => $this->module->displayInformation(
                    $this->module->l('Configure tracking settings to apply for PayPal seller protection', $this->className),
                    false
                ),
                'name' => '',
            ],
            [
                'type' => 'html',
                'html_content' => $this->getCarrierMapHtml(),
                'name' => '',
                'label' => $this->module->l('Carrier map', $this->className),
            ],
            [
                'type' => 'select',
                'label' => $this->module->l('PayPal checkout', $this->className),
                'name' => TrackingParametersMap::STATUS,
                'options' => [
                    'query' => $this->getPaypalStatusList(),
                    'id' => 'id',
                    'name' => 'name',
                ],
            ],
        ];

        $fields = [
            'legend' => [
                'title' => $this->module->l('Tracking parameters', $this->className),
                'icon' => 'icon-cogs',
            ],
            'input' => $input,
            'submit' => [
                'title' => $this->module->l('Save', $this->className),
                'class' => 'btn btn-default pull-right button',
                'name' => 'trackingParametersForm',
            ],
            'id_form' => 'pp_tracking_parameters',
        ];

        return $fields;
    }

    /**
     * @return array
     */
    public function getValues()
    {
        $values = [
            TrackingParametersMap::STATUS => $this->initTrackingParametersService()->getStatus(),
        ];

        return $values;
    }

    /**
     * @return bool
     */
    public function save()
    {
        if (Tools::isSubmit('trackingParametersForm') == false) {
            return false;
        }

        $carrierMap = [];
        $service = $this->initTrackingParametersService();

        foreach (Tools::getValue('carrier_map', []) as $map) {
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
        $service->setStatus(Tools::getValue(TrackingParametersMap::STATUS));

        return true;
    }

    protected function initTrackingParametersService()
    {
        return new TrackingParameters();
    }

    protected function getCarrierMapHtml()
    {
        $trackingParametersService = $this->initTrackingParametersService();
        $carriers = Carrier::getCarriers($this->context->language->id, true, false, false, null, Carrier::ALL_CARRIERS);

        return $this->context->smarty
            ->assign('mapService', $trackingParametersService)
            ->assign('carriers', $carriers)
            ->fetch(_PS_MODULE_DIR_ . $this->module->name . '/views/templates/admin/_partials/carrierMap.tpl');
    }

    protected function getPaypalStatusList()
    {
        $list = [];

        foreach ($this->initTrackingParametersService()->getStatusList() as $status) {
            $list[] = [
                'id' => $status['key'],
                'name' => $status['key'],
            ];
        }

        return $list;
    }
}
