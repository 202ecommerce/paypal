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

use PaypalPPBTlib\Db\ObjectModelDefinition;
use PaypalPPBTlib\Db\DbTableDefinitionRelation;

use \ObjectModel;

class DbTableDefinitionModel
{
    /**
     * Internal ID.
     */
    const ID = 'm';

    /**
     * @var PaypalPPBTlib\Db\ObjectModelDefinition
     */
    protected $def;

    /**
     * Register PaypalPPBTlib\Db\ObjectModelDefinition
     * @param PaypalPPBTlib\Db\ObjectModelDefinition $def
     */
    public function __construct($def)
    {
        $this->def = $def;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return ObjectModelDefinition::DB_PREFIX . $this->def->get('table');
    }

    /**
     * @return string
     */
    public function getPrimary()
    {
        return $this->def->get('primary');
    }

    /**
     * @return string
     */
    public function getEngine()
    {
        return !empty($this->def->get('engine')) ? $this->def->get('engine') : ObjectModelDefinition::ENGINE;
    }

    /**
     * @return string
     */
    public function getCharset()
    {
        return !empty($this->def->get('charset')) ?
            $this->def->get('charset') : ObjectModelDefinition::CHARSET;
    }

    /**
     * @return string
     */
    public function getCollation()
    {
        return !empty($this->def->get('collation')) ?
            $this->def->get('collation') : ObjectModelDefinition::COLLATION;
    }

    /**
     * @return array
     */
    public function getColumns()
    {
        return $this->def->getColumnsFromFields(
            $this->getFields()
        );
    }

    /**
     * @return array
     */
    public function getKeyPrimary()
    {
        return array(
            $this->getPrimary()
        );
    }

    /**
     * @return array
     */
    public function getKeysForeign()
    {
        $ids = $this->def->getIdsSingleRelations();
        $relations = $this->def->getRelations($ids);

        return array_map(function (DbTableDefinitionRelation $relation) {
            return array("{$relation->getName()}.{$relation->getPrimary()}");
        }, $relations);
    }

    /**
     * @return array
     */
    public function getKeysSimple()
    {
        return array_filter(
            $this->getFieldsCommon(),
            array(
                $this->def,
                'isFieldSimpleKey'
            )
        );
    }

    /**
     * @return array
     */
    public function getKeysUnique()
    {
        return array_filter(
            $this->getFieldsCommon(),
            array(
                $this->def,
                'isFieldUniqueKey'
            )
        );
    }

    /**
     * @return array
     */
    public function getKeysFulltext()
    {
        return array_filter(
            $this->getFieldsCommon(),
            array(
                $this->def,
                'isFieldFulltextKey'
            )
        );
    }

    /**
     * @param string $relation
     * @return bool
     */
    public function has($relation)
    {
        switch ($relation) {
            case DbTableDefinitionRelation::ID_LANG:
                return $this->def->get('multilang');
            case DbTableDefinitionRelation::ID_SHOP:
                return $this->def->get('multishop');
            default:
                return isset($this->def->get('associations')[$relation]);
        }
    }

    /**
     * @param string $relation
     * @return bool
     */
    public function hasMany($relation)
    {
        return $this->has($relation)
               && ObjectModel::HAS_MANY === $this->def->getRelation($relation)->getType();
    }

    /**
     * @param string $relation
     * @return bool
     */
    public function hasSingle($relation)
    {
        return $this->has($relation)
               && ObjectModel::HAS_ONE === $this->def->getRelation($relation)->getType();
    }

    /**
     * @return array
     */
    protected function getFields()
    {
        return array_merge(
            $this->getFieldPrimary(),
            $this->getFieldsCommon()
        );
    }

    /**
     * @return array
     */
    protected function getFieldPrimary()
    {
        return array(
            $this->getPrimary() => ObjectModelDefinition::PRIMARY_KEY_FIELD
        );
    }

    /**
     * @return array
     */
    protected function getFieldsCommon()
    {
        return array_filter(
            $this->def->get('fields'),
            array(
                $this,
                'hasField'
            )
        );
    }

    /**
     * Wether or not this table has a given field
     * @param array $field
     * @return bool
     */
    protected function hasField($field)
    {
        return !$this->isFieldMultilang($field)
               && !$this->isFieldMultishop($field)
               || $this->isFieldMultishopShared($field);
    }

    /**
     * Wether or not given field is multilang
     * @param array $field
     * @return bool
     */
    protected function isFieldMultilang($field)
    {
        return !empty($field['lang']);
    }

    /**
     * Wether or not given field is multishop
     * @param array $field
     * @return bool
     */
    protected function isFieldMultishop($field)
    {
        return !empty($field['shop']);
    }

    /**
     * Wether or not given multishop field is shared with this table
     * @param array $field
     * @return bool
     */
    protected function isFieldMultishopShared($field)
    {
        return !empty($field['shop'])
            && 'both' === $field['shop'];
    }
}
