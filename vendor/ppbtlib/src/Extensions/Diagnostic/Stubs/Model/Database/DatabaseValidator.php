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

use PaypalPPBTlib\Utils\Translate\TranslateTrait;
use Db;
use ObjectModel;

class DatabaseValidator
{
    use TranslateTrait;

    public function tableExist($tableName)
    {
        $result = Db::getInstance()->ExecuteS('SHOW TABLES LIKE "' . bqSQL(_DB_PREFIX_ . $tableName) . '"');

        return !empty($result);
    }

    public function fieldExists($tableName, $fieldName)
    {
        $result = Db::getInstance()->executeS('SHOW COLUMNS FROM `' . bqSQL(_DB_PREFIX_ . $tableName) . '` LIKE \'' . bqSQL($fieldName) . '\';');

        return !empty($result);
    }

    public function checkFieldArguments($tableName, $fieldName, $arguments)
    {
        $errors = [];
        $result = Db::getInstance()->executeS('SHOW FIELDS FROM `' . bqSQL(_DB_PREFIX_ . $tableName) . '` LIKE "' . bqSQL($fieldName) . '";');

        if (empty($result)) {
            $errors[] = (new DatabaseError())
                ->setText($this->l('Field definition not found'));

            return $errors;
        }

        $data = $result[0];

        if ($data['Field'] !== $fieldName) {
            $errors[] = (new DatabaseError())
                ->setText($this->l('Invalid field name'))
                ->setActual($data['Field'])
                ->setFixed($fieldName);
        }

        if (!empty($arguments['values'])) {
            $regex = '/ENUM\(\'' . implode('\',\'', $arguments['values']) . '\'\)/mi';
            if (!preg_match($regex, $data['Type'])) {
                $errors[] = (new DatabaseError())
                    ->setText($this->l('Invalid values'))
                    ->setActual($data['Type'])
                    ->setFixed($regex);
            }
        }

        if (empty($arguments['allow_null']) || isset($arguments['default']) || !empty($arguments['required'])) {
            if (strtolower($data['Null']) !== 'no') {
                $errors[] = (new DatabaseError())
                    ->setText($this->l('Field should be not nullable'))
                    ->setActual($data['Null'])
                    ->setFixed('No');
            }
        }

        if (isset($arguments['default']) && $data['Default'] !== $arguments['default']) {
            $errors[] = (new DatabaseError())
                ->setText($this->l('Default value is not correct'))
                ->setActual($data['Default'])
                ->setFixed($arguments['default']);
        }

        if (!empty($arguments['primary']) && ($data['Key'] !== 'PRI' || strtolower($data['Extra']) !== 'auto_increment')) {
            $errors[] = (new DatabaseError())
                ->setText($this->l('Primary key is not valid'))
                ->setActual($data['Key'])
                ->setFixed('PRI');
        }

        if (empty($arguments['values']) && !empty($arguments['type'])) {
            switch ($arguments['type']) {
                case ObjectModel::TYPE_BOOL:
                    if (!preg_match('/^tinyint.*$/mi', $data['Type'], $result)) {
                        $errors[] = (new DatabaseError())
                            ->setText($this->l('The type is not bool'))
                            ->setActual($data['Type'])
                            ->setFixed('tinyint(1)');
                    }
                    break;
                case ObjectModel::TYPE_DATE:
                    if (!preg_match('/^datetime$/mi', $data['Type'], $result)) {
                        $errors[] = (new DatabaseError())
                            ->setText($this->l('The type is not date'))
                            ->setActual($data['Type'])
                            ->setFixed('datetime');
                    }
                    break;
                case ObjectModel::TYPE_FLOAT:
                    $fixed = 'decimal';
                    if (isset($arguments['size'], $arguments['scale'])) {
                        $fixed .= '(' . $arguments['size'] . ',' . $arguments['scale'] . ')';
                    } else {
                        $fixed .= '(10,0)';
                    }
                    if (strtolower($data['Type']) != $fixed) {
                        $errors[] = (new DatabaseError())
                            ->setText($this->l('The type is not correct decimal'))
                            ->setActual($data['Type'])
                            ->setFixed($fixed);
                    }
                    break;
                case ObjectModel::TYPE_HTML:
                    $length = isset($arguments['size']) ? $arguments['size'] : null;
                    $length = isset($length['max']) ? $length['max'] : $length;
                    if ($length >= 65535) {
                        $fixed = 'text';
                    } else {
                        $fixed = 'mediumtext';
                    }

                    if (strtolower($data['Type']) != $fixed) {
                        $errors[] = (new DatabaseError())
                            ->setText($this->l('The type is not correct html'))
                            ->setActual($data['Type'])
                            ->setFixed($fixed);
                    }
                    break;
                case ObjectModel::TYPE_INT:
                    $regex = '/int(\((\d+)\))?' . (!empty($arguments['validate']) && strpos(strtolower($arguments['validate']), 'unsigned') ? ' unsigned' : '') . '/mi';

                    if (!preg_match($regex, strtolower($data['Type']))) {
                        $errors[] = (new DatabaseError())
                            ->setText($this->l('The type is not int'))
                            ->setActual($data['Type'])
                            ->setFixed($regex);
                    }

                    break;
                case ObjectModel::TYPE_STRING:
                    $length = isset($arguments['size']) ? $arguments['size'] : 255;
                    $length = isset($length['max']) ? $length['max'] : $length;
                    $fixed = "varchar($length)";

                    if (strtolower($data['Type']) != $fixed) {
                        $errors[] = (new DatabaseError())
                            ->setText($this->l('The type is not string'))
                            ->setActual($data['Type'])
                            ->setFixed($fixed);
                    }

                    break;
                default:
                    $errors[] = (new DatabaseError())
                        ->setText($this->l('Missing type constraint definition for field'));
            }
        }

        return $errors;
    }

    public function compareColumns($tableName, $fields)
    {
        $result = Db::getInstance()->executeS('SHOW COLUMNS FROM `' . bqSQL(_DB_PREFIX_ . $tableName) . '`');

        if (empty($result)) {
            return [];
        }

        $result = array_map(function ($item) {
            return $item['Field'];
        }, $result);

        return array_diff($result, $fields);
    }

    public function compareUniqueIndexes($tableName, $fields)
    {
        $query = 'SELECT group_concat(column_name order by seq_in_index) as index_columns
                  FROM information_schema.statistics
                  where TABLE_NAME = "' . bqSQL(_DB_PREFIX_ . $tableName) . '"
                      AND INDEX_SCHEMA = "' . _DB_NAME_ . '"
                      AND non_unique = 0
                      AND INDEX_NAME <> "PRIMARY"
                  group by index_schema,
                           index_name,
                           index_type,
                           table_name
                  order by index_schema,
                           index_name;';
        $indexes = Db::getInstance()->executeS($query);

        if (empty($indexes)) {
            return null;
        }

        $indexes = array_map(function ($index) {
            $index = explode(',', $index['index_columns']);

            return array_map(function ($i) {
                return trim($i);
            }, $index);
        }, $indexes);

        foreach ($indexes as $index) {
            $dbIndexes = array_keys($fields);
            return array_diff($dbIndexes, $index);
        }
    }
}
