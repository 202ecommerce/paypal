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
use \OrderState;
use \Tools;
use PaypalPPBTlib\Install\AbstractInstaller;
use PaypalPPBTlib\Install\ExtensionInstaller;

class ModuleInstaller extends AbstractInstaller
{
    /**
     * @return bool
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function install()
    {
        $result = parent::install();
        $result &= $this->registerOrderStates();
        $result &= $this->installExtensions();

        return $result;
    }

    /**
     * @return bool
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function uninstall()
    {
        $result = parent::uninstall();
        $result &= $this->uninstallConfiguration();
        $result &= $this->unregisterOrderStates();
        $result &= $this->uninstallExtensions();

        return $result;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function checkPhpVersion()
    {
        if (empty($this->module->php_version_required)) {
            return true;
        }

        $phpVersion = Tools::checkPhpVersion();

        if (Tools::version_compare($phpVersion, $this->module->php_version_required, '<')) {
            throw new \Exception(sprintf(
                '[%s] This module requires at least PHP %s or newer versions.',
                $this->module->name,
                $this->module->php_version_required
            ));
        }

        return true;
    }

    /**
     * Uninstall Configuration (with or without language management)
     *
     * @return bool
     * @throws \PrestaShopDatabaseException
     */
    public function uninstallConfiguration()
    {
        $query = new DbQuery();
        $query->select('name');
        $query->from('configuration');
        $query->where('name LIKE \'' . pSQL(Tools::strtoupper($this->module->name)) . '_%\'');

        $results = Db::getInstance()->executeS($query);

        if (empty($results)) {
            return true;
        }

        $configurationKeys = array_column($results, 'name');

        $result = true;
        foreach ($configurationKeys as $configurationKey) {
            $result &= Configuration::deleteByName($configurationKey);
        }

        return $result;
    }

    /**
     * Register Order State : create new order state for this module
     *
     * @return bool
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function registerOrderStates()
    {
        if (empty($this->module->orderStates)) {
            return true;
        }

        $result = true;
        foreach ($this->module->orderStates as $configurationName => $orderStateParams) {
            $orderState = new OrderState();
            foreach ($orderStateParams as $key => $value) {
                if ($key !== 'name' && property_exists($orderState, $key)) {
                    $orderState->$key = $value;
                }
                if ($key === 'name') {
                    foreach (Language::getLanguages(false) as $language) {
                        if (empty($value[$language['iso_code']])) {
                            $orderState->name[$language['id_lang']] = $value['en'];
                        } else {
                            $orderState->name[$language['id_lang']] = $value[$language['iso_code']];
                        }
                    }
                }
            }
            $orderState->module_name = $this->module->name;
            $result &= (bool)$orderState->save();
            Configuration::updateValue($configurationName, $orderState->id);
        }

        return $result;
    }

    /**
     * Unregister Order State : mark them as deleted
     *
     * @return bool
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function unregisterOrderStates()
    {
        if (empty($this->module->orderStates)) {
            return true;
        }

        $query = new DbQuery();
        $query->select('id_order_state');
        $query->from('order_state');
        $query->where('module_name = \'' . pSQL($this->module->name) . '\'');

        $orderStateData = Db::getInstance()->executeS($query);

        if (empty($orderStateData)) {
            return true;
        }

        $result = true;
        foreach ($orderStateData as $data) {
            $query = new DbQuery();
            $query->select('1');
            $query->from('orders');
            $query->where('current_state = ' . $data['id_order_state']);
            $isUsed = (bool)Db::getInstance()->getValue($query);
            $orderState = new OrderState($data['id_order_state']);
            if ($isUsed) {
                $orderState->deleted = true;
                $result &= (bool)$orderState->save();
            } else {
                $result &= (bool)$orderState->delete();
            }
        }

        return $result;
    }

    //region Install/Uninstall Extensions

    public function installExtensions()
    {
        if (!isset($this->module->extensions) || empty($this->module->extensions)) {
            return true;
        }

        return array_product(array_map(array($this, 'installExtension'), $this->module->extensions));
    }

    /**
     * @param $extensionName
     * @return bool
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function installExtension($extensionName)
    {
        $extension = new $extensionName($this->module);
        $extensionInstaller = new ExtensionInstaller($this->module, $extension);

        return $extensionInstaller->install();
    }

    public function uninstallExtensions()
    {
        if (!isset($this->module->extensions) || empty($this->module->extensions)) {
            return true;
        }

        return array_product(array_map(array($this, 'uninstallExtension'), $this->module->extensions));
    }

    /**
     * @param $extensionName
     * @return bool
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function uninstallExtension($extensionName)
    {
        $extension = new $extensionName($this->module);
        $extensionInstaller = new ExtensionInstaller($this->module, $extension);

        return $extensionInstaller->uninstall();
    }

    //endregion

    //region Getters

    public function getHooks()
    {
        return $this->module->hooks;
    }

    public function getAdminControllers()
    {
        return $this->module->moduleAdminControllers;
    }

    public function getObjectModels()
    {
        return $this->module->objectModels;
    }

    //endregion
}