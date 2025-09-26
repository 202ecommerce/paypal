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

use PaypalPPBTlib\Extensions\ProcessLogger\ProcessLoggerHandler;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class PaypalAbstarctModuleFrontController
 */
class PaypalCheckAndHandleWebhookNotificationModuleFrontController extends PaypalAbstarctModuleFrontController
{
    public function run()
    {
        parent::init();

        $token = Tools::getValue('token');

        if (empty($token) || $token !== $this->module->getSecurityKey()) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 401 Unauthorized', true, 401);
            exit;
        }
        try {
            $this->module->getWebhookService()->checkAndHandleNotifications();
            header($_SERVER['SERVER_PROTOCOL'] . ' 200 OK');
            exit;
        } catch (Throwable $e) {
            ProcessLoggerHandler::openLogger();
            ProcessLoggerHandler::logError(
                sprintf(
                    '[%s][%s] Error message: %s. File: %s. Line: %d',
                    self::class,
                    __FUNCTION__,
                    $e->getMessage(),
                    $e->getFile(),
                    $e->getLine()
                )
            );
            ProcessLoggerHandler::closeLogger();
            header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
            exit;
        }
    }
}
