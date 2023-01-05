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

namespace PaypalPPBTlib\Extensions\Diagnostic\Stubs\Model\Database;

class DefinitionInfo
{
    /**
     * @var TableInfo|null
     */
    protected $table;

    /**
     * @var TableInfo|null
     */
    protected $lang;

    /**
     * @var TableInfo|null
     */
    protected $shop;

    /**
     * @return TableInfo|null
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * @param TableInfo|null $table
     * @return DefinitionInfo
     */
    public function setTable($table)
    {
        $this->table = $table;
        return $this;
    }

    /**
     * @return TableInfo|null
     */
    public function getLang()
    {
        return $this->lang;
    }

    /**
     * @param TableInfo|null $lang
     * @return DefinitionInfo
     */
    public function setLang($lang)
    {
        $this->lang = $lang;
        return $this;
    }

    /**
     * @return TableInfo|null
     */
    public function getShop()
    {
        return $this->shop;
    }

    /**
     * @param TableInfo|null $shop
     * @return DefinitionInfo
     */
    public function setShop($shop)
    {
        $this->shop = $shop;
        return $this;
    }

    public function toArray()
    {
        return [
            'table' => is_null($this->getTable()) ? null : $this->getTable()->toArray(),
            'shop' => is_null($this->getShop()) ? null : $this->getShop()->toArray(),
            'lang' => is_null($this->getLang()) ? null : $this->getLang()->toArray(),
        ];
    }
}
