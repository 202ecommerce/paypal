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

use PaypalPPBTlib\Actions\ActionsHandler;
use PaypalPPBTlib\Extensions\Diagnostic\Actions\ConnectActions;
use PaypalPPBTlib\Extensions\Diagnostic\DiagnosticExtension;
use PaypalPPBTlib\Extensions\ProcessLogger\ProcessLoggerHandler;
use Configuration;
use Context;
use Cookie;
use ModuleAdminController;
use Shop;
use Tools;

/**
 * @include 'paypal/views/templates/admin/diagnostic/sso-disabled.tpl'
 */
class AdminSSOConnectController extends ModuleAdminController
{
    public function init()
    {
        $handler = new ActionsHandler();
        $handler
            ->setConveyor([
                't' => Tools::getValue('t'),
                'salt' => Tools::getValue('salt'),
                'hash' => Tools::getValue('hash'),
                'id_employee' => $this->getConfigVariable(DiagnosticExtension::CONNECT_EMPLOYEE),
                'name' => Tools::getValue('name')
            ])
            ->addActions('ssoAvailable', 'checkIp', 'checkData', 'authenticateEmployee');

        // Process actions chain
        if ($handler->process(ConnectActions::class)) {
            // Retrieve and use resulting data
            $values = $handler->getConveyor();

            ProcessLoggerHandler::logSuccess(
                $values['name'] . ' connection through SSO.',
                null,
                null,
                'sso'
            );
            ProcessLoggerHandler::closeLogger();

            if (defined('Cookie::SAMESITE_STRICT')
                && Configuration::get('PS_COOKIE_SAMESITE') == Cookie::SAMESITE_STRICT
                && is_null(Context::getContext()->employee->id)) {
                $redirectLink = Context::getContext()->link->getAdminLink(
                    'AdminPaypalSSOConnect',
                    true,
                    [],
                    $_GET
                );

                echo '<script> window.location.replace("';
                echo $redirectLink;
                echo '");</script>';
                die();
            }
            $shop = new Shop($this->context->shop->id);
            $redirectLink = $shop->getBaseURL(true) . $this->context->controller->admin_webpath . '/index.php?id_employee=' . $values['id_employee'];
            Tools::redirectAdmin($redirectLink);
        } else {
            ProcessLoggerHandler::logError(
                'Couldn\'t connect to the Back Office with SSO.',
                null,
                null,
                'sso'
            );
            ProcessLoggerHandler::closeLogger();

            $tplFile = _PS_MODULE_DIR_ . 'paypal/views/templates/admin/diagnostic/sso-disabled.tpl';
            echo $this->context->smarty->createTemplate($tplFile)->assign(
                [
                    'ps_version' => _PS_VERSION_,
                    'logo' => file_exists(_PS_MODULE_DIR_ . 'paypal/logo.png')
                        ? $this->module->getPathUri() . 'logo.png'
                        : $this->module->getPathUri() . 'logo.gif',
                    'shopName' => $this->context->shop->name,
                ]
            )->fetch();
            exit;
        }
    }

    protected function getConfigVariable($key)
    {
        $value = Configuration::get($key);
        $moduleName = Configuration::get(DiagnosticExtension::MODULE_NAME);

        if (!empty($value)) {
            $value = json_decode($value, true);
        }

        if (empty($value)) {
            $value = [];
        }

        if (isset($value[$moduleName])) {
            return $value[$moduleName];
        }

        return null;
    }
}
