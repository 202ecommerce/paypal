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
