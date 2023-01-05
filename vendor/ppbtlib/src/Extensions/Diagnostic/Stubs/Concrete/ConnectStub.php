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
use PaypalPPBTlib\Extensions\Diagnostic\Stubs\Handler\ConnectStubHandler;
use PaypalPPBTlib\Extensions\Diagnostic\Stubs\Model\ConnectParameters;

class ConnectStub extends AbstractStub
{
    const SAVE_EVENT = 'saveConnectStub';

    /**
     * @var ConnectParameters
     */
    protected $parameters;

    public function __construct($parameters = [])
    {
        parent::__construct();
        $this->tpl = _PS_MODULE_DIR_ . 'paypal/views/templates/admin/diagnostic/connect.tpl';
        $this->handler = new ConnectStubHandler($this);
        $this->events = [
            self::SAVE_EVENT,
        ];
        $this->parameters = (new ConnectParameters());
        if (!empty($parameters)) {
            $this->parameters->setSyslay(isset($parameters['syslay']) ? $parameters['syslay'] : '');
            $this->parameters->setHolder(isset($parameters['holder']) ? $parameters['holder'] : '');
            $this->parameters->setFolder(isset($parameters['folder']) ? $parameters['folder'] : '');
        }
    }

    public function dispatchEvent($event, $params)
    {
        switch ($event) {
            case self::SAVE_EVENT:
                $this->handler->savePreferences($params);
                break;
            default:
                throw new \RuntimeException('Undefined hook event provided');
        }
    }

    /**
     * @return ConnectParameters|array
     */
    public function getParameters()
    {
        return parent::getParameters();
    }
}
