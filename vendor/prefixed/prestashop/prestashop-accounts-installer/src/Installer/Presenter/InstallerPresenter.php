<?php

namespace PaypalAddons\PrestaShop\PsAccountsInstaller\Installer\Presenter;

use PaypalAddons\PrestaShop\PsAccountsInstaller\Installer\Installer;

class InstallerPresenter
{
    /**
     * @var Installer
     */
    private $installer;

    /**
     * @var \Context
     */
    private $context;

    /**
     * InstallerPresenter constructor.
     *
     * @param Installer $installer
     * @param \Context|null $context
     */
    public function __construct(Installer $installer, \Context $context = null)
    {
        $this->installer = $installer;

        if (null === $context) {
            $context = \Context::getContext();
        }
        $this->context = $context;
    }

    /**
     * @return array
     *
     * @throws \Exception
     */
    public function present()
    {
        // Fallback minimal Presenter
        return [
            'psIs17' => $this->installer->isShopVersion17(),

            'psAccountsInstallLink' => $this->installer->getInstallLink(),
            'psAccountsEnableLink' => $this->installer->getEnableLink(),
            'psAccountsUpdateLink' => $this->installer->getUpgradeLink(),

            'psAccountsIsInstalled' => $this->installer->isModuleInstalled(),
            'psAccountsIsEnabled' => $this->installer->isModuleEnabled(),
            'psAccountsIsUptodate' => $this->installer->checkModuleVersion(),

            'onboardingLink' => null,
            'user' => [
                'email' => null,
                'emailIsValidated' => false,
                'isSuperAdmin' => $this->isEmployeeSuperAdmin(),
            ],
            'currentShop' => null,
            'shops' => [],
            'superAdminEmail' => null,
            'ssoResendVerificationEmail' => null,
            'manageAccountLink' => null,
        ];
    }

    /**
     * @return bool
     */
    public function isEmployeeSuperAdmin()
    {
        return $this->context->employee->isSuperAdmin();
    }
}
