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

class ConfigurationShopModel
{
    protected $idShop;

    protected $value;

    /**
     * @return mixed
     */
    public function getIdShop()
    {
        return $this->idShop;
    }

    /**
     * @param mixed $idShop
     * @return ConfigurationShopModel
     */
    public function setIdShop($idShop)
    {
        $this->idShop = $idShop;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     * @return ConfigurationShopModel
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    public function toArray()
    {
        return [
          'id_shop' => $this->getIdShop(),
          'value' => $this->getValue(),
        ];
    }
}
