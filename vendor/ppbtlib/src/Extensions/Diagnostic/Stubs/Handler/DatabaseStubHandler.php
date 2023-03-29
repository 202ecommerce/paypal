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

namespace PaypalPPBTlib\Extensions\Diagnostic\Stubs\Handler;

use PaypalPPBTlib\Extensions\Diagnostic\DiagnosticExtension;
use PaypalPPBTlib\Extensions\Diagnostic\Stubs\Concrete\AbstractStub;
use PaypalPPBTlib\Extensions\Diagnostic\Stubs\Handler\AbstractStubHandler;
use PaypalPPBTlib\Extensions\Diagnostic\Stubs\Model\Constant\DiagnosticHook;
use PaypalPPBTlib\Extensions\Diagnostic\Stubs\Model\Constant\TableType;
use PaypalPPBTlib\Extensions\Diagnostic\Stubs\Model\Database\DatabaseError;
use PaypalPPBTlib\Extensions\Diagnostic\Stubs\Model\Database\DatabaseValidator;
use PaypalPPBTlib\Extensions\Diagnostic\Stubs\Model\Database\DefinitionInfo;
use PaypalPPBTlib\Extensions\Diagnostic\Stubs\Model\Database\FieldInfo;
use PaypalPPBTlib\Extensions\Diagnostic\Stubs\Model\Database\FixQueryModel;
use PaypalPPBTlib\Extensions\Diagnostic\Stubs\Model\Database\Handler\PsQueryHandler;
use PaypalPPBTlib\Extensions\Diagnostic\Stubs\Model\Database\TableInfo;
use PaypalPPBTlib\Install\ModuleInstaller;
use PaypalPPBTlib\Utils\Translate\TranslateTrait;
use Category;
use Configuration;
use Db;
use Hook;
use Image;
use Module;
use ObjectModel;

class DatabaseStubHandler extends AbstractStubHandler
{
    use TranslateTrait;

    /**
     * @var DatabaseValidator
     */
    protected $databaseValidator;

    /**
     * @param AbstractStub $stub
     */
    public function __construct(AbstractStub $stub)
    {
        parent::__construct($stub);
        $this->databaseValidator = new DatabaseValidator();
    }

    public function handle()
    {
        $tablesInfo = $this->getTablesInfo();

        return [
            'module_name' => $this->getStub()->getModule()->name,
            'tables' => array_map(function (DefinitionInfo $definitionInfo) {
                return $definitionInfo->toArray();
            }, $tablesInfo),
            'hasDatabaseErrors' => $this->hasDatabaseErrors($tablesInfo),
            'queries' => [],
            'optimizeQueries' => [],
        ];
    }

    protected function getObjectModels()
    {
        $module = $this->getStub()->getModule();

        return $module->objectModels;
    }

    protected function getTablesInfo()
    {
        $tablesInfo = [];
        $objectModels = $this->getObjectModels();
        foreach ($objectModels as $objectModelClass) {
            $definition = $objectModelClass::$definition;
            $tablesInfo[$objectModelClass] = $this->getDefinition($definition);
        }

        return $tablesInfo;
    }

    protected function getDefinition($definition)
    {
        $definitionInfo = new DefinitionInfo();

        $tableDefinition = new TableInfo();
        $tableDefinition->setName($definition['table']);

        if (!$this->databaseValidator->tableExist($definition['table'])) {
            $tableDefinition->addError($this->l('Table doesn\'t exist'));
            $definitionInfo->setTable($tableDefinition);
        } else {
            $tableDefinition->setFields($this->getFieldsInfo($definition, TableType::MAIN));
            $definitionInfo->setTable($tableDefinition);
            $uniqueIndexes = $this->checkUniqueIndexes($definition, TableType::MAIN);
            if (!empty($uniqueIndexes)) {
                $tableDefinition->addError(
                    sprintf(
                        $this->l('Unique index is not valid. Missing indexes: %s'),
                        implode(',', $uniqueIndexes)
                    )
                );
            }
        }

        if (!empty($definition['multilang']) || !empty($definition['multilang_shop'])) {
            $tableDefinition = new TableInfo();
            $tableDefinition->setName($definition['table'] . '_lang');

            if (!$this->databaseValidator->tableExist($definition['table'] . '_lang')) {
                $tableDefinition->addError($this->l('Table doesn\'t exist'));
                $definitionInfo->setLang($tableDefinition);
            } else {
                $tableDefinition->setFields($this->getFieldsInfo($definition, TableType::LANG));
                $definitionInfo->setLang($tableDefinition);
                $uniqueIndexes = $this->checkUniqueIndexes($definition, TableType::LANG);
                if (!empty($uniqueIndexes)) {
                    $tableDefinition->addError(
                        sprintf(
                            $this->l('Unique index is not valid. Missing indexes: %s'),
                            implode(',', $uniqueIndexes)
                        )
                    );
                }
            }
        }

        if (!empty($definition['multishop']) || !empty($definition['multilang_shop'])) {
            $tableDefinition = new TableInfo();
            $tableDefinition->setName($definition['table'] . '_shop');

            if (!$this->databaseValidator->tableExist($definition['table'] . '_shop')) {
                $tableDefinition->addError($this->l('Table doesn\'t exist'));
                $definitionInfo->setShop($tableDefinition);
            } else {
                $tableDefinition->setFields($this->getFieldsInfo($definition, TableType::SHOP));
                $definitionInfo->setShop($tableDefinition);
                $uniqueIndexes = $this->checkUniqueIndexes($definition, TableType::SHOP);
                if (!empty($uniqueIndexes)) {
                    $tableDefinition->addError(
                        sprintf(
                            $this->l('Unique index is not valid. Missing indexes: %s'),
                            implode(',', $uniqueIndexes)
                        )
                    );
                }
            }
        }

        return $definitionInfo;
    }

