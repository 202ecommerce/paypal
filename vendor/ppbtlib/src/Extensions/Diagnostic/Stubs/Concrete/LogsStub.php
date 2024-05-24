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

namespace PaypalPPBTlib\Extensions\Diagnostic\Stubs\Concrete;

use PaypalPPBTlib\Extensions\Diagnostic\Stubs\Concrete\AbstractStub;
use PaypalPPBTlib\Extensions\Diagnostic\Stubs\Handler\LogsStubHandler;

/**
 * @include 'paypal/views/templates/admin/diagnostic/logs-content.tpl'
 */
class LogsStub extends AbstractStub
{
    const LOAD_LOGS_EVENT = 'loadLogs';

    const DOWNLOAD_LOGS_EVENT = 'downloadLog';

    public function __construct()
    {
        parent::__construct();
        $this->tpl = _PS_MODULE_DIR_ . 'paypal/views/templates/admin/diagnostic/logs.tpl';
        $this->handler = new LogsStubHandler($this);
        $this->events = [
            self::LOAD_LOGS_EVENT,
            self::DOWNLOAD_LOGS_EVENT,
        ];
    }

    public function dispatchEvent($event, $params)
    {
        switch ($event) {
            case self::LOAD_LOGS_EVENT:
                $this->handler->loadLogs($params);
                break;
            case self::DOWNLOAD_LOGS_EVENT:
                $data = $this->handler->downloadLog($params);

                header("Content-Disposition: attachment; filename=\"" . $data['fileName'] . "\"");
                header("Content-Type: application/force-download");
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header("Content-Type: text/plain");

                echo $data['content'];
                exit();
            default:
                throw new \RuntimeException('Undefined hook event provided');
        }
    }
}
