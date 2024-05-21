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
