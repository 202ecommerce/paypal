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
