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

if (!defined('_PS_VERSION_')) {
    exit;
}

class CloudSyncView
{
    /** @var \PayPal */
    protected $module;
    /** @var \Context */
    protected $context;
    /** @var CloudSyncWrapper */
    protected $cloudSyncWrapper;

    public function __construct()
    {
        /* @phpstan-ignore-next-line */
        $this->module = \Module::getInstanceByName('paypal');
        $this->context = \Context::getContext();
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
        $tpl->assign('urlCloudsync', 'https://assets.prestashop3.com/ext/cloudsync-merchant-sync-consent/latest/cloudsync-cdc.js');
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
