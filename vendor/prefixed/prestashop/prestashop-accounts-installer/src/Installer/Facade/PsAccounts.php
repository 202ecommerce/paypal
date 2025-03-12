<?php

namespace PaypalAddons\PrestaShop\PsAccountsInstaller\Installer\Facade;

use PaypalAddons\PrestaShop\PsAccountsInstaller\Installer\Exception\ModuleNotInstalledException;
use PaypalAddons\PrestaShop\PsAccountsInstaller\Installer\Exception\ModuleVersionException;
use PaypalAddons\PrestaShop\PsAccountsInstaller\Installer\Installer;
use PaypalAddons\PrestaShop\PsAccountsInstaller\Installer\Presenter\InstallerPresenter;

class PsAccounts
{
    /**
     * Available services class names
     */
    const PS_ACCOUNTS_PRESENTER = 'PrestaShop\Module\PsAccounts\Presenter\PsAccountsPresenter';
    const PS_ACCOUNTS_SERVICE = 'PrestaShop\Module\PsAccounts\Service\PsAccountsService';
    const PS_BILLING_SERVICE = 'PrestaShop\Module\PsAccounts\Service\PsBillingService';

    /**
     * @var Installer
     */
    private $installer;

    /**
     * PsAccounts constructor.
     *
     * @param Installer $installer
     */
    public function __construct(Installer $installer)
    {
        $this->installer = $installer;
    }

    /**
     * @param string $serviceName
     *
     * @return mixed
     *
     * @throws ModuleNotInstalledException
     * @throws ModuleVersionException
     */
    public function getService($serviceName)
    {
        if ($this->installer->isModuleInstalled()) {
            if ($this->installer->checkModuleVersion()) {
                return \Module::getInstanceByName($this->installer->getModuleName())
                    ->getService($serviceName);
            }
            throw new ModuleVersionException('The current version of the module "' . $this->installer->getModuleName() . '" is below the required one and should be upgraded. The minimum expected version is: ' . $this->installer->getModuleVersion());
        }
        throw new ModuleNotInstalledException('Module not installed : ' . $this->installer->getModuleName());
    }

    /**
     * @return mixed
     *
     * @throws ModuleNotInstalledException
     * @throws ModuleVersionException
     */
    public function getPsAccountsService()
    {
        return $this->getService(self::PS_ACCOUNTS_SERVICE);
    }

    /**
     * @return mixed
     *
     * @throws ModuleNotInstalledException
     * @throws ModuleVersionException
     */
    public function getPsBillingService()
    {
        return $this->getService(self::PS_BILLING_SERVICE);
    }

    /**
     * @return mixed
     *
     * @throws ModuleNotInstalledException
     * @throws ModuleVersionException
     */
    public function getPsAccountsPresenter()
    {
        if ($this->installer->isModuleInstalled() &&
            $this->installer->checkModuleVersion() &&
            $this->installer->isModuleEnabled()
        ) {
            return $this->getService(self::PS_ACCOUNTS_PRESENTER);
        } else {
            return new InstallerPresenter($this->installer);
        }
    }
}
