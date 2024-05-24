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

use PaypalPPBTlib\Extensions\Diagnostic\Stubs\Model\HookShopModel;
use Db;
use DbQuery;
use Exception;
use Hook;
use Shop;

class HooksStubHandler extends AbstractStubHandler
{
    public function handle()
    {
        $shopList = Shop::getShops();
        $hookList = array_unique($this->getStub()->getModule()->hooks);

        $hookShopModels = [];
        $hooksOnError = [];

        foreach ($hookList as $hook) {
            $hookShopModel = new HookShopModel();
            $hookShopModel->setHookName($hook);
            $shopValues = [];

            foreach ($shopList as $shop) {
                $shopValue = [
                    'id' => $shop['id_shop'],
                    'value' => $this->isRegisteredHook($hook, $shop['id_shop']),
                ];
                if (!$shopValue['value']) {
                    $hooksOnError[] = $hook;
                }
                $shopValues[$shop['id_shop']] = $shopValue;
            }

            $hookShopModel->setShops($shopValues);
            $hookShopModels[] = $hookShopModel;
        }

        return [
            'module_name' => $this->getStub()->getModule()->name,
            'shopList' => $shopList,
            'hooks' => array_map(function (HookShopModel $hookShopModel) {
                return $hookShopModel->toArray();
            }, $hookShopModels),
            'hooksOnError' => array_unique($hooksOnError),
        ];
    }

    /**
     * Method override from Hook::isModuleRegisteredOnHook because
     * Hook::getIdByName usage retrieve hook id by name with aliases
     * which give wrong result in our case
     *
     * @param string $hookName
     * @param int $idShop
     *
     * @return bool
     */
    private function isRegisteredHook($hookName, $idShop)
    {
        try {
            $id_hook = (int)Hook::getIdByName($hookName, false);
            $idShop = (int)$idShop;
            $id_module = (int)$this->getStub()->getModule()->id;

            $query = new DbQuery();
            $query->select('*');
            $query->from('hook_module');
            $query->where("`id_hook` = {$id_hook} AND `id_module` = {$id_module} AND `id_shop` = {$idShop}");

            $result = Db::getInstance()->executeS($query);

            return empty($result) === false;
        } catch (Exception $e) {
            return false;
        }
    }

    public function fixHook($hookName, $idShop = null)
    {
        $this->getStub()->getModule()->registerHook(
            $hookName,
            empty($idShop) ? null : [$idShop]
        );
    }

    public function fixAllHooks()
    {
        $this->getStub()->getModule()->registerHook($this->getStub()->getModule()->hooks);
    }
}
