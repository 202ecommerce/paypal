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
 * @version   develop
 */

namespace PaypalPPBTlib;

use PrestaShop\PrestaShop\Core\Module\WidgetInterface;
use \ReflectionClass;
use \Tools;
use PaypalPPBTlib\Install\ModuleInstaller;
use PaypalPPBTlib\Extensions\AbstractModuleExtension;

class Module extends \Module
{
    /**
     * List of objectModel used in this Module
     * @var array $objectModels
     */
    public $objectModels = array();

    /**
     * List of hooks used in this Module
     * @var array $hooks
     */
    public $hooks = array();

    /**
     * List of AdminControllers used in this Module
     * @var array $moduleAdminControllers
     */
    public $moduleAdminControllers = array();

    /**
     * Install Module
     *
     * @return bool
     * @throws \PrestaShopException
     */
    public function install()
    {
        $installer = new ModuleInstaller($this);

        $isPhpVersionCompliant = false;
        try {
            $isPhpVersionCompliant = $installer->checkPhpVersion();
        } catch (\Exception $e) {
            $this->_errors[] = Tools::displayError($e->getMessage());
        }

        return $isPhpVersionCompliant && parent::install() && $installer->install();
    }

    /**
     * Uninstall Module
     *
     * @return bool
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function uninstall()
    {
        $installer = new ModuleInstaller($this);

        return parent::uninstall() && $installer->uninstall();
    }

    /**
     * TODO Reset Module only if merchant choose to keep data on modal
     *
     * @return bool
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function reset()
    {
        $installer = new ModuleInstaller($this);

        return $installer->reset($this);
    }

    /**
     * Handle module extension hook call
     * @param $hookName
     * @param $params
     * @return array|bool|string
     */
    public function handleExtensionsHook($hookName, $params)
    {
        if (!isset($this->extensions) || empty($this->extensions)) {
            return false;
        }
        $result = false;
        foreach ($this->extensions as $extension) {
            /** @var AbstractModuleExtension $extension */
            $extension = new $extension();
            $extension->setModule($this);
            if (is_callable(array($extension, $hookName))) {
                $hookResult = $extension->{$hookName}($params);
                if ($result === false) {
                    $result = $hookResult;
                } elseif (is_array($hookResult) && $result !== false) {
                    $result = array_merge($result, $hookResult);
                } else {
                    $result .= $hookResult;
                }
            }
        }

        return $result;
    }

    /**
     * Handle module widget call
     * @param $action
     * @param $method
     * @param $hookName
     * @param $configuration
     * @return bool
     * @throws \ReflectionException
     */
    public function handleWidget($action, $method, $hookName, $configuration)
    {
        if (!isset($this->extensions) || empty($this->extensions)) {
            return false;
        }

        foreach ($this->extensions as $extension) {
            /** @var AbstractModuleExtension $extension */
            $extension = new $extension();
            if (!($extension instanceof WidgetInterface)) {
                continue;
            }
            $extensionClass = (new ReflectionClass($extension))->getShortName();
            if ($extensionClass != $action) {
                continue;
            }
            $extension->setModule($this);
            if (is_callable(array($extension, $method))) {
                return $extension->{$method}($hookName, $configuration);
            }
        }

        return false;

    }
}
