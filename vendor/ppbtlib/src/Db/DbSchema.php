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

namespace PaypalPPBTlib\Db;

class DbSchema
{
    /**
     * @var PaypalPPBTlib\Db\ObjectModelDefinition
     */
    protected $def;

    /**
     * Table (internal) ID
     *
     * @var string
     */
    protected $id;

    /**
     * Register PaypalPPBTlib\Db\ObjectModelDefinition and table (internal) ID
     * @param PaypalPPBTlib\Db\ObjectModelDefinition $def
     * @param string                $id
     */
    public function __construct($def, $id)
    {
        $this->def = $def;
        $this->id  = $id;
    }

    /**
     * Map table properties
     * @param PaypalPPBTlib\Db\DbTable $table
     * @return PaypalPPBTlib\Db\DbTable
     */
    public function map($table)
    {
        return $table
            ->setName($this->def->getName($this->id))
            ->setEngine($this->def->getEngine($this->id))
            ->setCharset($this->def->getCharset($this->id))
            ->setCollation($this->def->getCollation($this->id))
            ->setColumns($this->def->getColumns($this->id))
            ->setKeyPrimary($this->def->getKeyPrimary($this->id))
            ->setKeysSimple($this->def->getKeysSimple($this->id))
            ->setKeysUnique($this->def->getKeysUnique($this->id))
            ->setKeysFulltext($this->def->getKeysFulltext($this->id))
            // @todo: fix foreign key with lang table not InnoDb
            //->setKeysForeign($this->def->getKeysForeign($this->id))
            ;
    }
}
