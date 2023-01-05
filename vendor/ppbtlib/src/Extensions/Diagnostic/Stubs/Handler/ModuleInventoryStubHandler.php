<?php
/*
 * NOTICE OF LICENSE
 *
 * This source file is subject to a commercial license from SARL 202 ecommerce
 * Use, copy, modification or distribution of this source file without written
 * license agreement from the SARL 202 ecommerce is strictly forbidden.
 * In order to obtain a license, please contact us: tech@202-ecommerce.com
 * 
 * INFORMATION SUR LA LICENCE D'UTILISATION
 *
 * L'utilisation de ce fichier source est soumise a une licence commerciale
 * concedee par la societe 202 ecommerce
 * Toute utilisation, reproduction, modification ou distribution du present
 * fichier source sans contrat de licence ecrit de la part de la SARL 202 ecommerce est
 * expressement interdite.
 * Pour obtenir une licence, veuillez contacter 202-ecommerce <tech@202-ecommerce.com>
 * 
 *
 * @author    202-ecommerce <tech@202-ecommerce.com>
 * @copyright Copyright (c) 202-ecommerce
 * @license   Commercial license
 * @version   feature/34626_diagnostic
 */

namespace PaypalPPBTlib\Extensions\Diagnostic\Stubs\Handler;

use Context;
use Module;
use PaypalPPBTlib\Extensions\Diagnostic\Stubs\Handler\AbstractStubHandler;

class ModuleInventoryStubHandler extends AbstractStubHandler
{
    public function handle()
    {
        $context = Context::getContext();
        return [
            'modulesCaracteristics' => $this->getModulesCaracteristics(),
        ];
    }

    public function getModulesCaracteristics()
    {
        $allGood = [];
        $notEnabled = [];
        $notInstalled = [];
        $modules = Module::getModulesDirOnDisk();

        foreach ($modules as $moduleName) {
            $module = Module::getInstanceByName($moduleName);
            $name = $module->name;
            $dName = $module->displayName;
            $version = $module->version;
            $enabled = Module::isEnabled($moduleName);
            $installed = Module::isInstalled($moduleName);

            $moduleData = [
                'name' => $name,
                'dName' => $dName,
                'version' => $version,
            ];

            if (!$installed) {
                $notInstalled[] = $moduleData;
            } else if (!$enabled) {
                $notEnabled[] = $moduleData;
            } else {
                $allGood[] = $moduleData;
            }
        }

        sort($allGood);
        sort($notEnabled);
        sort($notInstalled);

        return [
            'allGood' => $allGood,
            'notEnabled' => $notEnabled,
            'notInstalled' => $notInstalled,
        ];
    }
}
