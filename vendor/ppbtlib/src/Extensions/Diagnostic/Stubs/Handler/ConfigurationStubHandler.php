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
use PaypalPPBTlib\Extensions\Diagnostic\Stubs\Model\Configuration\ConfigurationModel;
use PaypalPPBTlib\Extensions\Diagnostic\Stubs\Model\Configuration\ConfigurationShopModel;
use Configuration;
use Db;
use DbQuery;
use Shop;

class ConfigurationStubHandler extends AbstractStubHandler
{
    public function handle()
    {
        $configurations = $this->getConfigurations();
        return [
            'module_name' => $this->getStub()->getModule()->name,
            'configurations' => array_map(function (ConfigurationModel $configurationModel) {
                return $configurationModel->toArray();
            }, $configurations),
            'shopList' => Shop::getShops(),
            'allConfigurationsAreSame' => empty(array_filter($configurations, function (ConfigurationModel $configurationModel) {
                return !$configurationModel->getIsSame();
            })),
        ];
    }

    /**
     * @return array<ConfigurationModel>
     */
    protected function getConfigurations()
    {
        $moduleName = Configuration::get(DiagnosticExtension::MODULE_NAME);

        if (empty($moduleName)) {
            return [];
        }

        $configurations = [];

        $moduleConfigurationsList = $this->getModuleConfigurationList();

        $shops = Shop::getShops(false, null, true);

        foreach ($moduleConfigurationsList as $moduleConfig) {
            $configurationModel = new ConfigurationModel();
            $allShopValue = $this->getConfigurationValue($moduleConfig);
            $configurationModel->setAllShopValue($allShopValue === false ? '-' : $allShopValue);
            $shopModels = [];
            foreach ($shops as $idShop) {
                $configurationShopModel = new ConfigurationShopModel();
                $shopValue = $this->getConfigurationValue($moduleConfig, $idShop);
                $configurationShopModel->setIdShop($idShop);
                $configurationShopModel->setValue($shopValue === false ? '-' : $shopValue);
                $shopModels[$idShop] = $configurationShopModel;
            }

            $configurationModel->setShopsValues($shopModels);
            $configurations[$moduleConfig] = $configurationModel;
        }

        return $configurations;
    }

    protected function getModuleConfigurationList()
    {
        $moduleName = Configuration::get(DiagnosticExtension::MODULE_NAME);

        if (empty($moduleName)) {
            return [];
        }

        $query = new DbQuery();
        $query->select('DISTINCT name');
        $query->from('configuration');
        $query->where('name LIKE "' . pSQL(strtoupper($moduleName)) . '%"');

        $result = Db::getInstance()->executeS($query);

        if (empty($result)) {
            return [];
        }

        return array_map(function ($item) {
            return $item['name'];
        }, $result);
    }

    protected function getConfigurationValue($config, $idShop = null)
    {
        $query = new DbQuery();
        $query->select('value');
        $query->from('configuration');
        $query->where('name = "' . pSQL($config) . '"');

        if (is_null($idShop)) {
            $query->where('id_shop IS NULL');
        } else {
            $query->where('id_shop = ' . (int) $idShop);
        }

        return Db::getInstance()->getValue($query);
    }
}
