<?php
/*
 * NOTICE OF LICENSE
 *
 * This source file is subject to a commercial license from SARL 202 ecommerce
 * Use, copy, modification or distribution of this source file without written
 * license agreement from the SARL 202 ecommerce is strictly forbidden.
 * In order to obtain a license, please contact us: tech@202-ecommerce.com
 * ...........................................................................
 * INFORMATION SUR LA LICENCE D'UTILISATION
 *
 * L'utilisation de ce fichier source est soumise a une licence commerciale
 * concedee par la societe 202 ecommerce
 * Toute utilisation, reproduction, modification ou distribution du present
 * fichier source sans contrat de licence ecrit de la part de la SARL 202 ecommerce est
 * expressement interdite.
 * Pour obtenir une licence, veuillez contacter 202-ecommerce <tech@202-ecommerce.com>
 * ...........................................................................
 *
 * @author    202-ecommerce <tech@202-ecommerce.com>
 * @copyright Copyright (c) 202-ecommerce
 * @license   Commercial license
 * @version   develop
 */

namespace PaypalPPBTlib\Extensions\Diagnostic\Stubs\Handler;

use Configuration;
use Context;
use Db;
use DbQuery;
use OrderState;
use Tools;
use PaypalPPBTlib\Extensions\Diagnostic\Stubs\Concrete\AbstractStub;
use PaypalPPBTlib\Extensions\Diagnostic\Stubs\Concrete\ConfigurationStub;
use PaypalPPBTlib\Extensions\Diagnostic\Stubs\Handler\AbstractStubHandler;
use Validate;

class OrderStateStubHandler extends AbstractStubHandler
{
    public function handle()
    {
        $context = \Context::getContext();
        $orderStateList = \OrderState::getOrderStates($context->language->id);

        return [
            'module_name' => $this->getStub()->getModule()->name,
            'orderStateList' => $orderStateList,
            'checkOrderStates' => $this->checkOrderStates(),
        ];
    }

    public function fixOrderState()
    {
        $statuses = $this->getStub()->getParameters()->getStatuses();

        foreach ($statuses as $configurationName => $orderStateProperties) {
            $orderStateId = (int) Configuration::getGlobalValue($configurationName);
            $isToFixed = false;

            foreach ($orderStateProperties as $orderStatePropertyName => $orderStatePropertyValue) {
                $orderState = new OrderState($orderStateId);

                if (false === Validate::isLoadedObject($orderState)) {
                    $isToFixed = true;
                }

                if (false === property_exists($orderState, $orderStatePropertyName)) {
                    continue;
                }

                if ($orderState->$orderStatePropertyName !== $orderStatePropertyValue) {
                    $orderState->$orderStatePropertyName = $orderStatePropertyValue;
                    $isToFixed = true;
                }
            }

            if (true === $isToFixed) {
                $orderState->save();
            }
        }
    }

    public function associateOrderState()
    {
        $statuses = $this->getStub()->getParameters()->getStatuses();

        foreach ($statuses as $configurationName => $orderStateProperties) {
            $configurationValue = Tools::getValue($configurationName);

            Configuration::deleteByName($configurationName);
            Configuration::updateGlobalValue($configurationName, $configurationValue);
        }
    }

    private function checkOrderStates()
    {
        $statuses = $this->getStub()->getParameters()->getStatuses();
        $checkOrderStates = [];
        $areWrong = 0;

        foreach ($statuses as $configurationName => $orderStateProperties) {
            $checkOrderState['name'] = $configurationName;
            $checkOrderState['value'] = Configuration::getGlobalValue($configurationName);

            if (false === empty($checkOrderState['value'])) {
                $orderState = new OrderState($checkOrderState['value']);
                $error = [];

                foreach ($orderStateProperties as $OSPName => $OSPValue) {
                    if (true === property_exists($orderState, $OSPName)) {
                        if ($orderState->$OSPName != $OSPValue) {
                            $var1 = $orderState->$OSPName === false ? '0' : $orderState->$OSPName;
                            $var2 = $OSPValue === false ? '0' : $OSPValue;
                            $error[] = sprintf('%s is invalid (%s instead of %s)', $OSPName, $var1, $var2);
                        }
                    }
                }

                if (false === empty($error)) {
                    $areWrong++;
                }

                $checkOrderState['error'] = $error;
            } else {
                $checkOrderState['error'] = ['No order state associated'];
                $checkOrderState['unassociated'] = true;
            }
            $checkOrderStates['order_states'][] = $checkOrderState;
        }
        $checkOrderStates['are_wrong'] = $areWrong;
        return $checkOrderStates;
    }
}
