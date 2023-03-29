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

use PaypalPPBTlib\Utils\Translate\TranslateTrait;
use PaypalPPBTlib\Extensions\Diagnostic\DiagnosticExtension;
use Context;
use Db;
use DbQuery;
use ZipArchive;

class LogsStubHandler extends AbstractStubHandler
{
    use TranslateTrait;

    const LIMITE_DB_NUMBER_OF_DAYS = 30;

    const LIMITE_DB_RAWS_ON_SCREEN = 100;

    const LIMITE_FILE_RAWS_ON_SCREEN = 100;

    const LIMITE_SIZE_FILE = 2*1000000;

    public function handle()
    {
        return [
            'logs' => $this->getListLogs(),
        ];
    }

    protected function getListLogs()
    {
        $logs = [
            'files' => [],
            'db' => [],
        ];

        if (is_dir(_PS_ROOT_DIR_ . '/var/logs')) {
            $logFiles = glob(_PS_ROOT_DIR_ . '/var/logs/*.log');
            if (empty($logFiles) === false) {
                foreach ($logFiles as $file => $path) {
                    $logs['files'][$file]['path'] = $path;
                    $logs['files'][$file]['title'] = sprintf("Log - %s - %s", $path, $this->fileGetSize($path));
                    $logs['files'][$file]['downloadYes'] = filesize($path) < self::LIMITE_SIZE_FILE;
                }
            }
        }

        if (is_dir(_PS_ROOT_DIR_ . '/log')) {
            $logFiles = glob(_PS_ROOT_DIR_ . '/log/*.log');
            if (empty($logFiles) === false) {
                foreach ($logFiles as $file => $path) {
                    $logs['files'][$file]['path'] = $path;
                    $logs['files'][$file]['title'] = sprintf("Log - %s - %s", $path, $this->fileGetSize($path));
                    $logs['files'][$file]['downloadYes'] = filesize($path) < self::LIMITE_SIZE_FILE;
                }
            }
        }

        $logs['db']['prestashop']['whichDb'] = 'prestashop';
        $logs['db']['prestashop']['available'] = true;
        $logs['db']['prestashop']['xLastDays'] = self::LIMITE_DB_NUMBER_OF_DAYS;
        $logs['db']['prestashop']['countLines'] = $this->countTableLogs('log');
        if ($this->isTableExist(\Configuration::get(DiagnosticExtension::MODULE_NAME))) {
            $logs['db']['module']['whichDb'] = 'module';
            $logs['db']['module']['available'] = true;
            $logs['db']['module']['xLastDays'] = self::LIMITE_DB_NUMBER_OF_DAYS;
            $logs['db']['module']['countLines'] = $this->countTableLogs(\Configuration::get(DiagnosticExtension::MODULE_NAME) . '_processlogger');
        }

        return $logs;
    }

    public function loadLogs($data)
    {
        $type = $data['type'];
        $value = $data['value'];
        $listLogs = $this->getListLogs();
        $dbName = array_keys($listLogs['db']);
        $logsContentTpl = _PS_MODULE_DIR_ . 'paypal/views/templates/admin/diagnostic/logs-content.tpl';

        foreach ($listLogs['files'] as $file) {
            $paths[] = $file['path'];
        }

        if (empty($listLogs[$type]) || (!in_array($value, $dbName) && !in_array($value, $paths))) {
            echo json_encode(['content' => Context::getContext()->smarty->fetch($logsContentTpl)]);
            die;
        }

        if ($type == 'files') {
            Context::getContext()->smarty->assign([
                'fileContent' => $this->fileCountLines($value),
            ]);
            echo json_encode(['content' => Context::getContext()->smarty->fetch($logsContentTpl)]);
            die;
        }

        if ($value == 'prestashop') {
            $psLogs = $this->getTableLogs('log');
            if (!empty($psLogs)) {
                Context::getContext()->smarty->assign([
                    'dbContent' => [
                        'headers' => array_keys($psLogs[0]),
                        'content' => $psLogs,
                    ],
                ]);
                echo json_encode(['content' => Context::getContext()->smarty->fetch($logsContentTpl)]);
                die;
            }
        } else {
            $tableLogs = $this->getTableLogs(\Configuration::get(DiagnosticExtension::MODULE_NAME) . '_processlogger');
            if (!empty($tableLogs)) {
                Context::getContext()->smarty->assign([
                    'dbContent' => [
                        'headers' => array_keys($tableLogs[0]),
                        'content' => $tableLogs,
                    ],
                ]);
                echo json_encode(['content' => Context::getContext()->smarty->fetch($logsContentTpl)]);
                die;
            }
        }
    }

