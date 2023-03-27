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

namespace PaypalPPBTlib\Extensions\Diagnostic;

use PaypalPPBTlib\Extensions\Diagnostic\Stubs\Concrete\ConflictsStub;
use PaypalPPBTlib\Extensions\Diagnostic\Stubs\Concrete\FileIntegrityStub;
use PaypalPPBTlib\Extensions\Diagnostic\Stubs\Concrete\GithubVersionStub;
use PaypalPPBTlib\Extensions\Diagnostic\Stubs\Concrete\HooksStub;
use PaypalPPBTlib\Extensions\Diagnostic\Stubs\Concrete\HostStub;
use PaypalPPBTlib\Extensions\Diagnostic\Stubs\Concrete\OrderStateStub;
use PaypalPPBTlib\Extensions\Diagnostic\Stubs\Concrete\OverridesStub;
use PaypalPPBTlib\Extensions\Diagnostic\Stubs\Concrete\LogsStub;
use PaypalPPBTlib\Extensions\Diagnostic\Stubs\Concrete\ConfigurationStub;
use PaypalPPBTlib\Extensions\Diagnostic\Stubs\Concrete\DatabaseStub;
use PaypalPPBTlib\Extensions\Diagnostic\Stubs\Model\Constant\DiagnosticHook;
use PaypalPPBTlib\Extensions\Diagnostic\Stubs\Storage\DiagnosticRetriever;
use PaypalPPBTlib\Extensions\Diagnostic\Stubs\Storage\StubStorage;
use PaypalPPBTlib\Extensions\AbstractModuleExtension;
use PaypalPPBTlib\Extensions\Diagnostic\Controllers\Admin\AdminDiagnosticController;
use Configuration;

class DiagnosticExtension extends AbstractModuleExtension
{
    public $name = 'diagnostic';

    public $extensionAdminControllers = [
        [
            'name' => [
                'en' => 'Diagnostic',
                'fr' => 'Diagnostique',
            ],
            'class_name' => 'AdminPaypalDiagnostic',
            'parent_class_name' => 'AdminPaypalConfiguration',
            'visible' => true,
        ],
    ];

    public $objectModels = [];

    const MODULE_NAME = 'PAYPAL_MODULE_NAME';

    const DIAGNOSTIC_MODULE_NAME = 'PAYPAL_DIAGNOSTIC_MODULE_NAME';

    const CONNECT_EMPLOYEE = 'PAYPAL_CONNECT_EMPLOYEE';

    const CONNECT_SECURE_KEY = 'PAYPAL_CONNECT_SECRET_KEY';

    const CONNECT_RESTRICTED_IPS = 'PAYPAL_CONNECT_RESTRICTED_IPS';

    const CONNECT_SLUG = 'PAYPAL_CONNECT_SLUG';

    public $hooks = [
        DiagnosticHook::HOOK_CONFLICTS,
        DiagnosticHook::HOOK_CONFLICTS_ACTION,
        DiagnosticHook::HOOK_FIX_MODULE_TABLES,
    ];

    public function install()
    {
        Configuration::updateGlobalValue(self::MODULE_NAME, $this->module->name);
        Configuration::updateGlobalValue(self::DIAGNOSTIC_MODULE_NAME, $this->module->name);

        return parent::install();
    }

    public function initExtension()
    {
        parent::initExtension();

        $stubStorage = StubStorage::getInstance();
        $diagnosticRetriever = new DiagnosticRetriever();
        $diagnosticConfig = $diagnosticRetriever->retrieveCurrent();
        if (empty($diagnosticConfig)) {
            return;
        }

        $stubStorage->setModuleConfigModel($diagnosticConfig);
    }
}
