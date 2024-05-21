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