    public function downloadLog($data)
    {
        $type = $data['type'];
        $value = $data['value'];
        $listLogs = $this->getListLogs();
        $dbName = array_keys($listLogs['db']);
        $content = '';

        foreach ($listLogs['files'] as $file) {
            $paths[] = $file['path'];
        }

        if (empty($listLogs[$type]) || (!in_array($value, $dbName) && !in_array($value, $paths))) {
            return false;
        }

        if ($type == 'files') {
            $path = basename($value);
            $content = file_get_contents($value);

        } else {
            $tableName = $value == 'prestashop' ? 'log' : \Configuration::get(DiagnosticExtension::MODULE_NAME) . '_processlogger';
            $tableLogs = $this->getTableLogs($tableName);
            $path = $tableName . '.csv';

            if (!empty($tableLogs)) {
                $content .= implode(";", array_keys($tableLogs[0])) . PHP_EOL;
                foreach ($tableLogs as $log) {
                    $content .= implode(";", $log) . PHP_EOL;
                }
            }
        }

        return [
            'path' => $path,
            'content' => $content,
        ];
    }

    public function export($download = true)
    {
        $listLogs = $this->getListLogs();
        $logs = [];

        foreach ($listLogs['files'] as $file) {
            if ($file['downloadYes']) {
                $data = $this->downloadLog([
                    'type' => 'files',
                    'value' => $file['path'],
                ]);
                $logs[$data['path']] = $data['content'];
            }
        }

        foreach ($listLogs['db'] as $db) {
            $data = $this->downloadLog([
                'type' => 'db',
                'value' => $db['whichDb'],
            ]);
            $logs[$data['path']] = $data['content'];
        }

        if (!$download) {
            return $logs;
        }

        $zipPath = _PS_MODULE_DIR_ . 'paypal/export.logs.zip';

        if (file_exists($zipPath)) {
            unlink($zipPath);
        }

        $zip = new ZipArchive();

        if (!$zip->open($zipPath, ZipArchive::CREATE)) {
            throw new \PrestaShopException('Failed to create zip file');
        }

        foreach ($logs as $name => $log) {
            $zip->addFromString($name, $log);
        }

        $zip->close();

        header("Content-type: application/zip");
        header("Content-Disposition: attachment; path=export.Paypal.logs.zip");
        header("Content-length: " . filesize($zipPath));
        header("Pragma: no-cache");
        header("Expires: 0");
        readfile($zipPath);
        unlink($zipPath);
    }

    protected function isTableExist($logName)
    {
        if (empty($logName)) {
            return false;
        }

        $sql = 'SELECT COUNT(*)
                FROM information_schema.tables
                WHERE table_schema = "' . _DB_NAME_ . '"
                AND table_name = "' . _DB_PREFIX_ . bqSQL($logName) . '_processlogger"';

        return !empty(\Db::getInstance()->executeS($sql));
    }

    protected function getTableLogs($table)
    {
        $query = new DbQuery();
        $query->select('*');
        $query->from($table);
        $query->where('date_add BETWEEN DATE_SUB(NOW(), INTERVAL ' . self::LIMITE_DB_NUMBER_OF_DAYS . ' DAY) AND NOW()');
        $query->orderBy('date_add DESC');
        $query->limit(self::LIMITE_DB_RAWS_ON_SCREEN);

        return Db::getInstance()->executeS($query);
    }

    protected function countTableLogs($table)
    {
        $query = new DbQuery();
        $query->select('count(*)');
        $query->from($table);
        $query->where('date_add BETWEEN DATE_SUB(NOW(), INTERVAL ' . self::LIMITE_DB_NUMBER_OF_DAYS . ' DAY) AND NOW()');

        return Db::getInstance()->getValue($query);
    }

    function fileGetSize($path, $isShort = true, $checkIfFileExist = false)
    {
        if ($checkIfFileExist && !file_exists($path)) {
            return 0;
        }

        $size = filesize($path);

        if (empty($size)) {
            return '0 ' . ($isShort ? 'o':'octets');
        }

        $units = [];
        $units[] = ['name' => 'octets',      'abbr' => 'o',  'size' => 1];
        $units[] = ['name' => 'kilo octets', 'abbr' => 'ko', 'size' => 1024];
        $units[] = ['name' => 'mega octets', 'abbr' => 'Mo', 'size' => 1048576];
        $units[] = ['name' => 'giga octets', 'abbr' => 'Go', 'size' => 1073741824];
        $units[] = ['name' => 'tera octets', 'abbr' => 'To', 'size' => 1099511627776];
        $units[] = ['name' => 'peta octets', 'abbr' => 'Po', 'size' => 1125899906842620];

        foreach ($units as $key => $unit){
            if ($size < $unit['size']) {
                return round($size/$units[$key-1]['size'], 2) . ' ' . ($isShort ? $units[$key-1]['abbr'] : $units[$key-1]['name']);
            }
        }

        $units = end($units);
        return round($size / $units['size'], 2) . ' ' . ($isShort ? $units['abbr'] : $units['name']);
    }

    function fileCountLines($path)
    {
        $textLog = "";
        $lines = [];
        $f = fopen($path, "r");

        while(!feof($f)) {
            $line = fgets($f, 4096);
            array_push($lines, $line);
            if (count($lines) >= self::LIMITE_FILE_RAWS_ON_SCREEN +2) {
                array_shift($lines);
            }
        }

        fclose($f);
        foreach ($lines as $l) {
            $textLog = $textLog . $l;
        }

        return $textLog;
    }

}
