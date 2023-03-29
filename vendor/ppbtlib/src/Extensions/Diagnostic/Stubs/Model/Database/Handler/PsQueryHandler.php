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

namespace PaypalPPBTlib\Extensions\Diagnostic\Stubs\Model\Database\Handler;

use PaypalPPBTlib\Extensions\Diagnostic\Stubs\Model\Database\FixQueryModel;
use PaypalPPBTlib\Utils\Translate\TranslateTrait;
use Context;
use Db;
use Module;
use Tools;

class PsQueryHandler
{
    use TranslateTrait;

    public function getConfigurationDuplicates()
    {
        $queryModels = [];
        $filteredConfiguration = [];
        $result = Db::getInstance()->executeS('SELECT * FROM ' . _DB_PREFIX_ . 'configuration');
        foreach ($result as $row) {
            $key = $row['id_shop_group'] . '-|-' . $row['id_shop'] . '-|-' . $row['name'];
            if (in_array($key, $filteredConfiguration)) {
                $fixQueryModel = new FixQueryModel();
                $configQuery = 'SELECT * FROM ' . _DB_PREFIX_ . 'configuration WHERE id_configuration = ' . (int) $row['id_configuration'];
                $deleteQuery = 'DELETE FROM ' . _DB_PREFIX_ . 'configuration WHERE id_configuration = ' . (int) $row['id_configuration'];
                $fixQueryModel->setFixQuery($deleteQuery);
                $fixQueryModel->setQuery($configQuery);

                $configQueryResult = Db::getInstance()->executeS($configQuery);
                if (!empty($configQueryResult)) {
                    $fixQueryModel->setHeaders(array_keys($configQueryResult[0]));
                    $fixQueryModel->setRows($configQueryResult);
                }
                $queryModels[] = $fixQueryModel;
            } else {
                $filteredConfiguration[] = $key;
            }
        }

        return $queryModels;
    }

    public function getPSTablesQueries()
    {
        $resultQueries = [];
        $queries = $this->getCheckAndFixQueries();

        $queries = $this->sort($queries);
        foreach ($queries as $queryArray) {
            if (isset($queryArray[4]) && !Module::isInstalled($queryArray[4])) {
                continue;
            }

            $query = 'SELECT SQL_CALC_FOUND_ROWS *
                      FROM `' . _DB_PREFIX_ . $queryArray[0] . '`
                      WHERE  `' . $queryArray[1] . '` != 0
                      AND `' . $queryArray[1] . '` NOT IN (
                        SELECT `' . $queryArray[3] . '`
                        FROM `' . _DB_PREFIX_ . $queryArray[2] . '`)
                      LIMIT 5';

            $queryNbRows = 'SELECT FOUND_ROWS()';

            $queryResult = Db::getInstance()->executeS($query);
            $nbRows = Db::getInstance()->getValue($queryNbRows);

            if (!empty($queryResult)) {
                $fixQueryModel = new FixQueryModel();
                $fixQueryModel->setQuery($query);
                $fixQueryModel->setFixQuery(str_replace(
                    ['SELECT SQL_CALC_FOUND_ROWS *', '`' . $queryArray[1] . '` != 0 AND `', 'LIMIT 5'],
                    ['DELETE', '', ''], $query));
                $fixQueryModel->setRows($queryResult);
                $fixQueryModel->setHeaders(array_keys($queryResult[0]));
                $fixQueryModel->setCountRows($nbRows);
                $resultQueries[] = $fixQueryModel;
            }
        }

        return $resultQueries;
    }

    public function getLangTableQueries()
    {
        $resultQueries = [];
        $tables = Db::getInstance()->executeS('SHOW TABLES LIKE "' . _DB_PREFIX_ . '%_lang"');

        foreach ($tables as $table) {
            $tableLang = current($table);
            $table = preg_replace('/_lang$/', '', $tableLang);
            $idTable = 'id_' . preg_replace('/^' . _DB_PREFIX_ . '/', '', $table);

            $query = 'SELECT * FROM `' . bqSQL($tableLang) . '`
                      WHERE `' . bqSQL($idTable) . '` NOT IN
                      (SELECT `' . bqSQL($idTable) . '` FROM `' . bqSQL($table) . '`) LIMIT 5';
            $result = Db::getInstance()->executeS($query);
            if (!empty($result)) {
                $fixQueryModel = new FixQueryModel();
                $fixQueryModel->setQuery($query);
                $fixQueryModel->setFixQuery(str_replace(['SELECT *', 'LIMIT 5'], ['DELETE', ''], $query));
                $fixQueryModel->setRows($result);
                $fixQueryModel->setHeaders(array_keys($result[0]));

                $resultQueries[] = $fixQueryModel;
            }

            $query = 'SELECT * FROM `' . bqSQL($tableLang) . '`
                      WHERE `id_lang` NOT IN
                      (SELECT `id_lang` FROM `' . _DB_PREFIX_ . 'lang`) LIMIT 5';
            $result = Db::getInstance()->executeS($query);

            if (!empty($result)) {
                $fixQueryModel = new FixQueryModel();
                $fixQueryModel->setQuery($query);
                $fixQueryModel->setFixQuery(str_replace(['SELECT *', 'LIMIT 5'], ['DELETE', ''], $query));
                $fixQueryModel->setRows($result);
                $fixQueryModel->setHeaders(array_keys($result[0]));

                $resultQueries[] = $fixQueryModel;
            }
        }

        return $resultQueries;
    }

