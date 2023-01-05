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
