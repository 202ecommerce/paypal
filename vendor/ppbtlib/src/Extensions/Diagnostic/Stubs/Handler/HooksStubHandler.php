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
