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

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * @param $module PayPal
 *
 * @return bool
 */
function upgrade_module_6_4_3($module)
{
    $kit = new \PaypalAddons\services\Kit();
    $baseDir = _PS_ROOT_DIR_ . '/modules/paypal/';
    $dirs = [
        '_dev',
        'classes/APM',
        'views/templates/apm',
        'views/templates/admin/_partials/paypalBanner',
        'views/templates/admin/_partials/form/fields',
    ];
    $files = [
        'controllers/admin/AdminPayPalCustomizeCheckoutController.php',
        'controllers/admin/AdminPayPalHelpController.php',
        'controllers/admin/AdminPayPalInstallmentController.php',
        'controllers/admin/AdminPayPalLogsController.php',
        'controllers/admin/AdminPayPalPUIListenerController.php',
        'controllers/admin/AdminPayPalSetupController.php',
        'controllers/admin/AdminPaypalGetCredentialsController.php',
        'views/templates/admin/customizeCheckout.tpl',
        'views/templates/admin/help.tpl',
        'views/templates/admin/installment.tpl',
        'views/templates/admin/setup.tpl',
        'views/templates/admin/_partials/accountSettingsBlock.tpl',
        'views/templates/admin/_partials/blockPreviewButtonContext.tpl',
        'views/templates/admin/_partials/block_info.tpl',
        'views/templates/admin/_partials/carrierMap.tpl',
        'views/templates/admin/_partials/ecCredentialFields.tpl',
        'views/templates/admin/_partials/headerLogo.tpl',
        'views/templates/admin/_partials/helperOptionInfo.tpl',
        'views/templates/admin/_partials/mbCredentialsForm.tpl',
        'views/templates/admin/_partials/pppCredentialsForm.tpl',
        'views/templates/admin/_partials/roundingSettings.tpl',
        'views/templates/admin/_partials/switchSandboxBlock.tpl',
        'views/templates/admin/_partials/white-list.tpl',
        'views/templates/admin/_partials/form/colorDescriptions.tpl',
        'views/templates/admin/_partials/form/customizeStyleSection.tpl',
        'views/templates/admin/_partials/form/sectionTitle.tpl',
    ];

    foreach ($dirs as $dir) {
        $kit->rrmdir($baseDir . $dir);
    }
    foreach ($files as $file) {
        $kit->unlink($baseDir . $file);
    }

    return true;
}
