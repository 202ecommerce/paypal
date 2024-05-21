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

namespace PaypalPPBTlib\Extensions\Diagnostic\Stubs\Handler;

use PaypalPPBTlib\Utils\Translate\TranslateTrait;
use Configuration;
use ConfigurationTest;
use Context;
use Db;
use Tools;

class HostStubHandler extends AbstractStubHandler
{
    use TranslateTrait;

    public function handle()
    {
        return [
            'hostingInfo' => $this->getHostingVars(),
            'shopInfo' => $this->getShopVars(),
            'checks' => $this->getTestResult(),
        ];
    }

    protected function getHostingVars()
    {
        return [
            'version' => [
                'php' => phpversion(),
                'server' => $_SERVER['SERVER_SOFTWARE'],
                'memory_limit' => ini_get('memory_limit'),
                'max_execution_time' => ini_get('max_execution_time')
            ],
            'database' => [
                'version' => Db::getInstance()->getVersion(),
                'server' => _DB_SERVER_,
                'name' => _DB_NAME_,
                'user' => _DB_USER_,
                'prefix' => _DB_PREFIX_,
                'engine' => _MYSQL_ENGINE_,
                'driver' => Db::getClass(),
            ],
            'uname' => function_exists('php_uname')
                ? php_uname('s') . ' ' . php_uname('v') . ' ' . php_uname('m')
                : '',
            'apache_instaweb' => Tools::apacheModExists('mod_instaweb')
        ];
    }

    protected function getShopVars()
    {
        return [
            'shop' => [
                'ps' => _PS_VERSION_,
                'url' => Context::getContext()->shop->getBaseURL(),
                'theme' => Context::getContext()->shop->theme_name,
            ],
            'mail' => Configuration::get('PS_MAIL_METHOD') == 1,
            'smtp' => [
                'server' => Configuration::get('PS_MAIL_SERVER'),
                'user' => Configuration::get('PS_MAIL_USER'),
                'password' => Configuration::get('PS_MAIL_PASSWD'),
                'encryption' => Configuration::get('PS_MAIL_SMTP_ENCRYPTION'),
                'port' => Configuration::get('PS_MAIL_SMTP_PORT'),
            ],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'],
        ];
    }

    /**
     * get all tests
     *
     * @return array of test results
     */
    public function getTestResult()
    {
        $testErrors = [
            'phpversion' => $this->l('Update your PHP version.'),
            'upload' => $this->l('Configure your server to allow file uploads.'),
            'system' => $this->l('Configure your server to allow the creation of directories and files with write permissions.'),
            'gd' => $this->l('Enable the GD library on your server.'),
            'mysql_support' => $this->l('Enable the MySQL support on your server.'),
            'config_dir' => $this->l('Set write permissions for the "config" folder.'),
            'cache_dir' => $this->l('Set write permissions for the "cache" folder.'),
            'sitemap' => $this->l('Set write permissions for the "sitemap.xml" file.'),
            'img_dir' => $this->l('Set write permissions for the "img" folder and subfolders.'),
            'log_dir' => $this->l('Set write permissions for the "log" folder and subfolders.'),
            'mails_dir' => $this->l('Set write permissions for the "mails" folder and subfolders.'),
            'module_dir' => $this->l('Set write permissions for the "modules" folder and subfolders.'),
            'theme_lang_dir' => sprintf($this->l('Set the write permissions for the "themes%s/lang/" folder and subfolders, recursively.'), _THEME_NAME_),
            'translations_dir' => $this->l('Set write permissions for the "translations" folder and subfolders.'),
            'customizable_products_dir' => $this->l('Set write permissions for the "upload" folder and subfolders.'),
            'virtual_products_dir' => $this->l('Set write permissions for the "download" folder and subfolders.'),
            'fopen' => $this->l('Allow the PHP fopen() function on your server.'),
            'register_globals' => $this->l('Set PHP "register_globals" option to "Off".'),
            'gz' => $this->l('Enable GZIP compression on your server.'),
            'files' => $this->l('Some PrestaShop files are missing from your server.'),
            'new_phpversion' => sprintf($this->l('You are using PHP %s version. Soon, the latest PHP version supported by PrestaShop will be PHP 5.4. To make sure you’re ready for the future, we recommend you to upgrade to PHP 5.4 now!'), phpversion())
        ];

        $paramsRequiredResults = ConfigurationTest::check(ConfigurationTest::getDefaultTests());

        if (!defined('_PS_HOST_MODE_')) {
            $paramsOptionalResults = ConfigurationTest::check(ConfigurationTest::getDefaultTestsOp());
        }

        $failRequired = in_array('fail', $paramsRequiredResults);

        if ($failRequired && $paramsRequiredResults['files'] != 'ok') {
            $tmp = ConfigurationTest::test_files(true);
            if (is_array($tmp) && count($tmp)) {
                $testErrors['files'] = $testErrors['files'].'<br/>('.implode(', ', $tmp).')';
            }
        }

        $results = [
            'failRequired' => $failRequired,
            'testsErrors' => $testErrors,
            'testsRequired' => $paramsRequiredResults,
        ];

        if (!defined('_PS_HOST_MODE_')) {
            $results = array_merge($results, [
                'failOptional' => in_array('fail', $paramsOptionalResults),
                'testsOptional' => $paramsOptionalResults,
            ]);
        }

        return $results;
    }
}
