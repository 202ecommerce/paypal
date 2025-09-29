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

namespace PaypalAddons\classes\PrestaShopCloudSync;

use PaypalAddons\Prestashop\ModuleLibMboInstaller\DependencyBuilder;
use PaypalAddons\PrestaShop\PsAccountsInstaller\Installer\Facade\PsAccounts;
use PaypalAddons\PrestaShop\PsAccountsInstaller\Installer\Installer;
use PrestaShop\PrestaShop\Core\Addon\Module\ModuleManagerBuilder;

if (!defined('_PS_VERSION_')) {
    exit;
}

class CloudSyncWrapper
{
    protected $moduleManager;
    /** @var Installer */
    protected $accountInstaller;
    /** @var DependencyBuilder */
    protected $mboInstaller;

    public function __construct()
    {
        $this->moduleManager = ModuleManagerBuilder::getInstance()->build();
        $this->accountInstaller = new Installer('5.0');
        $this->mboInstaller = new DependencyBuilder(\Module::getInstanceByName('paypal'));
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
        $eventbusModule = \Module::getInstanceByName('ps_eventbus');

        return call_user_func([$eventbusModule, 'getService'], 'PrestaShop\Module\PsEventbus\Service\PresenterService');
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
