<?php

namespace PaypalAddons\Prestashop\ModuleLibMboInstaller;

use PrestaShop\PrestaShop\Core\Addon\Module\ModuleManagerBuilder;

class Presenter
{
    /**
     * @return array<string, string|boolean|null>
     */
    public function present()
    {
        /**
         * @var \Module|null
         */
        $mboModule = \Module::getInstanceByName(Installer::MODULE_NAME);

        $version = null;
        if ($mboModule) {
            $version = $mboModule->version;
        }

        $moduleManagerBuilder = ModuleManagerBuilder::getInstance();
        if (is_null($moduleManagerBuilder)) {
            throw new \Exception('ModuleManagerBuilder::getInstance() failed');
        }

        $moduleManager = $moduleManagerBuilder->build();
        if (is_null($moduleManager)) {
            throw new \Exception('ModuleManagerBuilder::build() failed');
        }

        return [
            'isPresentOnDisk' => (bool) $mboModule,
            'isInstalled' => ($mboModule && $moduleManager->isInstalled(Installer::MODULE_NAME)),
            'isEnabled' => ($mboModule && $moduleManager->isEnabled(Installer::MODULE_NAME)),
            'version' => $version,
        ];
    }
}
