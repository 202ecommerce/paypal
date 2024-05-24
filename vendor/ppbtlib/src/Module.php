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
