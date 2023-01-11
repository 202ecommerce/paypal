<?php
/**
 * 2007-2023 PayPal
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
 *  @author 2007-2023 PayPal
 *  @author 202 ecommerce <tech@202-ecommerce.com>
 *  @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 *  @copyright PayPal
 *
 */

namespace PaypalPPBTlib\Install;


use \Configuration;
use \Db;
use \DbQuery;
use \Language;
use \Tab;
use \Tools;
use PaypalPPBTlib\Db\ObjectModelExtension;

abstract class AbstractInstaller
{
    //region Fields

    /**
     * @var \Paypal
     */
    protected $module;

    /**
     * @var array
     */
    protected $hooks;

    /**
     * @var array
     */
    protected $adminControllers;

    /**
     * @var array
     */
    protected $objectModels;

    //endregion

    public function __construct($module)
    {
        $this->module = $module;
    }

    /**
     * Get hooks used in module/extension
     * @return array
     */
    abstract public function getHooks();

    /**
     * Get admin controllers used in module/extension
     * @return array
     */
    abstract public function getAdminControllers();

    /**
     * Get object models used in module/extension
     * @return array
     */
    abstract public function getObjectModels();

    /**
     * @param \Paypal $module
     * @return $this
     */
    public function setModule($module)
    {
        $this->module = $module;

        return $this;
    }

    /**
     * @return bool
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function install()
    {
        $result = $this->registerHooks();
        $result &= $this->installObjectModels();
        $result &= $this->installAdminControllers();

        return $result;
    }

    /**
     * @return bool
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function uninstall()
    {
        $result = $this->uninstallObjectModels();
        $result &= $this->uninstallModuleAdminControllers();

        return $result;
    }

    //TODO
    /**
     * Used only if merchant choose to keep data on modal in Prestashop 1.6
     *
     * @param Paypal $module
     * @return bool
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function reset($module)
    {
        $this->module = $module;
        $result = $this->clearHookUsed();
        $result &= $this->installObjectModels();
        $result &= $this->uninstallModuleAdminControllers();
        $result &= $this->installAdminControllers();

        return $result;
    }

    /**
     * Register hooks used by our module
     * @return bool
     */
    public function registerHooks()
    {
        if (empty($this->getHooks())) {
            return true;
        }

        return array_product(array_map(array($this->module, 'registerHook'), $this->getHooks()));
    }

    //TODO
    /**
     * Clear hooks used by our module
     *
     * @return bool
     * @throws \PrestaShopException
     */
    public function clearHookUsed()
    {
        $result = true;
        $hooksUsed = $this->getHooksUsed();

        if (empty($hooksUsed) && empty($this->getHooks())) {
            // Both are empty, no need to continue process
            return $result;
        }

        if (false === is_array($this->getHooks())) {
            // If $module->hooks is not defined or is not an array
            $this->hooks = array();
        }

        foreach ($this->getHooks() as $hook) {
            if (false === in_array($hook, $hooksUsed, true)) {
                // If hook is not registered, do it
                $result &= $this->module->registerHook($hook);
            }
        }

        foreach ($hooksUsed as $hookUsed) {
            if (false === in_array($hookUsed, $this->getHooks(), true)) {
                // If hook is registered by module but is not used anymore
                $result &= $this->module->unregisterHook($hookUsed);
                $result &= $this->module->unregisterExceptions($hookUsed);
            }
        }

        return $result;
    }

    /**
     * Retrieve hooks used by our module
     *
     * @return array
     * @throws \PrestaShopDatabaseException
     */
    public function getHooksUsed()
    {
        $query = new DbQuery();
        $query->select('h.name');
        $query->from('hook', 'h');
        $query->innerJoin('hook_module', 'hm', 'hm.id_hook = h.id_hook');
        $query->where('hm.id_module = ' . (int)$this->module->id);
        $query->groupBy('h.name');

        $results = Db::getInstance()->executeS($query);

        if (empty($results)) {
            return array();
        }

        return array_column($results, 'name');
    }

