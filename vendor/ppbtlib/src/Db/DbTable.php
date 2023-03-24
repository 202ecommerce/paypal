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

use \Tools;

class DbTable
{
    /**
     * Key identifiers.
     *
     * @var int
     */
    const PRIMARY  = 1;
    const FOREIGN  = 2;
    const UNIQUE   = 3;
    const FULLTEXT = 4;

    /** @var Db */
    protected $db;
    /** @var string */
    protected $name;
    /** @var string */
    protected $engine;
    /** @var string */
    protected $charset;
    /** @var string */
    protected $collation;
    /** @var array */
    protected $columns;
    /** @var array */
    protected $schema;
    /** @var array */
    protected $keys;

    /**
     * Register Db
     * @param Db $db
     */
    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Hydrate properties
     * @param PaypalPPBTlib\Db\DbSchema $schema
     * @return PaypalPPBTlib\Db\DbTable
     */
    public function hydrate($schema)
    {
        $this->schema = $schema;

        return $schema->map($this);
    }

    /**
     * Create table
     * @return bool
     */
    public function create()
    {
        $tableExists = $this->db->executeS("SHOW TABLES LIKE '$this->name'");
        if ($tableExists == false) {
            $keys = array();
            foreach ($this->keys as $modelDef) {
                if (strpos($modelDef, 'FOREIGN KEY') === 0) {
                    continue;
                }
                $keys[] = $modelDef;
            }
            $result = $this->db->execute("CREATE TABLE IF NOT EXISTS `$this->name` (".
                            implode(', ', array_merge($this->columns, $keys)).
                        ") ENGINE=$this->engine CHARSET=$this->charset;");
            if ($result == true) {
                $this->alterKeys();
            }
            return $result;
        }
        // table exists
        $alter = $this->alterFields();

        if (!empty($alter)) {
            $result = true;

            foreach ($alter as $request) {
                $result &= $this->db->execute($request);
            }

            return $result;
        }
        $this->alterKeys();

        return true;
    }

    /**
     * Alter table fields
     * @return string
     */
    private function alterFields()
    {
        $describe = $this->db->executeS("SHOW COLUMNS FROM `$this->name`");

        foreach ($describe as $key => $col) {
            $describe[$key]['modelDef'] = '`'.$col['Field'].'` '.Tools::strtoupper($col['Type']).' ';
            if ('NO' === $col['Null']) {
                $describe[$key]['modelDef'] .= 'NOT NULL ';
            }
            if (false === empty($col['Extra'])) {
                $describe[$key]['modelDef'] .= Tools::strtoupper($col['Extra']);
            }
        }

        $alterToSkip = array();
        $alterToExecute = array();
        $alters = array();
        foreach ($this->columns as $key => $column) {
            foreach ($describe as $col) {
                if (trim($column) === trim($col['modelDef'])) {
                    $alterToSkip[$key] = true;
                } elseif (false !== strpos($column, '`' . $col['Field'] . '`')) {
                    $alterToExecute[$key] = 'MODIFY';
                    $alters[$key] = "ALTER TABLE `$this->name` MODIFY $column;";
                }
            }
            if (empty($alterToExecute[$key]) && empty($alterToSkip[$key])) {
                $alterToExecute[$key]['action'] = 'ADD '.$column;
                $alters[$key] =  "ALTER TABLE `$this->name` ADD $column;";
            }
        }

        return $alters;
    }

    /**
     * Alter table keys
     * @return string
     */
    private function alterKeys()
    {
        $describe = $this->db->executeS("SHOW KEYS FROM `$this->name`");

        foreach ($describe as $k => $key) {
            if ($key['Key_name'] != 'PRIMARY') {
                try {
                    $this->db->execute("ALTER TABLE `$this->name` DROP KEY `".$key['Key_name']."`; ");
                } catch (\Exception $e) {
                    continue;
                }
            }
        }

        if (version_compare(_PS_VERSION_, '1.7', '>')) {
            $i = 0;
            foreach ($this->keys as $modelDef) {
                if (strpos($modelDef, 'FOREIGN KEY') === 0) {
                    $i++;
                    try {
                        $this->db->execute("ALTER TABLE `$this->name` DROP FOREIGN KEY `".$this->name . "_ibfk_$i`;");
                    } catch (\Exception $e) {
                        continue;
                    }
                }
            }
        }

        foreach ($this->keys as $modelDef) {
            if (strpos($modelDef, 'PRIMARY KEY') === 0) {
                continue;
            }
            try {
                if (strpos($modelDef, 'FOREIGN KEY') === 0 && version_compare(_PS_VERSION_, '1.7', '<')) {
                    continue;
                }
                $this->db->execute("ALTER TABLE `$this->name` ADD ".$modelDef.";");
            } catch (\Exception $e) {
                continue;
            }
        }

        return true;
    }

