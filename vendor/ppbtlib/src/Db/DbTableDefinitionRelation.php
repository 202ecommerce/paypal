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

class DbTableDefinitionRelation
{
    /**
     * Internal IDs.
     */
    const ID_LANG = 'l';
    const ID_SHOP = 's';

    /**
     * Internal table ID.
     *
     * @var string
     */
    protected $id;

    /**
     * @var paypalObjectModelDefinition
     */
    protected $def;

    /**
     * Register paypalObjectModelDefinition and the internal ID
     * @param paypalObjectModelDefinition $def
     * @param string                $id
     */
    public function __construct($def, $id)
    {
        $this->id  = $id;
        $this->def = $def;
    }

    /**
     * Get key value from paypalObjectModel::$definition['associations'][$this->id]
     * @param string $key
     * @return array|null
     */
    public function get($key)
    {
        switch ($key) {
            case 'fields':
                return isset($this->def->get('associations')[$this->id][$key]) ?
                    $this->def->get('associations')[$this->id][$key] : array();
            default:
                return isset($this->def->get('associations')[$this->id][$key]) ?
                    $this->def->get('associations')[$this->id][$key] : null;
        }
    }

    /**
     * @return string
     */
    public function getName()
    {
        switch ($this->id) {
            case static::ID_LANG:
                return $this->def->getModel()->getName().'_lang';
            case static::ID_SHOP:
                return $this->def->getModel()->getName().'_shop';
            default:
                return ObjectModelDefinition::DB_PREFIX.$this->get('association');
        }
    }

    /**
     * @return string
     */
    public function getPrimary()
    {
        switch ($this->id) {
            case static::ID_LANG:
                return 'id_lang';
            case static::ID_SHOP:
                return 'id_shop';
            default:
                return (string)$this->get('field');
        }
    }

    /**
     * @return string
     */
    public function getEngine()
    {
        return !empty($this->get('engine')) ? (string)$this->get('engine') : ObjectModelDefinition::ENGINE;
    }

    /**
     * @return string
     */
    public function getCharset()
    {
        return !empty($this->get('charset')) ? (string)$this->get('charset') : ObjectModelDefinition::CHARSET;
    }

    /**
     * @return string
     */
    public function getCollation()
    {
        return !empty($this->get('collation')) ?
            (string)$this->get('collation') : ObjectModelDefinition::COLLATION;
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
        $primary = array(
            $this->def->getModel()->getPrimary(),
            $this->getPrimary(),
        );

        if ($this->hasMany('shop')) {
            $primary[] = 'id_shop';
        }

        if ($this->hasMany('lang')) {
            $primary[] = 'id_lang';
        }

        return $primary;
    }

    /**
     * @return array
     */
    public function getKeysForeign()
    {
        $model_table_1   = $this->def->getModel()->getName();
        $model_primary_1 = $this->def->getModel()->getPrimary();
        $model_table_2   = $this->getForeignTable();
        $model_primary_2 = $this->getPrimary();

        $foreign = array(
            array("$model_table_1.$model_primary_1"),
            array("$model_table_2.$model_primary_2"),
        );

        if ($this->hasMany('shop')) {
            $foreign[] = array(_DB_PREFIX_.'shop.id_shop');
        }

        if ($this->hasMany('lang')) {
            $foreign[] = array(_DB_PREFIX_.'lang.id_lang');
        }

        return $foreign;
    }

    /**
     * @return array
     */
    public function getKeysSimple()
    {
        switch ($this->id) {
            case DbTableDefinitionRelation::ID_LANG:
            case DbTableDefinitionRelation::ID_SHOP:
                return array();
            default:
                return array_filter(
                    $this->get('fields'),
                    array(
                        $this->def,
                        'isFieldSimpleKey'
                    )
                );
        }
    }

    /**
     * @return array
     */
    public function getKeysUnique()
    {
        switch ($this->id) {
            case DbTableDefinitionRelation::ID_LANG:
            case DbTableDefinitionRelation::ID_SHOP:
                return array();
            default:
                return array_filter(
                    $this->getFieldsCommon(),
                    array(
                        $this->def,
                        'isFieldUniqueKey'
                    )
                );
        }
    }

    /**
     * @return array
     */
    public function getKeysFulltext()
    {
        switch ($this->id) {
            case DbTableDefinitionRelation::ID_LANG:
            case DbTableDefinitionRelation::ID_SHOP:
                return array();
            default:
                return array_filter(
                    $this->getFieldsCommon(),
                    array(
                        $this->def,
                        'isFieldFulltextKey'
                    )
                );
        }
    }

    /**
     * @return int
     */
    public function getType()
    {
        switch ($this->id) {
            case DbTableDefinitionRelation::ID_LANG:
            case DbTableDefinitionRelation::ID_SHOP:
                return ObjectModel::HAS_MANY;
            default:
                return (int)$this->get('type');
        }
    }

    /**
     * Get relation fields
     * @return array
     */
    protected function getFields()
    {
        return array_merge(
            $this->getFieldsPrimary(),
            $this->getFieldsCommon()
        );
    }

    /**
     * Get relation primary fields
     * @return array
     */
    protected function getFieldsPrimary()
    {
        $fields = array(
            $this->def->getModel()->getPrimary() => ObjectModelDefinition::KEY_FIELD,
            $this->getPrimary() => ObjectModelDefinition::KEY_FIELD,
        );

        if ($this->hasMany('shop')) {
            $fields['id_shop'] = ObjectModelDefinition::KEY_FIELD;
        }

        if ($this->hasMany('lang')) {
            $fields['id_lang'] = ObjectModelDefinition::KEY_FIELD;
        }

        return $fields;
    }

    /**
     * Get relation common fields
     * @return array
     */
    protected function getFieldsCommon()
    {
        switch ($this->id) {
            case DbTableDefinitionRelation::ID_LANG:
            case DbTableDefinitionRelation::ID_SHOP:
                return array_filter(
                    $this->def->get('fields'),
                    array(
                        $this,
                        'hasField'
                    ),
                    ARRAY_FILTER_USE_BOTH
                );
            default:
                return $this->get('fields');
        }
    }

    /**
     * Get relation foreign table
     * @return string
     */
    protected function getForeignTable()
    {
        switch ($this->id) {
            case DbTableDefinitionRelation::ID_LANG:
                return _DB_PREFIX_ . 'lang';
            case DbTableDefinitionRelation::ID_SHOP:
                return _DB_PREFIX_ . 'shop';
            default:
                return _DB_PREFIX_ . $this->getForeignModelTableName();
        }
    }

    /**
     * Get table name of foreign relation model
     * @return string
     */
    protected function getForeignModelTableName()
    {
        /** @var \ObjectModel $class */
        $class = $this->get('object');

        $classDefinition = $class::$definition; //static definition \ObjectModel named $class

        return $classDefinition['table'];
    }

    /**
     * Wether or not this table has a 'OneToMany' relation
     * @param string $relation
     * @return bool
     */
    protected function hasMany($relation)
    {
        switch ($this->id) {
            case DbTableDefinitionRelation::ID_LANG:
                return !empty($this->def->get("multilang_$relation"));
            default:
                return !empty($this->get("multi$relation"));
        }
    }

    /**
     * Wether or not this table has a given field
     * @param array  $field
     * @param string $name
     * @return bool
     */
    protected function hasField($field, $name)
    {
        switch ($this->id) {
            case DbTableDefinitionRelation::ID_LANG:
                return !empty($field['lang']);
            case DbTableDefinitionRelation::ID_SHOP:
                return !empty($field['shop']);
            default:
                return isset($this->get('fields')[$name]);
        }
    }
}
