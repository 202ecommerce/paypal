<?php

namespace PaypalAddons\classes\PrestaShopCloudSync;

use Context;
use Module;
use PayPal;
use PrestaShop\PrestaShop\Core\Addon\Module\ModuleManagerBuilder;
use PrestaShop\PrestaShop\Core\Module\ModuleManager;

class CloudSyncView
{
    /** @var Module|PayPal */
    protected $module;
    /** @var Context */
    protected $context;
    /** @var CloudSyncWrapper */
    protected $cloudSyncWrapper;

    public function __construct()
    {
        $this->module = Module::getInstanceByName('paypal');
        $this->context = Context::getContext();
        $this->cloudSyncWrapper = new CloudSyncWrapper();
    }

    public function render()
    {
        if (false === $this->cloudSyncWrapper->areDependenciesMet()) {
            $tpl = $this->context->smarty->createTemplate(_PS_MODULE_DIR_ . $this->module->name . '/views/templates/admin/_partials/cloud-sync-dependency.tpl');
            $tpl->assign('dependencies', $this->cloudSyncWrapper->getDependencies());

            return $tpl->fetch();
        }

        $eventbusPresenterService = $this->cloudSyncWrapper->getEventbusPresenterService();
        $tpl = $this->context->smarty->createTemplate(_PS_MODULE_DIR_ . $this->module->name . '/views/templates/admin/_partials/cloud-sync.tpl');
        $tpl->assign('module_dir', _PS_MODULE_DIR_ . $this->module->name);
        $tpl->assign('urlAccountsCdn', $this->cloudSyncWrapper->getPsAccountsService()->getAccountsCdn());
        $tpl->assign('urlCloudsync', "https://assets.prestashop3.com/ext/cloudsync-merchant-sync-consent/latest/cloudsync-cdc.js");
        $tpl->assign(
            'JSvars',
            [
                'contextPsAccounts' => $this->cloudSyncWrapper->getPsAccountsPresenter()->present($this->module->name),
                'contextPsEventbus' => $eventbusPresenterService->expose($this->module, ['info', 'modules', 'themes']),
            ]
        );

        return $tpl->fetch();
    }
}
