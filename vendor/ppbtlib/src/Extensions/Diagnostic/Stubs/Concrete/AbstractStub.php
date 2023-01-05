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