    /**
     * Drop table
     * @return bool
     */
    public function drop()
    {
        return $this->db->execute("DROP TABLE IF EXISTS `$this->name`;");
    }

    /**
     * @param string $name
     * @return PaypalPPBTlib\Db\DbTable
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @param string $engine
     * @return PaypalPPBTlib\Db\DbTable
     */
    public function setEngine($engine)
    {
        $this->engine = $engine;

        return $this;
    }

    /**
     * @param string $charset
     * @return PaypalPPBTlib\Db\DbTable
     */
    public function setCharset($charset)
    {
        $this->charset = $charset;

        return $this;
    }

    /**
     * @param string $collation
     * @return PaypalPPBTlib\Db\DbTable
     */
    public function setCollation($collation)
    {
        $this->collation = $collation;

        return $this;
    }

    /**
     * @param array $columns
     * @return PaypalPPBTlib\Db\DbTable
     */
    public function setColumns($columns)
    {
        $this->columns = $columns;

        return $this;
    }

    /**
     * @param array $columns
     * @return PaypalPPBTlib\Db\DbTable
     */
    public function setKeyPrimary($columns)
    {
        return $this->setKey($columns, static::PRIMARY);
    }

    /**
     * @param array $keys
     * @return PaypalPPBTlib\Db\DbTable
     */
    public function setKeysForeign($keys)
    {
        foreach ($keys as $columns) {
            $this->setKeyForeign($columns);
        }

        return $this;
    }

    /**
     * @param array $columns
     * @return PaypalPPBTlib\Db\DbTable
     */
    public function setKeyForeign($columns)
    {
        return $this->setKey($columns, static::FOREIGN);
    }

    /**
     * @param array $keys
     * @return PaypalPPBTlib\Db\DbTable
     */
    public function setKeysUnique($keys)
    {
        return $this->setKeyUnique(array_keys($keys));
    }

    /**
     * @param array $columns
     * @return PaypalPPBTlib\Db\DbTable
     */
    public function setKeyUnique($columns)
    {
        return $this->setKey($columns, static::UNIQUE);
    }

    /**
     * @param array $keys
     * @return PaypalPPBTlib\Db\DbTable
     */
    public function setKeysFulltext($keys)
    {
        return $this->setKeyFulltext(array_keys($keys));
    }

    /**
     * @param array $columns
     * @return PaypalPPBTlib\Db\DbTable
     */
    public function setKeyFulltext($columns)
    {
        return $this->setKey($columns, static::FULLTEXT);
    }

    /**
     * @param array $keys
     * @return PaypalPPBTlib\Db\DbTable
     */
    public function setKeysSimple($keys)
    {
        return $this->setKeySimple(array_keys($keys));
    }

    /**
     * @param array $columns
     * @return PaypalPPBTlib\Db\DbTable
     */
    public function setKeySimple($columns)
    {
        return $this->setKey($columns);
    }

    /**
     * @param array    $columns
     * @param int|null $type
     * @return PaypalPPBTlib\Db\DbTable
     */
    protected function setKey($columns, $type = null)
    {
        // Empty columns may be returned by `array_filter`s.
        if (empty($columns)) {
            return $this;
        }

        $name = implode('_', $columns);
        $columns = implode('`, `', $columns);
        switch ($type) {
            case static::PRIMARY:
                $this->keys[] = "PRIMARY KEY (`$columns`)";
                break;
            case static::FOREIGN:
                list($table, $columns) = explode('.', $name);
                $this->keys[] = "FOREIGN KEY (`$columns`) REFERENCES $table (`$columns`)
                ON UPDATE CASCADE ON DELETE CASCADE";
                break;
            case static::UNIQUE:
                $this->keys[] = "UNIQUE KEY (`$columns`)";
                break;
            case static::FULLTEXT:
                $this->keys[] = "FULLTEXT KEY (`$columns`)";
                break;
            default:
                $this->keys[] = "KEY (`$columns`)";
                break;
        }

        return $this;
    }
}
