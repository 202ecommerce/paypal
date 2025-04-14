<?php

namespace PaypalAddons\PrestaShop\PsAccountsInstaller\Installer;

use PrestaShop\PrestaShop\Adapter\SymfonyContainer;
use PrestaShop\PrestaShop\Core\Addon\Module\ModuleManagerBuilder;

class Installer
{
    const PS_ACCOUNTS_MODULE_NAME = 'ps_accounts';

    /**
     * @var string required version
     */
    private $moduleVersion;

    /**
     * @var string
     */
    private $moduleName = self::PS_ACCOUNTS_MODULE_NAME;

    /**
     * @var \Link
     */
    private $link;

    /**
     * @var mixed
     */
    private $moduleManager;

    /**
     * Installer constructor.
     *
     * @param string $psAccountsVersion
     * @param \Link|null $link
     */
    public function __construct($psAccountsVersion, \Link $link = null)
    {
        $this->moduleVersion = $psAccountsVersion;

        if (null === $link) {
            $link = new \Link();
        }
        $this->link = $link;

        if (true === $this->isShopVersion17()) {
            $moduleManagerBuilder = ModuleManagerBuilder::getInstance();
            $this->moduleManager = $moduleManagerBuilder->build();
        }
    }

    /**
     * Install ps_accounts module if not installed
     * Method to call in every psx modules during the installation process
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function install()
    {
        if (true === $this->isModuleInstalled()) {
            return true;
        }

        if (false === $this->isShopVersion17()) {
            return true;
        }

        return $this->moduleManager->install($this->getModuleName());
    }

    /**
     * @return bool
     */
    public function isModuleInstalled()
    {
        if (false === $this->isShopVersion17()) {
            return \Module::isInstalled($this->getModuleName());
        }

        return $this->moduleManager->isInstalled($this->getModuleName());
    }

    /**
     * @return bool
     */
    public function isModuleEnabled()
    {
        if (false === $this->isShopVersion17()) {
            return \Module::isEnabled($this->getModuleName());
        }

        return $this->moduleManager->isEnabled($this->getModuleName());
    }

    /**
     * @return string|null
     *
     * @throws \PrestaShopException
     */
    public function getInstallLink()
    {
        if ($this->isShopVersion173()) {
            $router = SymfonyContainer::getInstance()->get('router');

            return \Tools::getHttpHost(true) . $router->generate('admin_module_manage_action', [
                'action' => 'install',
                'module_name' => $this->moduleName,
            ]);
        }

        return $this->getAdminLink('AdminModules', true, [], [
            'module_name' => $this->moduleName,
            'install' => $this->moduleName,
        ]);
    }

    /**
     * @return string|null
     *
     * @throws \PrestaShopException
     */
    public function getEnableLink()
    {
        if ($this->isShopVersion173()) {
            $router = SymfonyContainer::getInstance()->get('router');

            return \Tools::getHttpHost(true) . $router->generate('admin_module_manage_action', [
                'action' => 'enable',
                'module_name' => $this->moduleName,
            ]);
        }

        return $this->getAdminLink('AdminModules', true, [], [
            'module_name' => $this->moduleName,
            'enable' => 1,
        ]);
    }

    /**
     * @return string|null
     *
     * @throws \PrestaShopException
     */
    public function getUpgradeLink()
    {
        if ($this->isShopVersion173()) {
            $router = SymfonyContainer::getInstance()->get('router');

            return \Tools::getHttpHost(true) . $router->generate('admin_module_manage_action', [
                'action' => 'upgrade',
                'module_name' => $this->moduleName,
            ]);
        }

        return $this->getAdminLink('AdminModules', true, [], [
            'module_name' => $this->moduleName,
            'upgrade' => $this->moduleName,
        ]);
    }

    /**
     * @return bool
     */
    public function isShopVersion17()
    {
        return version_compare(_PS_VERSION_, '1.7.0.0', '>=');
    }

    /**
     * @return bool
     */
    public function isShopVersion173()
    {
        return version_compare(_PS_VERSION_, '1.7.3.0', '>=');
    }

    /**
     * @return bool
     */
    public function checkModuleVersion()
    {
        $module = \Module::getInstanceByName($this->getModuleName());

        if ($module instanceof \Ps_accounts) {
            return version_compare(
                $module->version,
                $this->moduleVersion,
                '>='
            );
        }

        return false;
    }

    /**
     * @return string
     */
    public function getModuleVersion()
    {
        return $this->moduleVersion;
    }

    /**
     * @return string
     */
    public function getModuleName()
    {
        return $this->moduleName;
    }

    /**
     * Adapter for getAdminLink from prestashop link class
     *
     * @param string $controller controller name
     * @param bool $withToken include or not the token in the url
     * @param array $sfRouteParams
     * @param array $params
     *
     * @return string
     *
     * @throws \PrestaShopException
     */
    protected function getAdminLink($controller, $withToken = true, $sfRouteParams = [], $params = [])
    {
        if ($this->isShopVersion17()) {
            return $this->link->getAdminLink($controller, $withToken, $sfRouteParams, $params);
        }
        $paramsAsString = '';
        foreach ($params as $key => $value) {
            $paramsAsString .= "&$key=$value";
        }

        return \Tools::getShopDomainSsl(true)
            . __PS_BASE_URI__
            . basename(_PS_ADMIN_DIR_)
            . '/' . $this->link->getAdminLink($controller, $withToken)
            . $paramsAsString;
    }
}
