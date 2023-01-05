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

    public function getMonoLanguageConfigurationOrhpans()
    {
        $queries = [];

        $query = 'SELECT * FROM `' . _DB_PREFIX_ . 'configuration_lang`
                  WHERE `id_configuration` NOT IN (SELECT `id_configuration` FROM `' . _DB_PREFIX_ . 'configuration`)
                  OR `id_configuration` IN (SELECT `id_configuration` FROM `' . _DB_PREFIX_ . 'configuration` WHERE name IS NULL OR name = "")';
        $result = Db::getInstance()->executeS($query);
        if (!empty($result)) {
            $fixQueryModel = new FixQueryModel();
            $fixQueryModel->setQuery($query);
            $fixQueryModel->setFixQuery(str_replace('SELECT *', 'DELETE', $query));
            $fixQueryModel->setRows($result);
            $fixQueryModel->setHeaders(array_keys($result[0]));

            $queries[] = $fixQueryModel;
        }

        return $queries;
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

    public function getCartQueries()
    {
        $resultQueries = [];

        $query = 'SELECT * FROM `' . _DB_PREFIX_ . 'cart`
                  WHERE id_cart NOT IN (SELECT id_cart FROM `' . _DB_PREFIX_ . 'orders`)
                  AND date_add < "' . pSQL(date('Y-m-d', strtotime('-1 month'))) . '"';
        $result = Db::getInstance()->executeS($query);

        if (!empty($result)) {
            $fixQueryModel = new FixQueryModel();
            $fixQueryModel->setQuery($query);
            $fixQueryModel->setFixQuery(str_replace('SELECT *', 'DELETE', $query));
            $fixQueryModel->setRows($result);
            $fixQueryModel->setHeaders(array_keys($result[0]));

            $resultQueries[] = $fixQueryModel;
        }

        return $resultQueries;
    }

    public function getCartRulesQueries()
    {
        $resultQueries = [];

        $query = 'SELECT * FROM `' . _DB_PREFIX_ . 'cart_rule`
                  WHERE (
                    active = 0
                    OR quantity = 0
                    OR date_to < "' . pSQL(date('Y-m-d')) . '"
                  )
                  AND date_add < "' . pSQL(date('Y-m-d', strtotime('-1 month'))) . '"';

        $result = Db::getInstance()->executeS($query);

        if (!empty($result)) {
            $fixQueryModel = new FixQueryModel();
            $fixQueryModel->setQuery($query);
            $fixQueryModel->setFixQuery(str_replace('SELECT *', 'DELETE', $query));
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

    public function getCheckAndFixQueries()
    {
        return [
            /*---------------------------
             * 0 => DELETE FROM __table__,
             * 1 => WHERE __id__ NOT IN,
             * 2 => NOT IN __table__,
             * 3 => __id__ used in the "NOT IN" table,
             * 4 => module_name
             */
            ['access', 'id_profile', 'profile', 'id_profile'],
            ['accessory', 'id_product_1', 'product', 'id_product'],
            ['accessory', 'id_product_2', 'product', 'id_product'],
            ['address_format', 'id_country', 'country', 'id_country'],
            ['attribute', 'id_attribute_group', 'attribute_group', 'id_attribute_group'],
            ['carrier_group', 'id_carrier', 'carrier', 'id_carrier'],
            ['carrier_group', 'id_group', 'group', 'id_group'],
            ['carrier_zone', 'id_carrier', 'carrier', 'id_carrier'],
            ['carrier_zone', 'id_zone', 'zone', 'id_zone'],
            ['cart_cart_rule', 'id_cart', 'cart', 'id_cart'],
            ['cart_product', 'id_cart', 'cart', 'id_cart'],
            ['cart_rule_carrier', 'id_cart_rule', 'cart_rule', 'id_cart_rule'],
            ['cart_rule_carrier', 'id_carrier', 'carrier', 'id_carrier'],
            ['cart_rule_combination', 'id_cart_rule_1', 'cart_rule', 'id_cart_rule'],
            ['cart_rule_combination', 'id_cart_rule_2', 'cart_rule', 'id_cart_rule'],
            ['cart_rule_country', 'id_cart_rule', 'cart_rule', 'id_cart_rule'],
            ['cart_rule_country', 'id_country', 'country', 'id_country'],
            ['cart_rule_group', 'id_cart_rule', 'cart_rule', 'id_cart_rule'],
            ['cart_rule_group', 'id_group', 'group', 'id_group'],
            ['cart_rule_lang', 'id_cart_rule', 'cart_rule', 'id_cart_rule'],
            ['cart_rule_lang', 'id_lang', 'lang', 'id_lang'],
            ['cart_rule_product_rule_group', 'id_cart_rule', 'cart_rule', 'id_cart_rule'],
            ['cart_rule_product_rule', 'id_product_rule_group', 'cart_rule_product_rule_group', 'id_product_rule_group'],
            ['cart_rule_product_rule_value', 'id_product_rule', 'cart_rule_product_rule', 'id_product_rule'],
            ['category_group', 'id_category', 'category', 'id_category'],
            ['category_group', 'id_group', 'group', 'id_group'],
            ['category_product', 'id_category', 'category', 'id_category'],
            ['category_product', 'id_product', 'product', 'id_product'],
            ['cms', 'id_cms_category', 'cms_category', 'id_cms_category'],
            ['cms_block', 'id_cms_category', 'cms_category', 'id_cms_category', 'blockcms'],
            ['cms_block_page', 'id_cms', 'cms', 'id_cms', 'blockcms'],
            ['cms_block_page', 'id_cms_block', 'cms_block', 'id_cms_block', 'blockcms'],
            ['connections', 'id_shop_group', 'shop_group', 'id_shop_group'],
            ['connections', 'id_shop', 'shop', 'id_shop'],
            ['connections_page', 'id_connections', 'connections', 'id_connections'],
            ['connections_page', 'id_page', 'page', 'id_page'],
            ['connections_source', 'id_connections', 'connections', 'id_connections'],
            ['customer', 'id_shop_group', 'shop_group', 'id_shop_group'],
            ['customer', 'id_shop', 'shop', 'id_shop'],
            ['customer_group', 'id_group', 'group', 'id_group'],
            ['customer_group', 'id_customer', 'customer', 'id_customer'],
            ['customer_message', 'id_customer_thread', 'customer_thread', 'id_customer_thread'],
            ['customer_thread', 'id_shop', 'shop', 'id_shop'],
            ['customization', 'id_cart', 'cart', 'id_cart'],
            ['customization_field', 'id_product', 'product', 'id_product'],
            ['customized_data', 'id_customization', 'customization', 'id_customization'],
            ['delivery', 'id_shop', 'shop', 'id_shop'],
            ['delivery', 'id_shop_group', 'shop_group', 'id_shop_group'],
            ['delivery', 'id_carrier', 'carrier', 'id_carrier'],
            ['delivery', 'id_zone', 'zone', 'id_zone'],
            ['editorial', 'id_shop', 'shop', 'id_shop', 'editorial'],
            ['favorite_product', 'id_product', 'product', 'id_product', 'favoriteproducts'],
            ['favorite_product', 'id_customer', 'customer', 'id_customer', 'favoriteproducts'],
            ['favorite_product', 'id_shop', 'shop', 'id_shop', 'favoriteproducts'],
            ['feature_product', 'id_feature', 'feature', 'id_feature'],
            ['feature_product', 'id_product', 'product', 'id_product'],
            ['feature_value', 'id_feature', 'feature', 'id_feature'],
            ['group_reduction', 'id_group', 'group', 'id_group'],
            ['group_reduction', 'id_category', 'category', 'id_category'],
            ['homeslider', 'id_shop', 'shop', 'id_shop', 'homeslider'],
            ['homeslider', 'id_homeslider_slides', 'homeslider_slides', 'id_homeslider_slides', 'homeslider'],
            ['hook_module', 'id_hook', 'hook', 'id_hook'],
            ['hook_module', 'id_module', 'module', 'id_module'],
            ['hook_module_exceptions', 'id_hook', 'hook', 'id_hook'],
            ['hook_module_exceptions', 'id_module', 'module', 'id_module'],
            ['hook_module_exceptions', 'id_shop', 'shop', 'id_shop'],
            ['image', 'id_product', 'product', 'id_product'],
            ['message', 'id_cart', 'cart', 'id_cart'],
            ['message_readed', 'id_message', 'message', 'id_message'],
            ['message_readed', 'id_employee', 'employee', 'id_employee'],
            ['module_access', 'id_profile', 'profile', 'id_profile'],
            ['module_country', 'id_module', 'module', 'id_module'],
            ['module_country', 'id_country', 'country', 'id_country'],
            ['module_country', 'id_shop', 'shop', 'id_shop'],
            ['module_currency', 'id_module', 'module', 'id_module'],
            ['module_currency', 'id_currency', 'currency', 'id_currency'],
            ['module_currency', 'id_shop', 'shop', 'id_shop'],
            ['module_group', 'id_module', 'module', 'id_module'],
            ['module_group', 'id_group', 'group', 'id_group'],
            ['module_group', 'id_shop', 'shop', 'id_shop'],
            ['module_preference', 'id_employee', 'employee', 'id_employee'],
            ['orders', 'id_shop', 'shop', 'id_shop'],
            ['orders', 'id_shop_group', 'group_shop', 'id_shop_group'],
            ['order_carrier', 'id_order', 'orders', 'id_order'],
            ['order_cart_rule', 'id_order', 'orders', 'id_order'],
            ['order_detail', 'id_order', 'orders', 'id_order'],
            ['order_detail_tax', 'id_order_detail', 'order_detail', 'id_order_detail'],
            ['order_history', 'id_order', 'orders', 'id_order'],
            ['order_invoice', 'id_order', 'orders', 'id_order'],
            ['order_invoice_payment', 'id_order', 'orders', 'id_order'],
            ['order_invoice_tax', 'id_order_invoice', 'order_invoice', 'id_order_invoice'],
            ['order_return', 'id_order', 'orders', 'id_order'],
            ['order_return_detail', 'id_order_return', 'order_return', 'id_order_return'],
            ['order_slip', 'id_order', 'orders', 'id_order'],
            ['order_slip_detail', 'id_order_slip', 'order_slip', 'id_order_slip'],
            ['pack', 'id_product_pack', 'product', 'id_product'],
            ['pack', 'id_product_item', 'product', 'id_product'],
            ['page', 'id_page_type', 'page_type', 'id_page_type'],
            ['page_viewed', 'id_shop', 'shop', 'id_shop'],
            ['page_viewed', 'id_shop_group', 'shop_group', 'id_shop_group'],
            ['page_viewed', 'id_date_range', 'date_range', 'id_date_range'],
            ['product_attachment', 'id_attachment', 'attachment', 'id_attachment'],
            ['product_attachment', 'id_product', 'product', 'id_product'],
            ['product_attribute', 'id_product', 'product', 'id_product'],
            ['product_attribute_combination', 'id_product_attribute', 'product_attribute', 'id_product_attribute'],
            ['product_attribute_combination', 'id_attribute', 'attribute', 'id_attribute'],
            ['product_attribute_image', 'id_image', 'image', 'id_image'],
            ['product_attribute_image', 'id_product_attribute', 'product_attribute', 'id_product_attribute'],
            ['product_carrier', 'id_product', 'product', 'id_product'],
            ['product_carrier', 'id_shop', 'shop', 'id_shop'],
            ['product_carrier', 'id_carrier_reference', 'carrier', 'id_reference'],
            ['product_country_tax', 'id_product', 'product', 'id_product'],
            ['product_country_tax', 'id_country', 'country', 'id_country'],
            ['product_country_tax', 'id_tax', 'tax', 'id_tax'],
            ['product_download', 'id_product', 'product', 'id_product'],
            ['product_group_reduction_cache', 'id_product', 'product', 'id_product'],
            ['product_group_reduction_cache', 'id_group', 'group', 'id_group'],
            ['product_sale', 'id_product', 'product', 'id_product'],
            ['product_supplier', 'id_product', 'product', 'id_product'],
            ['product_supplier', 'id_supplier', 'supplier', 'id_supplier'],
            ['product_tag', 'id_product', 'product', 'id_product'],
            ['product_tag', 'id_tag', 'tag', 'id_tag'],
            ['range_price', 'id_carrier', 'carrier', 'id_carrier'],
            ['range_weight', 'id_carrier', 'carrier', 'id_carrier'],
            ['referrer_cache', 'id_referrer', 'referrer', 'id_referrer'],
            ['referrer_cache', 'id_connections_source', 'connections_source', 'id_connections_source'],
            ['search_index', 'id_product', 'product', 'id_product'],
            ['search_word', 'id_lang', 'lang', 'id_lang'],
            ['search_word', 'id_shop', 'shop', 'id_shop'],
            ['shop_url', 'id_shop', 'shop', 'id_shop'],
            ['specific_price_priority', 'id_product', 'product', 'id_product'],
            ['specific_price', 'id_product', 'product', 'id_product'],
            ['stock', 'id_warehouse', 'warehouse', 'id_warehouse'],
            ['stock', 'id_product', 'product', 'id_product'],
            ['stock_available', 'id_product', 'product', 'id_product'],
            ['stock_mvt', 'id_stock', 'stock', 'id_stock'],
            ['tab_module_preference', 'id_employee', 'employee', 'id_employee'],
            ['tab_module_preference', 'id_tab', 'tab', 'id_tab'],
            ['tax_rule', 'id_country', 'country', 'id_country'],
            ['warehouse_carrier', 'id_warehouse', 'warehouse', 'id_warehouse'],
            ['warehouse_carrier', 'id_carrier', 'carrier', 'id_carrier'],
            ['warehouse_product_location', 'id_product', 'product', 'id_product'],
            ['warehouse_product_location', 'id_warehouse', 'warehouse', 'id_warehouse']
        ];
    }
}
