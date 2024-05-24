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

use PaypalPPBTlib\Extensions\Diagnostic\DiagnosticExtension;
use PaypalPPBTlib\Extensions\Diagnostic\Stubs\Interfaces\StubHandlerInterface;
use PaypalPPBTlib\Extensions\Diagnostic\Stubs\Interfaces\StubInterface;
use PaypalPPBTlib\Module;
use Configuration;
use RuntimeException;
use Context;

class AbstractStub implements StubInterface
{
    /**
     * @var object
     */
    protected $parameters;

    protected $tpl;

    /**
     * @var bool
     */
    protected $hasExport = true;

    /**
     * @var Module
     */
    protected $module;

    /**
     * @var StubHandlerInterface
     */
    protected $handler;

    protected $events = [];

    public function __construct($parameters = [])
    {
    }

    public function setModule($module)
    {
        $this->module = $module;

        return $this;
    }

    public function fetch()
    {
        if (empty($this->tpl) === true) {
            throw new RuntimeException('Your diagnostic stub "' . get_class($this) . '" need a template.');
        }

        if (empty($this->handler)) {
            throw new RuntimeException('Your diagnostic stub "' . get_class($this) . '" need a handler.');
        }

        $variables = $this->getHandler()->handle();
        Context::getContext()->smarty->assign($variables);

        return Context::getContext()->smarty->fetch($this->tpl);
    }

    /**
     * @param string $event
     * @param array $params
     * @return void
     */
    public function dispatchEvent($event, $params)
    {
    }

    /**
     * @param string $event
     * @return bool
     */
    public function hasEvent($event)
    {
        return in_array($event, $this->getEvents());
    }

    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @return StubHandlerInterface
     */
    public function getHandler()
    {
        return $this->handler;
    }

    /**
     * @return array
     */
    public function getEvents()
    {
        return $this->events;
    }

    /**
     * @return Module
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * @return bool
     */
    public function isHasExport()
    {
        return $this->hasExport;
    }
}
