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

namespace PaypalPPBTlib\Extensions\Diagnostic\Stubs\Storage;

use PaypalPPBTlib\Extensions\Diagnostic\DiagnosticExtension;
use PaypalPPBTlib\Extensions\Diagnostic\Stubs\Model\ModuleConfigModel;
use Configuration;

class DiagnosticRetriever
{
    protected static $config = null;

    /**
     * @return null|ModuleConfigModel
     */
    public function retrieveCurrent()
    {
        $moduleName = Configuration::get(DiagnosticExtension::MODULE_NAME);

        if (empty($moduleName)) {
            return null;
        }

        $configs = $this->retrieveAll();

        return current(array_filter($configs, function (ModuleConfigModel $moduleConfigModel) {
            return $moduleConfigModel->getName() == Configuration::get(DiagnosticExtension::MODULE_NAME);
        }));
    }

    /**
     * @return ModuleConfigModel[]
     */
    public function retrieveAll()
    {
        $diagnosticModuleName = Configuration::get(DiagnosticExtension::DIAGNOSTIC_MODULE_NAME);

        if (empty($diagnosticModuleName)) {
            return [];
        }

        $configs = $this->getDiagnosticConfig($diagnosticModuleName);

        return array_map(function ($config) {
            $moduleConfigModel = new ModuleConfigModel();
            $moduleConfigModel->setName(!empty($config['name']) ? $config['name'] : '')
                ->setMeta(!empty($config['meta']) ? $config['meta'] : [])
                ->setStubs(!empty($config['stubs']) ? $config['stubs'] : []);

            return $moduleConfigModel;
        }, $configs);
    }

    protected function getDiagnosticConfig($moduleName)
    {
        if (!is_null(static::$config)) {
            return static::$config;
        }
        $file = _PS_MODULE_DIR_ . $moduleName . '/diagnostic.php';

        if (!file_exists($file)) {
            static::$config = [];
            return static::$config;
        }

        static::$config = include $file;
        return static::$config;
    }
}
