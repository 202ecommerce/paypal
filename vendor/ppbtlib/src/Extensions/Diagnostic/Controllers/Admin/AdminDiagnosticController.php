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
        $this->page_header_toolbar_title = sprintf($this->l('Diagnostic %s'), Configuration::get(DiagnosticExtension::MODULE_NAME));
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
