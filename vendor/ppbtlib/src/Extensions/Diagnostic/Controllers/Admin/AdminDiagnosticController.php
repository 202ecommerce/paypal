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

namespace PaypalPPBTlib\Extensions\Diagnostic\Controllers\Admin;

use PaypalPPBTlib\Extensions\Diagnostic\Stubs\Concrete\AbstractStub;
use PaypalPPBTlib\Extensions\Diagnostic\Stubs\Storage\StubStorage;
use PaypalPPBTlib\Extensions\Diagnostic\DiagnosticExtension;
use PaypalPPBTlib\Module;
use Configuration;
use Context;
use HelperForm;
use PaypalPPBTlib\Extensions\Diagnostic\Stubs\Concrete\GithubVersionStub;
use PaypalPPBTlib\Extensions\Diagnostic\Stubs\Concrete\HooksStub;
use PaypalPPBTlib\Extensions\Diagnostic\Stubs\Concrete\OverridesStub;
use Media;
use Tools;
use ZipArchive;

/**
 * @include 'paypal/views/templates/admin/diagnostic/export.tpl'
 * @include 'paypal/views/js/diagnostic/diagnostic.js.map'
 */
class AdminDiagnosticController extends \ModuleAdminController
{
    public $bootstrap = false;

    public $override_folder;
    /** @var \PayPal */
    public $module;

    /**
     * @var int
     */
    public $multishop_context = 0;

    public $className = 'Configuration';

    public $table = 'configuration';

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);

        $this->addJS(_PS_MODULE_DIR_ . 'paypal/views/js/diagnostic/diagnostic.js');
        $this->addCSS(_PS_MODULE_DIR_ . 'paypal/views/css/diagnostic/diagnostic.css');

        Media::addJsDef([
            $this->module->name => $this->getJsVariables(),
        ]);
    }

    public function initContent()
    {
        $this->content .= $this->renderConfiguration();
        parent::initContent();
    }

    protected function renderConfiguration()
    {
        $tplFile = _PS_MODULE_DIR_ . 'paypal/views/templates/admin/diagnostic/layout.tpl';

        $actionsLink = Context::getContext()->link->getAdminLink(
            $this->controller_name,
            true
        ) . '&stubAction=true';
        $exportStubLink = Context::getContext()->link->getAdminLink(
            $this->controller_name,
            true
        ) . '&stubExport=true';

        Context::getContext()->smarty->assign([
            'actionsLink' => $actionsLink,
            'exportStubLink' => $exportStubLink,
        ]);
        $tpl = Context::getContext()->smarty->createTemplate($tplFile);
        $tpl->assign([
            'stubs' => $this->getStubs(),
            'exportStubLink' => $exportStubLink,
            'actionsLink' => $actionsLink,
        ]);

        return $tpl->fetch();
    }

    protected function getStubs()
    {
        $stubStorage = StubStorage::getInstance();
        $stubs = [];

        if (empty($stubStorage->getModuleConfigModel())) {
            return [];
        }

        /** @var string $stub */
        foreach ($stubStorage->getModuleConfigModel()->getStubs() as $stub => $parameters) {
            /** @var AbstractStub $stubObj */
            $stubObj = new $stub($parameters);
            $stubObj->setModule($this->getStubModule());
            $stubs[] = $stubObj->fetch();
        }

        return $stubs;
    }

    protected function getJsVariables()
    {
        return [
            'actionLink' => Context::getContext()->link->getAdminLink(
                $this->controller_name,
                true
            ) . '&stubAction=true',

        ];
    }

    public function postProcess()
    {
        if (Tools::getIsset('stubAction')) {
            $event = Tools::getValue('event');
            if (empty($event)) {
                Tools::redirectAdmin(Context::getContext()->link->getAdminLink($this->controller_name));
            }
            $stubStorage = StubStorage::getInstance();

            if (!empty($stubStorage->getModuleConfigModel())) {
                /** @var AbstractStub $stub */
                foreach ($stubStorage->getModuleConfigModel()->getStubs() as $stub => $parameters) {
                    $stubObj = new $stub($parameters);
                    $stubObj->setModule($this->getStubModule());
                    if ($stubObj->hasEvent($event)) {
                        $stubObj->dispatchEvent($event, Tools::getAllValues());
                    }
                }
            }

            Tools::redirectAdmin(Context::getContext()->link->getAdminLink($this->controller_name));
        }

        parent::postProcess();
    }

    public function initPageHeaderToolbar()
    {
        parent::initPageHeaderToolbar();
        $this->page_header_toolbar_title = sprintf($this->module->l('Diagnostic %s'), Configuration::get(DiagnosticExtension::MODULE_NAME));
    }

    protected function getStubModule()
    {
        $moduleName = Configuration::get(DiagnosticExtension::MODULE_NAME);
        $module = null;
        if (!empty($moduleName)) {
            $module = Module::getInstanceByName($moduleName);
        }

        return $module;
    }
}