    public function getShopTableQueries()
    {
        $resultQueries = [];
        $tables = Db::getInstance()->executeS('SHOW TABLES LIKE "' . _DB_PREFIX_ . '%_shop"');

        foreach ($tables as $table) {
            $tableShop = current($table);
            $table = preg_replace('/_shop$/', '', $tableShop);
            $idTable = 'id_' . preg_replace('/^' . _DB_PREFIX_ . '/', '', $table);

            if (in_array($tableShop, array(_DB_PREFIX_ . 'carrier_tax_rules_group_shop'))) {
                continue;
            }

            $query = 'SELECT SQL_CALC_FOUND_ROWS *
                      FROM `' . bqSQL($tableShop) . '`
                      WHERE `' . bqSQL($idTable) . '` NOT IN (
                        SELECT `' . bqSQL($idTable) . '`
                        FROM `' . bqSQL($table) . '`)
                      LIMIT 5';

            $queryNbRows = 'SELECT FOUND_ROWS()';

            $result = Db::getInstance()->executeS($query);
            $nbRows = Db::getInstance()->getValue($queryNbRows);

            if (!empty($result)) {
                $fixQueryModel = new FixQueryModel();
                $fixQueryModel->setQuery($query);
                $fixQueryModel->setFixQuery(str_replace(['SELECT SQL_CALC_FOUND_ROWS *', 'LIMIT 5'], ['DELETE', ''], $query));
                $fixQueryModel->setRows($result);
                $fixQueryModel->setHeaders(array_keys($result[0]));
                $fixQueryModel->setCountRows($nbRows);

                $resultQueries[] = $fixQueryModel;
            }

            $query = 'SELECT SQL_CALC_FOUND_ROWS *
                      FROM `' . bqSQL($tableShop) . '`
                      WHERE `id_shop` NOT IN (
                        SELECT `id_shop`
                        FROM `' . _DB_PREFIX_ . 'shop`)
                      LIMIT 5';

            $queryNbRows = 'SELECT FOUND_ROWS()';

            $result = Db::getInstance()->executeS($query);
            $nbRows = Db::getInstance()->getValue($queryNbRows);

            if (!empty($result)) {
                $fixQueryModel = new FixQueryModel();
                $fixQueryModel->setQuery($query);
                $fixQueryModel->setFixQuery(str_replace(['SELECT SQL_CALC_FOUND_ROWS *', 'LIMIT 5'], ['DELETE', ''], $query));
                $fixQueryModel->setRows($result);
                $fixQueryModel->setHeaders(array_keys($result[0]));
                $fixQueryModel->setCountRows($nbRows);

                $resultQueries[] = $fixQueryModel;
            }
        }

        return $resultQueries;
    }

    public function getStockAvailableQueries()
    {
        $resultQueries = [];
        $query = 'SELECT * FROM `' . _DB_PREFIX_ . 'stock_available`
                  WHERE `id_shop` NOT IN
                  (SELECT `id_shop` FROM `' . _DB_PREFIX_ . 'shop`)
                  AND `id_shop_group` NOT IN (SELECT `id_shop_group`
                  FROM `' . _DB_PREFIX_ . 'shop_group`) LIMIT 5';

        $result = Db::getInstance()->executeS($query);
        if (!empty($result)) {
            $fixQueryModel = new FixQueryModel();
            $fixQueryModel->setQuery($query);
            $fixQueryModel->setFixQuery(str_replace(['SELECT *', 'LIMIT 5'], ['DELETE', ''], $query));
            $fixQueryModel->setRows($result);
            $fixQueryModel->setHeaders(array_keys($result[0]));

            $resultQueries[] = $fixQueryModel;
        }

        return $resultQueries;
    }

    public function clearAllCaches()
    {
        $index = file_exists(_PS_TMP_IMG_DIR_ . 'index.php')
            ? file_get_contents(_PS_TMP_IMG_DIR_ . 'index.php')
            : '';
        Tools::deleteDirectory(_PS_TMP_IMG_DIR_, false);
        file_put_contents(_PS_TMP_IMG_DIR_ . 'index.php', $index);
        Context::getContext()->smarty->clearAllCache();
    }

    protected function sort($array)
    {
        $sorted = false;
        $size = count($array);
        while (!$sorted) {
            $sorted = true;
            for ($i = 0; $i < $size - 1; ++$i) {
                for ($j = $i + 1; $j < $size; ++$j) {
                    if ($array[$i][2] == $array[$j][0]) {
                        $tmp = $array[$i];
                        $array[$i] = $array[$j];
                        $array[$j] = $tmp;
                        $sorted = false;
                    }
                }
            }
        }

        return $array;
    }
}
