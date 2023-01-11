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
use PaypalPPBTlib\Extensions\Diagnostic\Stubs\Interfaces\StubInterface;
use PaypalPPBTlib\Extensions\Diagnostic\Stubs\Handler\OrderStateStubHandler;
use PaypalPPBTlib\Extensions\Diagnostic\Stubs\Model\OrderStateParameters;

class OrderStateStub extends AbstractStub implements StubInterface
{
    const FIX_ORDER_STATE_EVENT = 'fixOrderState';

    const ASSOCIATE_ORDER_STATE_EVENT = 'associateOrderState';

    public function __construct($parameters = [])
    {
        parent::__construct($parameters);
        $this->tpl = _PS_MODULE_DIR_ . 'paypal/views/templates/admin/diagnostic/order_state.tpl';
        $this->handler = new OrderStateStubHandler($this);
        $this->events = [
            self::FIX_ORDER_STATE_EVENT,
            self::ASSOCIATE_ORDER_STATE_EVENT,
        ];
        $this->parameters = (new OrderStateParameters());
        if (!empty($parameters)) {
            $this->parameters->setStatuses(isset($parameters['statuses']) ? $parameters['statuses'] : false);
        }
    }

    public function dispatchEvent($event, $params)
    {
        switch ($event) {
            case self::FIX_ORDER_STATE_EVENT:
                $this->handler->fixOrderState($params['moduleName']);
                break;
            case self::ASSOCIATE_ORDER_STATE_EVENT:
                $this->handler->associateOrderState($params['moduleName'], $params['moduleName']);
                break;
            default:
                throw new \RuntimeException('message d\'erreur');
        }
    }

}
