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
