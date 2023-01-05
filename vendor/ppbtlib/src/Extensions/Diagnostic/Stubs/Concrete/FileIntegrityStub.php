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
use PaypalPPBTlib\Extensions\Diagnostic\Stubs\Concrete\AbstractStub;
use PaypalPPBTlib\Extensions\Diagnostic\Stubs\Handler\FileIntegrityStubHandler;
use PaypalPPBTlib\Extensions\Diagnostic\Stubs\Model\FileIntegrityParameters;
use PaypalPPBTlib\Extensions\Diagnostic\Stubs\Storage\StubStorage;
use Configuration;
use Module;

class FileIntegrityStub extends AbstractStub
{
    /**
     * @var FileIntegrityParameters
     */
    protected $parameters;

    public function __construct($parameters = [])
    {
        parent::__construct();
        $this->tpl = _PS_MODULE_DIR_ . 'paypal/views/templates/admin/diagnostic/file_integrity.tpl';
        $this->handler = new FileIntegrityStubHandler($this);
        $this->parameters = (new FileIntegrityParameters());
        if (!empty($parameters)) {
            $this->parameters->setRepository(isset($parameters['repository']) ? $parameters['repository'] : '');
        }
    }

    /**
     * @param Module $module
     */
    public function setModule($module)
    {
        $this->module = $module;
        $this->parameters->setModuleVersion($module->version);

        return $this;
    }

    /**
     * @return FileIntegrityParameters|array
     */
    public function getParameters()
    {
        return parent::getParameters();
    }
}
