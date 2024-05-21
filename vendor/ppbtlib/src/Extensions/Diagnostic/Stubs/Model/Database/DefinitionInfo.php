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
