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

namespace PaypalPPBTlib\Extensions\Diagnostic\Stubs\Handler;

use PaypalPPBTlib\Extensions\Diagnostic\DiagnosticExtension;
use PaypalPPBTlib\Extensions\Diagnostic\Stubs\Model\Constant\DiagnosticHook;
use PaypalPPBTlib\Module;
use Configuration;
use Hook;

class ConflictsStubHandler extends AbstractStubHandler
{
    public function handle()
    {
        return [
            'conflicts' => [
                'data' => $this->getConflictsData(),
                'action' => $this->getConflictsAction(),
            ],
        ];
    }

    protected function getConflictsData()
    {
        $moduleName = Configuration::get(DiagnosticExtension::MODULE_NAME);
        $idModule = Module::getModuleIdByName($moduleName);
        $data = Hook::exec(DiagnosticHook::HOOK_CONFLICTS, [], $idModule, true);

        if (empty($data) || empty($data[$moduleName])) {
            return null;
        }

        return $data[$moduleName];
    }

    protected function getConflictsAction()
    {
        $moduleName = Configuration::get(DiagnosticExtension::MODULE_NAME);
        $idModule = Module::getModuleIdByName($moduleName);
        $data = Hook::exec(DiagnosticHook::HOOK_CONFLICTS_ACTION, [], $idModule, false);

        if (empty($data)) {
            return null;
        }

        return $data;
    }
}
