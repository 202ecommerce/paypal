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
use PaypalPPBTlib\Extensions\Diagnostic\Stubs\Handler\DatabaseStubHandler;
use PaypalPPBTlib\Extensions\Diagnostic\Stubs\Model\DatabaseParameters;

class DatabaseStub extends AbstractStub
{
    /**
     * @var DatabaseParameters
     */
    protected $parameters;

    const FIX_MODULE_TABLES_EVENT = 'fixModuleTables';

    const FIX_TABLES_EVENT = 'fixTables';

    const OPTIMIZE_TABLES_EVENT = 'optimizeTables';

    public function __construct($parameters = [])
    {
        parent::__construct();
        $this->tpl = _PS_MODULE_DIR_ . 'paypal/views/templates/admin/diagnostic/database.tpl';
        $this->handler = new DatabaseStubHandler($this);
        $this->events = [
            self::FIX_TABLES_EVENT,
            self::FIX_MODULE_TABLES_EVENT,
            self::OPTIMIZE_TABLES_EVENT,
        ];
        $this->parameters = (new DatabaseParameters());
        if (!empty($parameters)) {
            $this->parameters->setAllowFix(isset($parameters['fix']) ? $parameters['fix'] : false);
            $this->parameters->setOptimize(isset($parameters['optimize']) ? $parameters['optimize'] : false);
            $this->parameters->setIntegrity(isset($parameters['integrity']) ? $parameters['integrity'] : false);
        }
    }

    public function dispatchEvent($event, $params)
    {
        if ($this->parameters->getAllowFix() === false) {
            return;
        }

        switch ($event) {
            case self::FIX_MODULE_TABLES_EVENT:
                $this->handler->fixModuleTables($params);
                break;
            default:
                throw new \RuntimeException('Undefined hook event provided');
        }
    }
}
