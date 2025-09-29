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

use PaypalPPBTlib\Extensions\Diagnostic\Stubs\Concrete\FileIntegrityStub;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * @param PayPal $module
 *
 * @return bool
 */
function upgrade_module_6_4_3(PayPal $module)
{
    $baseDir = _PS_MODULE_DIR_ . 'paypal/';
    $diagnosticConf = include _PS_MODULE_DIR_ . 'paypal/diagnostic.php';

    if (empty($diagnosticConf[0]['stubs'][FileIntegrityStub::class])) {
        return true;
    }

    $stub = new FileIntegrityStub($diagnosticConf[0]['stubs'][FileIntegrityStub::class]);
    $stub->setModule($module);
    $response = $stub->getHandler()->handle();

    if (empty($response['created'])) {
        return true;
    }

    $files = array_filter(
        $response['created'],
        function ($file) {
            return !preg_match('/^config_[a-z]+\.xml$/', $file) && $file !== 'config.xml';
        });

    foreach ($files as $file) {
        try {
            unlink($baseDir . $file);
        } catch (Exception $e) {
        } catch (Throwable $e) {
        }
    }

    return true;
}
