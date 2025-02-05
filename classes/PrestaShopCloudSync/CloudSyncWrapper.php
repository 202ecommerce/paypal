<?php

namespace PaypalAddons\classes\PrestaShopCloudSync;

use Module;
use PaypalAddons\PrestaShop\PsAccountsInstaller\Installer\Facade\PsAccounts;
use PaypalAddons\PrestaShop\PsAccountsInstaller\Installer\Installer;
use Prestashop\ModuleLibMboInstaller\DependencyBuilder;
use PrestaShop\PrestaShop\Core\Addon\Module\ModuleManagerBuilder;
use PrestaShop\PrestaShop\Core\Module\ModuleManager;

class CloudSyncWrapper
{
    /** @var ModuleManager */
    protected $moduleManager;
    /** @var Installer */
    protected $accountInstaller;
    /** @var DependencyBuilder */
    protected $mboInstaller;

    public function __construct()
    {
        $this->moduleManager = ModuleManagerBuilder::getInstance()->build();
        $this->accountInstaller = new Installer('5.0');
        $this->mboInstaller = new DependencyBuilder(Module::getInstanceByName('paypal'));
    }

    public function installModules()
    {
        $this->moduleManager->install('ps_eventbus');
        $this->moduleManager->enable('ps_eventbus');
        $this->accountInstaller->install();
    }

    public function isPsEventbusInstalled()
    {
        return $this->moduleManager->isInstalled('ps_eventbus') && $this->moduleManager->isEnabled('ps_eventbus');
    }

    public function isPsAccountsInstalled()
    {
        return $this->accountInstaller->isModuleInstalled() && $this->accountInstaller->isModuleEnabled();
    }

    public function getPsAccountsService()
    {
        return (new PsAccounts($this->accountInstaller))->getPsAccountsService();
    }

    public function getPsAccountsPresenter()
    {
        return (new PsAccounts($this->accountInstaller))->getPsAccountsPresenter();
    }

    public function getEventbusPresenterService()
    {
        $eventbusModule = Module::getInstanceByName('ps_eventbus');

        return $eventbusModule->getService('PrestaShop\Module\PsEventbus\Service\PresenterService');
    }

    public function areDependenciesMet()
    {
        return $this->mboInstaller->areDependenciesMet();
    }

    public function getDependencies()
    {
        return $this->mboInstaller->handleDependencies();
    }
}
