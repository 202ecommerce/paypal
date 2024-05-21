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
