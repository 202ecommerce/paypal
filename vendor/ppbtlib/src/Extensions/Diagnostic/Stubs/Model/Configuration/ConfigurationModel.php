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

namespace PaypalPPBTlib\Extensions\Diagnostic\Stubs\Model\Configuration;

use PaypalPPBTlib\Extensions\Diagnostic\Stubs\Model\Configuration\ConfigurationShopModel;

class ConfigurationModel
{
    protected $allShopValue;

    /**
     * @var array<ConfigurationShopModel>
     */
    protected $shopsValues;

    /**
     * @var bool
     */
    protected $isSame = true;

    /**
     * @return mixed
     */
    public function getAllShopValue()
    {
        return $this->allShopValue;
    }

    /**
     * @param mixed $allShopValue
     * @return ConfigurationModel
     */
    public function setAllShopValue($allShopValue)
    {
        $this->allShopValue = $allShopValue;
        return $this;
    }

    /**
     * @return ConfigurationShopModel[]
     */
    public function getShopsValues()
    {
        return $this->shopsValues;
    }

    /**
     * @param ConfigurationShopModel[] $shopsValues
     * @return ConfigurationModel
     */
    public function setShopsValues($shopsValues)
    {
        $this->shopsValues = $shopsValues;
        return $this;
    }

    public function getIsSame()
    {
        $allValues = $this->getAllShopValue();

        foreach ($this->getShopsValues() as $shopValue) {
            if ($shopValue->getValue() == '-' || $shopValue->getValue() == $allValues) {
                continue;
            }

            return false;
        }

        return true;
    }

    public function toArray()
    {
        return [
            'all_shop_value' => $this->getAllShopValue(),
            'shops_value' => array_map(function (ConfigurationShopModel $shopValue) {
                return $shopValue->toArray();
            }, $this->getShopsValues()),
            'is_same' => $this->getIsSame(),
        ];
    }
}
