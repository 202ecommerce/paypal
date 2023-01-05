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
 * @version   develop
 */

namespace PaypalPPBTlib\Extensions\Diagnostic\Stubs\Handler;

use Context;
use Module;
use PaypalPPBTlib\Extensions\Diagnostic\Stubs\Handler\AbstractStubHandler;
use PaypalPPBTlib\Extensions\Diagnostic\Stubs\Storage\SecurityAdvisoryRetriever;

class SecurityAdvisoryStubHandler extends AbstractStubHandler
{
    public function handle()
    {
        $context = Context::getContext();
        return [
            'modules' => $this->compareInstalledModulesWithFile(),
        ];
    }

    public function getListModules()
    {
        $modules = Module::getModulesDirOnDisk();
        foreach ($modules as $moduleName) {
            $module = Module::getInstanceByName($moduleName);
            $name = $module->name;
            $version = $module->version;
            $enabled = Module::isEnabled($moduleName);
            $installed = Module::isInstalled($moduleName);

            if ($enabled === true && $installed === true) {
                $installedModules[] = [
                    'name' => $name,
                    'version' => $version,
                ];
            }
        }
        return $installedModules;
    }

    public function compareInstalledModulesWithFile()
    {
        $withBreach = [];
        $installedModules = $this->getListModules();
        $securityAdvisoryRetriever = new SecurityAdvisoryRetriever();
        $advisories = $securityAdvisoryRetriever->retrieve();

        foreach ($installedModules as $module) {
            foreach ($advisories as $advisory) {
                if ($module['name'] == $advisory->getFolderName() &&
                (empty($advisory->getFrom()) ? true : version_compare($advisory->getFrom(), $module['version']) <= 0) &&
                (empty($advisory->getTo()) ? true : version_compare($module['version'], $advisory->getTo()) < 0)) {
                    $withBreach[] = $advisory->toArray();
                    continue;
                }
            }
        }

        return $withBreach;
    }
}