    protected function checkUniqueIndexes($definition, $tableType = TableType::MAIN)
    {
        $table = $definition['table'];
        if ($tableType == TableType::LANG) {
            $table .= '_lang';
        } elseif ($tableType == TableType::SHOP) {
            $table .= '_shop';
        }

        $definitionFields = $this->getDefinitionFields($definition, $tableType);
        $definitionFields = array_filter($definitionFields, function ($field) {
            return !empty($field['unique']);
        });

        return $this->databaseValidator->compareUniqueIndexes($table, $definitionFields);
    }

    protected function getDefinitionFields($definition, $tableType = TableType::MAIN)
    {
        switch ($tableType) {
            case TableType::MAIN:
                return array_filter($definition['fields'], function ($field) {
                    return empty($field['lang'])
                        && empty($field['shop'])
                        && (empty($field['both']) || $field['both'] == 'shop');
                });
            case TableType::LANG:
                return array_filter($definition['fields'], function ($field) {
                    return !empty($field['lang']);
                });
            case TableType::SHOP:
                return array_filter($definition['fields'], function ($field) {
                    return !empty($field['shop'])
                        || (!empty($field['both']) && $field['both'] == 'shop');
                });
        }

        return [];
    }

    protected function getFieldsInfo($definition, $tableType = TableType::MAIN)
    {
        $fieldsInfo = [];
        $table = $definition['table'];
        if ($tableType == TableType::LANG) {
            $table .= '_lang';
        } elseif ($tableType == TableType::SHOP) {
            $table .= '_shop';
        }

        $definitionFields = $this->getDefinitionFields($definition, $tableType);

        if ($tableType == TableType::MAIN) {
            $primaryField = [
                $definition['primary'] => [
                    'type' => ObjectModel::TYPE_INT,
                    'validate' => 'isUnsignedId',
                    'required' => true,
                    'primary' => true,
                ]
            ];
            $definitionFields = array_merge($primaryField, $definitionFields);
        } else {
            $fields = [
                $definition['primary'] => [
                    'type' => ObjectModel::TYPE_INT,
                    'validate' => 'isUnsignedId',
                    'required' => true,
                ],
            ];
            if ($tableType == TableType::LANG) {
                $fields['id_lang'] = [
                    'type' => ObjectModel::TYPE_INT,
                    'validate' => 'isUnsignedId',
                    'required' => true,
                ];
            } else {
                $fields['id_shop'] = [
                    'type' => ObjectModel::TYPE_INT,
                    'validate' => 'isUnsignedId',
                    'required' => true,
                ];
            }
            $definitionFields = array_merge($fields, $definitionFields);
        }

        foreach ($definitionFields as $column => $definitionField) {
            $fieldInfo = new FieldInfo();
            $fieldInfo->setColumn($column);

            if (!$this->databaseValidator->fieldExists($table, $column)) {
                $fieldInfo->addError(
                    (new DatabaseError())
                        ->setText($this->l("Column $column doesn't exist"))
                );
            } else {
                $errors = $this->databaseValidator->checkFieldArguments($table, $column, $definitionField);
                foreach ($errors as $error) {
                    $fieldInfo->addError($error);
                }
            }

            $fieldsInfo[$column] = $fieldInfo;
        }

        $compareColumns = $this->databaseValidator->compareColumns($table, array_keys($definitionFields));
        foreach ($compareColumns as $compareColumn) {
            $fieldInfo = new FieldInfo();
            $fieldInfo->setColumn($compareColumn);
            $fieldInfo->addError(
                (new DatabaseError())
                    ->setText($this->l('This field should not be present in current table'))
                    ->setActual($this->l('The column is present in table'))
                    ->setFixed($this->l('The column should be deleted manually'))
            );
            $fieldsInfo[$compareColumn] = $fieldInfo;
        }

        return $fieldsInfo;
    }

    protected function hasDatabaseErrors($tables)
    {
        /** @var DefinitionInfo $definition */
        foreach ($tables as $definition) {
            if (!empty($definition->getTable())) {
                if (!empty($definition->getTable()->getErrors())) {
                    return true;
                }
                foreach ($definition->getTable()->getFields() as $field) {
                    if (!empty($field->getErrors())) {
                        return true;
                    }
                }
            }
            if (!empty($definition->getLang())) {
                if (!empty($definition->getLang()->getErrors())) {
                    return true;
                }
                foreach ($definition->getLang()->getFields() as $field) {
                    if (!empty($field->getErrors())) {
                        return true;
                    }
                }
            }
            if (!empty($definition->getShop())) {
                if (!empty($definition->getShop()->getErrors())) {
                    return true;
                }
                foreach ($definition->getShop()->getFields() as $field) {
                    if (!empty($field->getErrors())) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    public function fixModuleTables($params)
    {
        $moduleInstaller = new ModuleInstaller($this->getStub()->getModule());
        $moduleInstaller->installObjectModels();

        $moduleName = Configuration::get(DiagnosticExtension::MODULE_NAME);
        $idModule = Module::getModuleIdByName($moduleName);
        Hook::exec(DiagnosticHook::HOOK_FIX_MODULE_TABLES, [], $idModule, false);

        return true;
    }
}
