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

namespace PaypalPPBTlib\Extensions\ProcessLogger;

use \Db;
use \Configuration;
use \Hook;

class ProcessLoggerHandler
{
    /**
     * @var array logs
     */
    private static $logs = array();

    /**
     * open logger
     */
    public static function openLogger()
    {
        self::autoErasingLogs();
    }

    /**
     * close logger
     */
    public static function closeLogger()
    {
        self::saveLogsInDb();
    }

    /**
     * @param string $msg (Log message)
     * @param string|null $id_transaction
     * @param int|null $id_order
     * @param int|null $id_cart
     * @param int|null $id_shop
     * @param string|null $tools (Cards, paypal, google ...)
     * @param bool|null $sandbox (Sandbox/Live)
     * @param string $date_transaction date of transaction
     * @param string|null status (info or error)
     */
    public static function addLog(
        $msg,
        $id_transaction = null,
        $id_order = null,
        $id_cart = null,
        $id_shop = null,
        $tools = null,
        $sandbox = null,
        $date_transaction = null,
        $status = null
    )
    {
        self::$logs[] = array(
            'id_order' => (int)$id_order,
            'id_cart' => (int)$id_cart,
            'id_shop' => (int)$id_shop,
            'id_transaction' => pSQL($id_transaction),
            'log' => pSQL($msg),
            'status' => pSQL($status),
            'sandbox' => (int)$sandbox,
            'tools' => pSQL($tools),
            'date_add' => date("Y-m-d H:i:s"),
            'date_transaction' => pSQL($date_transaction)
        );

        if (100 === count(self::$logs)) {
            self::saveLogsInDb();
        }
    }

    /**
     * @param string $msg (Log message)
     * @param string|null $id_transaction
     * @param int|null $id_order
     * @param int|null $id_cart
     * @param int|null $id_shop
     * @param string|null $tools (Cards, paypal, google ...)
     * @param bool|null $sandbox (Sandbox/Live)
     * @param string $date_transaction date of transaction
     */
    public static function logInfo(
        $msg,
        $id_transaction = null,
        $id_order = null,
        $id_cart = null,
        $id_shop = null,
        $tools = null,
        $sandbox = null,
        $date_transaction = null
    )
    {
        self::addLog(
            $msg,
            $id_transaction,
            $id_order,
            $id_cart,
            $id_shop,
            $tools,
            $sandbox,
            $date_transaction,
            $status = 'Info'
        );
    }

    /**
     * @param string $msg (Log message)
     * @param string|null $id_transaction
     * @param int|null $id_order
     * @param int|null $id_cart
     * @param int|null $id_shop
     * @param string|null $tools (Cards, paypal, google ...)
     * @param bool|null $sandbox (Sandbox/Live)
     * @param string $date_transaction date of transaction
     */
    public static function logError(
        $msg,
        $id_transaction = null,
        $id_order = null,
        $id_cart = null,
        $id_shop = null,
        $tools = null,
        $sandbox = null,
        $date_transaction = null
    )
    {
        self::addLog(
            $msg,
            $id_transaction,
            $id_order,
            $id_cart,
            $id_shop,
            $tools,
            $sandbox,
            $date_transaction,
            $status = 'Error'
        );
    }

    /**
     * @return bool
     */
    public static function saveLogsInDb()
    {
        $result = true;
        if (false === empty(self::$logs) && self::getSkippingHooksResult()) {

            Hook::exec(
                    'actionProcessLoggerSave',
                    array(
                        'logs' => &self::$logs,
                    ),
                    null,
                    true
            );
            Hook::exec(
                    'actionPaypalProcessLoggerSave',
                    array(
                        'logs' => &self::$logs,
                    ),
                    null,
                    true
            );

            $result = Db::getInstance()->insert(
                'paypal_processlogger',
                self::$logs
            );

            if ($result) {
                self::$logs = array();
            }
        }

        return $result;
    }

    /**
     * @return bool
     */
    public static function autoErasingLogs()
    {
        if (self::isAutoErasingEnabled()) {
            return Db::getInstance()->delete(
                'paypal_processlogger',
                sprintf(
                    'date_add <= NOW() - INTERVAL %d DAY AND id_order = 0',
                    self::getAutoErasingDelayInDays()
                )
            );
        }

        return true;
    }

    /**
     * @return bool
     */
    public static function isAutoErasingEnabled()
    {
        return false === (bool)Configuration::get('PAYPAL_EXTLOGS_ERASING_DISABLED');
    }

    /**
     * @return int
     */
    public static function getAutoErasingDelayInDays()
    {
        $numberOfDays = Configuration::get('PAYPAL_EXTLOGS_ERASING_DAYSMAX');

        if (empty($numberOfDays) || false === is_numeric($numberOfDays)) {
            return 5;
        }

        return (int)$numberOfDays;
    }

    /**
     * Executes the hooks used to skip a ProcessLogger save. This will return
     * false if any module hooked to either 'actionSkipProcessLoggerSave' or
     * 'actionSkipPaypalProcessLoggerSave' returns false (weak comparison)
     *
     * @return bool
     */
    protected static function getSkippingHooksResult() {

        if (Hook::getIdByName('actionSkipProcessLoggerSave')) {
            $hookProcessLoggerReturnArray = Hook::exec(
                    'actionSkipProcessLoggerSave',
                    array(
                        'logs' => self::$logs,
                    ),
                    null,
                    true
            );

            if (!is_array($hookProcessLoggerReturnArray)) {
                return false;
            }

            if (!empty($hookProcessLoggerReturnArray)) {
                $hookReturn = array_reduce($hookProcessLoggerReturnArray, function($and, $hookReturn) {
                    return $and && (bool)$hookReturn;
                });
                if (!$hookReturn) {
                    return false;
                }
            }
        }

        if (Hook::getIdByName('actionSkipPaypalProcessLoggerSave')) {
            $hookModuleProcessLoggerReturnArray = Hook::exec(
                    'actionSkipPaypalProcessLoggerSave',
                    array(
                        'logs' => self::$logs,
                    ),
                    null,
                    true
            );

            if (!is_array($hookModuleProcessLoggerReturnArray)) {
                return false;
            }

            if (!empty($hookModuleProcessLoggerReturnArray)) {
                $hookReturn = array_reduce($hookModuleProcessLoggerReturnArray, function($and, $hookReturn) {
                    return $and && (bool)$hookReturn;
                });
                if (!$hookReturn) {
                    return false;
                }
            }
        }

        return true;
    }
}
