<?php
/**
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
 * @copyright 202-ecommerce
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @version   develop
 */

namespace PaypalPPBTlib\Db;

use PaypalPPBTlib\Db\DbTableDefinitionModel;
use PaypalPPBTlib\Db\DbTableDefinitionRelation;
use PaypalPPBTlib\Db\DbSchema;

use \ObjectModel;
use \Tools;

class ObjectModelDefinition
{
    /**
     * Defaults.
     */
    const CHARSET   = 'utf8';
    const COLLATION = 'DEFAULT';
    const ENGINE    = _MYSQL_ENGINE_;
    const DB_PREFIX = _DB_PREFIX_;
    /**
     * Primary key field description.
     */
    const PRIMARY_KEY_FIELD = array(
        'type'     => ObjectModel::TYPE_INT,
        'validate' => 'isUnsignedId',
        'required' => true,
        'primary'  => true,
    );
    /**
     * (Simple) Key field description.
     */
    const KEY_FIELD = array(
        'type'     => ObjectModel::TYPE_INT,
        'validate' => 'isUnsignedId',
        'required' => true,
    );

    /**
     * ObjectModel::$definition.
     *
     * @var array
     */
    protected $def;

    /**
     * @var DbTableDefinitionModel
     */
    protected $model;

    /**
     * @var array of PaypalPPBTlib\Db\DbTableDefinitionRelation
     */
    protected $relations;

    /**
     * Register ObjectModel::$definition as a collection data source
     * @param array $def
     */
    public function __construct($def)
    {
        $this->def = $def;
        // Prestashop doesn't define shop association when fetching definition.
        if ($this->get('multishop')) {
            $this->def['associations'][DbTableDefinitionRelation::ID_SHOP] = array(
                'type'  => ObjectModel::HAS_MANY,
                'field' => $this->get('primary'),
            );
        }
    }

    /**
     * @param string $key
     * @return array|bool|mixed|null
     */
    public function get($key)
    {
        switch ($key) {
            case 'table':
            case 'primary':
                return $this->def[$key];
            case 'fields':
            case 'associations':
                return isset($this->def[$key]) ? $this->def[$key] : array();
            case 'multilang':
            case 'multilang_shop':
            case 'multishop':
                return isset($this->def[$key]) ? $this->def[$key] : false;
            default:
                return isset($this->def[$key]) ? $this->def[$key] : null;
        }
    }

    /**
     * @return array
     */
    public function getSchemas()
    {
        return array_map(
            array(
                $this,
                'getSchema'
            ),
            $this->getIds()
        );
    }

    /**
     * @param string $id
     * @return PaypalPPBTlib\Db\DbSchema
     */
    public function getSchema($id)
    {
        return new DbSchema($this, $id);
    }

    /**
     * @return DbTableDefinitionModel
     */
    public function getModel()
    {
        return isset($this->model) ? $this->model : $this->model = new DbTableDefinitionModel($this);
    }

    /**
     * @param array $ids
     * @return array
     */
    public function getRelations($ids)
    {
        return array_map(
            array(
                $this,
                'getRelation'
            ),
            $ids
        );
    }

    /**
     * @param string $id
     * @return PaypalPPBTlib\Db\DbTableDefinitionRelation
     */
    public function getRelation($id)
    {
        return isset($this->relations[$id]) ?
            $this->relations[$id] : $this->relations[$id] = new DbTableDefinitionRelation($this, $id);
    }

    /**
     * @return array
     */
    public function getIds()
    {
        return array_unique(array_merge(
            array($this->getIdModel()),
            $this->getIdsMultiRelations()
        ));
    }

    /**
     * @return string
     */
    public function getIdModel()
    {
        return DbTableDefinitionModel::ID;
    }

    /**
     * @param int|null $type
     * @return array
     */
    public function getIdsRelations($type = null)
    {
        switch ($type) {
            // OneToOne and ManyToOne.
            case ObjectModel::HAS_ONE:
                return array_filter(
                    $this->getIdsRelations(),
                    array(
                        $this->getModel(),
                        'hasSingle'
                    )
                );
            // ManyToMany.
            case ObjectModel::HAS_MANY:
                return array_filter(
                    $this->getIdsRelations(),
                    array(
                        $this->getModel(),
                        'hasMany'
                    )
                );
            // All potential relations.
            default:
                return array_merge(
                    array(
                        DbTableDefinitionRelation::ID_LANG,
                        DbTableDefinitionRelation::ID_SHOP
                    ),
                    array_keys($this->get('associations'))
                );
        }
    }

    /**
     * @return array
     */
    public function getIdsMultiRelations()
    {
        return $this->getIdsRelations(ObjectModel::HAS_MANY);
    }

    /**
     * @return array
     */
    public function getIdsSingleRelations()
    {
        return $this->getIdsRelations(ObjectModel::HAS_ONE);
    }

    /**
     * @return array
     */
    public function getNames()
    {
        return array_map(
            array(
                $this,
                'getName'
            ),
            $this->getIds()
        );
    }

    /**
     * @param string $id
     * @return string
     */
    public function getName($id)
    {
        switch ($id) {
            case DbTableDefinitionModel::ID:
                return $this->getModel()->getName();
            default:
                return $this->getRelation($id)->getName();
        }
    }

    /**
     * @param string $id
     * @return string
     */
    public function getEngine($id)
    {
        switch ($id) {
            case DbTableDefinitionModel::ID:
                return $this->getModel()->getEngine();
            default:
                return $this->getRelation($id)->getEngine();
        }
    }

