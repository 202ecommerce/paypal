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

namespace PaypalPPBTlib\Extensions\Diagnostic\Stubs\Model;

class HookShopModel
{
    /**
     * @var string
     */
    private $hookName;

    /**
     * @var array
     */
    private $shops = [];

    /**
     * @return string
     */
    public function getHookName()
    {
        return $this->hookName;
    }

    /**
     * @param string $hookName
     * @return HookShopModel
     */
    public function setHookName($hookName)
    {
        $this->hookName = $hookName;
        return $this;
    }

    /**
     * @return array
     */
    public function getShops()
    {
        return $this->shops;
    }

    /**
     * @param array $shops
     * @return HookShopModel
     */
    public function setShops($shops)
    {
        $this->shops = $shops;
        return $this;
    }

    public function toArray()
    {
        return [
            'name' => $this->getHookName(),
            'shops' => $this->getShops(),
        ];
    }
}