    /**
     * Add Tabs for our ModuleAdminController
     *
     * @return bool
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function installAdminControllers()
    {
        $result = true;

        if (empty($this->getAdminControllers())) {
            // If module has no ModuleAdminController to install
            return $result;
        }

        foreach ($this->getAdminControllers() as $tabData) {
            if (Tab::getIdFromClassName($tabData['class_name'])) {
                $result &= true;
                continue;
            }

            $parentClassName = $tabData['parent_class_name'];
            if ($parentClassName == 'ShopParameters' && version_compare(_PS_VERSION_, '1.7', '<')) {
                $parentClassName = 'AdminPreferences';
            }
            /** 3 levels available on 1.7+ */
            $defaultTabLevel1 = array('SELL', 'IMPROVE', 'CONFIGURE', 'DEFAULT');
            if (in_array($parentClassName, $defaultTabLevel1) && version_compare(_PS_VERSION_, '1.7', '<')) {
                continue;
            }
            if ($tabData['class_name'] == 'paypal' && version_compare(_PS_VERSION_, '1.7', '<')) {
                $parentClassName = 'AdminParentModulesSf';
                $tabData['parent_class_name'] = 'AdminParentModulesSf';
                $tabData['visible'] = true;
            }

            $tab = new Tab();
            $parentId = (int)Tab::getIdFromClassName($parentClassName);
            if (!empty($parentId)) {
                $tab->id_parent = $parentId;
            }
            $tab->class_name = $tabData['class_name'];
            $tab->module = $this->module->name;

            foreach (Language::getLanguages(false) as $language) {
                if (empty($tabData['name'][$language['iso_code']])) {
                    $tab->name[$language['id_lang']] = $tabData['name']['en'];
                } else {
                    $tab->name[$language['id_lang']] = $tabData['name'][$language['iso_code']];
                }
            }

            $tab->active = true;
            if (isset($tabData['visible'])) {
                $tab->active = $tabData['visible'];
            }

            if (isset($tabData['icon']) && property_exists('Tab', 'icon')) {
                $tab->icon = $tabData['icon']; // For Prestashop 1.7
            }
            $result &= (bool)$tab->add();
        }

        return $result;
    }

    /**
     * Delete Tabs of our ModuleAdminController
     *
     * @return bool
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function uninstallModuleAdminControllers()
    {
        $query = new DbQuery();
        $query->select('*');
        $query->from('tab');
        $query->where('module = \''.pSQL($this->module->name).'\'');

        $tabs = Db::getInstance()->executeS($query);

        if (empty($tabs)) {
            return true;
        }

        $result = true;
        foreach ($tabs as $tabData) {
            $tab = new Tab((int)$tabData['id_tab']);
            $result &= (bool)$tab->delete();
        }
        return $result;
    }

    /**
     * Install all our \ObjectModel
     *
     * @return bool
     */
    public function installObjectModels()
    {
        if (empty($this->getObjectModels())) {
            return true;
        }

        return array_product(array_map(array($this, 'installObjectModel'), $this->getObjectModels()));
    }

    /**
     * Install model
     *
     * @param string $objectModelClassName
     * @return bool
     * @throws \Exception
     */
    public function installObjectModel($objectModelClassName)
    {
        if (!class_exists($objectModelClassName)) {
            throw new \Exception('Installer error : ModelObject "' . $objectModelClassName . '" not found');
        }

        /** @var \ObjectModel $objectModel */
        $objectModel = new $objectModelClassName();

        $objectModelExtended = new ObjectModelExtension(
            $objectModel,
            Db::getInstance()
        );

        return $objectModelExtended->install();
    }

    /**
     * Uninstall models
     *
     * @return bool
     */
    public function uninstallObjectModels()
    {
        if (empty($this->getObjectModels())) {
            return true;
        }

        return array_product(array_map(array($this, 'uninstallObjectModel'), $this->getObjectModels()));
    }

    /**
     * Uninstall model
     *
     * @param string $objectModelClassName
     * @return bool
     * @throws \Exception
     */
    public function uninstallObjectModel($objectModelClassName)
    {
        if (!class_exists($objectModelClassName)) {
            throw new \Exception('Installer error : ModelObject "' . $objectModelClassName . '" not found');
        }

        /** @var \ObjectModel $objectModel */
        $objectModel = new $objectModelClassName();

        $objectModelExtended = new ObjectModelExtension(
            $objectModel,
            Db::getInstance()
        );

        return $objectModelExtended->uninstall();
    }
}
