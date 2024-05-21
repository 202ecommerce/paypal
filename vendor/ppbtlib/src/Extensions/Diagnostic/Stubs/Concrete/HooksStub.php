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
