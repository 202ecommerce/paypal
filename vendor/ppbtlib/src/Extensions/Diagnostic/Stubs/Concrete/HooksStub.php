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
use PaypalPPBTlib\Extensions\Diagnostic\Stubs\Handler\HooksStubHandler;
use PaypalPPBTlib\Extensions\Diagnostic\Stubs\Interfaces\StubInterface;
use Hook;
use Tools;

class HooksStub extends AbstractStub implements StubInterface
{
    const FIX_HOOK_EVENT = 'fixHook';

    const FIX_HOOKS_EVENT = 'fixHooks';

    const FIX_ALL_HOOKS_EVENT = 'fixAllHooks';

    public function __construct()
    {
        parent::__construct();
        $this->tpl = _PS_MODULE_DIR_ . 'paypal/views/templates/admin/diagnostic/hooks.tpl';
        $this->handler = new HooksStubHandler($this);
        $this->events = [
            self::FIX_HOOK_EVENT,
            self::FIX_HOOKS_EVENT,
            self::FIX_ALL_HOOKS_EVENT,
        ];
    }

    public function dispatchEvent($event, $params)
    {
        switch ($event) {
            case self::FIX_HOOK_EVENT:
                $this->handler->fixHook($params['hookName'], $params['id_shop']);
                break;
            case self::FIX_HOOKS_EVENT:
                $this->handler->fixHook($params['hookName']);
                break;
            case self::FIX_ALL_HOOKS_EVENT:
                $this->handler->fixAllHooks();
                break;
            default:
                throw new \RuntimeException('Undefined hook event provided');
        }
    }
}