    /**
     * @param string $id
     * @return string
     */
    public function getCharset($id)
    {
        switch ($id) {
            case DbTableDefinitionModel::ID:
                return $this->getModel()->getCharset();
            default:
                return $this->getRelation($id)->getCharset();
        }
    }

    /**
     * @param string $id
     * @return string
     */
    public function getCollation($id)
    {
        switch ($id) {
            case DbTableDefinitionModel::ID:
                return $this->getModel()->getCollation();
            default:
                return $this->getRelation($id)->getCollation();
        }
    }

    /**
     * @param string $id
     * @return array
     */
    public function getColumns($id)
    {
        switch ($id) {
            case DbTableDefinitionModel::ID:
                return $this->getModel()->getColumns();
            default:
                return $this->getRelation($id)->getColumns();
        }
    }

    /**
     * @param array $fields
     * @return array
     */
    public function getColumnsFromFields($fields)
    {
        return array_map(
            array(
                $this,
                'getColumnFromField'
            ),
            array_keys($fields),
            $fields
        );
    }

    /**
     * @param string $name
     * @param array  $constraints
     * @return string
     * @throws \PrestaShopException
     */
    public function getColumnFromField($name, $constraints)
    {
        $description = "`$name` ";
        if (empty($constraints['values'])) {
            switch ($constraints['type']) {
                case ObjectModel::TYPE_BOOL:
                    $description .= 'TINYINT(1) UNSIGNED';
                    break;
                case ObjectModel::TYPE_DATE:
                    $description .= 'DATETIME';
                    break;
                case ObjectModel::TYPE_FLOAT:
                    $description .= 'DECIMAL'.(
                        isset($constraints['size'], $constraints['scale'])
                            ? "({$constraints['size']},{$constraints['scale']})"
                            : ''
                    );
                    break;
                case ObjectModel::TYPE_HTML:
                case ObjectModel::TYPE_SQL:
                    $length = isset($constraints['size']) ? $constraints['size'] : null;
                    $length = isset($length['max']) ? $length['max'] : $length;
                    if ($length >= 65535) {
                        $description .= $length ? "TEXT($length)" : 'TEXT';
                    } else {
                        $description .= 'MEDIUMTEXT';
                    }
                    break;
                case ObjectModel::TYPE_INT:
                    $description .= 'INT(10)'.(
                        !empty($constraints['validate'])
                        && strpos(Tools::strtolower($constraints['validate']), 'unsigned')
                            ? ' UNSIGNED'
                            : ' SIGNED'
                    );
                    break;
                case ObjectModel::TYPE_STRING:
                    $length = isset($constraints['size']) ? $constraints['size'] : 255;
                    $length = isset($length['max']) ? $length['max'] : $length;
                    $description .= "VARCHAR($length)";
                    break;
                default:
                    throw new \PrestaShopException("Missing type constraint definition for field $name");
            }
        }
        if (!empty($constraints['values'])) {
            $description .= " ENUM('".implode("','", $constraints['values'])."')";
        }
        if (empty($constraints['allow_null']) || isset($constraints['default']) || !empty($constraints['required'])) {
            $description .= ' NOT NULL';
        }
        if (isset($constraints['default'])) {
            $description .= " DEFAULT '".addslashes($constraints['default'])."'";
        }
        if (!empty($constraints['primary'])) {
            $description .= ' AUTO_INCREMENT';
        }

        return $description;
    }

    /**
     * @param string $id
     * @return array
     */
    public function getKeyPrimary($id)
    {
        switch ($id) {
            case DbTableDefinitionModel::ID:
                return $this->getModel()->getKeyPrimary();
            default:
                return $this->getRelation($id)->getKeyPrimary();
        }
    }

    /**
     * @param string $id
     * @return array
     */
    public function getKeysForeign($id)
    {
        switch ($id) {
            case DbTableDefinitionModel::ID:
                return $this->getModel()->getKeysForeign();
            default:
                return $this->getRelation($id)->getKeysForeign();
        }
    }

    /**
     * @param string $id
     * @return array
     */
    public function getKeysSimple($id)
    {
        switch ($id) {
            case DbTableDefinitionModel::ID:
                return $this->getModel()->getKeysSimple();
            default:
                return $this->getRelation($id)->getKeysSimple();
        }
    }

    /**
     * @param string $id
     * @return array
     */
    public function getKeysUnique($id)
    {
        switch ($id) {
            case DbTableDefinitionModel::ID:
                return $this->getModel()->getKeysUnique();
            default:
                return $this->getRelation($id)->getKeysUnique();
        }
    }

    /**
     * @param string $id
     * @return array
     */
    public function getKeysFulltext($id)
    {
        switch ($id) {
            case DbTableDefinitionModel::ID:
                return $this->getModel()->getKeysFulltext();
            default:
                return $this->getRelation($id)->getKeysFulltext();
        }
    }

    /**
     * @param array $field
     * @return bool
     */
    public function isFieldSimpleKey($field)
    {
        return !empty($field['key']);
    }

    /**
     * @param array $field
     * @return bool
     */
    public function isFieldUniqueKey($field)
    {
        return !empty($field['unique']);
    }

    /**
     * @param array $field
     * @return bool
     */
    public function isFieldFulltextKey($field)
    {
        return !empty($field['fulltext']);
    }
}
