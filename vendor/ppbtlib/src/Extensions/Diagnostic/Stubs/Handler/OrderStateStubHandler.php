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
