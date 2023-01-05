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

use PaypalPPBTlib\Extensions\Diagnostic\DiagnosticExtension;
use PaypalPPBTlib\Extensions\Diagnostic\Stubs\Model\Constant\DiagnosticHook;
use PaypalPPBTlib\Module;
use Configuration;
use Hook;

class ConflictsStubHandler extends AbstractStubHandler
{
    public function handle()
    {
        return [
            'conflicts' => [
                'data' => $this->getConflictsData(),
                'action' => $this->getConflictsAction(),
            ],
        ];
    }

    protected function getConflictsData()
    {
        $moduleName = Configuration::get(DiagnosticExtension::MODULE_NAME);
        $idModule = Module::getModuleIdByName($moduleName);
        $data = Hook::exec(DiagnosticHook::HOOK_CONFLICTS, [], $idModule, true);

        if (empty($data) || empty($data[$moduleName])) {
            return null;
        }

        return $data[$moduleName];
    }

    protected function getConflictsAction()
    {
        $moduleName = Configuration::get(DiagnosticExtension::MODULE_NAME);
        $idModule = Module::getModuleIdByName($moduleName);
        $data = Hook::exec(DiagnosticHook::HOOK_CONFLICTS_ACTION, [], $idModule, false);

        if (empty($data)) {
            return null;
        }

        return $data;
    }
}
