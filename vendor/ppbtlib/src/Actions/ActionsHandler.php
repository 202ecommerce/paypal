<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to a commercial license from SARL 202 ecommence
 * Use, copy, modification or distribution of this source file without written
 * license agreement from the SARL 202 ecommence is strictly forbidden.
 * In order to obtain a license, please contact us: tech@202-ecommerce.com
 * ...........................................................................
 * INFORMATION SUR LA LICENCE D'UTILISATION
 *
 * L'utilisation de ce fichier source est soumise a une licence commerciale
 * concedee par la societe 202 ecommence
 * Toute utilisation, reproduction, modification ou distribution du present
 * fichier source sans contrat de licence ecrit de la part de la SARL 202 ecommence est
 * expressement interdite.
 * Pour obtenir une licence, veuillez contacter 202-ecommerce <tech@202-ecommerce.com>
 * ...........................................................................
 *
 * @author    202-ecommerce <tech@202-ecommerce.com>
 * @copyright Copyright (c) 202-ecommerce
 * @license   Commercial license
 *
 * @version   develop
 */

namespace PaypalPPBTlib\Actions;

use Hook;
use Module;
use ObjectModel;
use Tools;

/**
 * Actions Handler
 */
class ActionsHandler
{
    const PROCESS_OVERRIDE_HOOK = 'actionPaypalActionsHandler';

    /**
     * @var ObjectModel
     */
    protected $modelObject;

    /**
     * Values conveyored by the classes
     *
     * @var array
     */
    protected $conveyor = [];

    /**
     * List of actions
     *
     * @var array
     */
    protected $actions;

    /**
     * Set an modelObject
     *
     * @param ObjectModel $modelObject
     *
     * @return $this
     */
    public function setModelObject($modelObject)
    {
        $this->modelObject = $modelObject;

        return $this;
    }

    /**
     * Set the conveyor
     *
     * @param array $conveyorData
     *
     * @return $this
     */
    public function setConveyor($conveyorData)
    {
        $this->conveyor = $conveyorData;

        return $this;
    }

    /**
     * Return data in conveyor
     *
     * @return array
     */
    public function getConveyor()
    {
        return $this->conveyor;
    }

    /**
     * Call sevral actions
     *
     * @param mixed $actions
     *
     * @return $this
     */
    public function addActions($actions)
    {
        $this->actions = func_get_args();

        return $this;
    }

    /**
     * Process the action call back of cross modules
     *
     * @param string $className Name of the actions chain / Namespaced classname
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function process($className)
    {
        if (!class_exists($className)) {
            $className = Tools::ucfirst($className) . 'Actions';
            if (!preg_match('/^[a-zA-Z]+$/', $className)) {
                throw new \Exception($className . '" class name not valid "');
            }
            include_once _PS_MODULE_DIR_ . 'paypal/classes/actions/' . $className . '.php';

            $overridePath = _PS_OVERRIDE_DIR_ . 'modules/Paypal/classes/actions/' . $className . '.php';
            if (file_exists($overridePath)) {
                $className .= 'Override';
                include_once $overridePath;
            }
        }

        $moduleId = Module::getModuleIdByName('paypal');
        /** @var array $hookResult */
        $hookResult = Hook::exec(self::PROCESS_OVERRIDE_HOOK, ['className' => $className], $moduleId, true, false);
        if (!empty($hookResult) && !empty($hookResult['paypal'])) {
            $className = $hookResult['paypal'];
        }

        if (class_exists($className)) {
            /** @var DefaultActions $classAction */
            $classAction = new $className();
            $classAction->setModelObject($this->modelObject);
            $classAction->setConveyor($this->conveyor);

            foreach ($this->actions as $action) {
                if (!is_callable([$classAction, $action], false, $callableName)) {
                    continue;
                }
                if (!call_user_func_array([$classAction, $action], [])) {
                    $this->setConveyor($classAction->getConveyor());

                    return false;
                }
            }

            $this->setConveyor($classAction->getConveyor());
        } else {
            throw new \Exception($className . '" class not defined "');
        }

        return true;
    }
}
