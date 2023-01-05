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

namespace PaypalPPBTlib\Extensions\Diagnostic\Actions;

use PaypalPPBTlib\Actions\DefaultActions;
use PaypalPPBTlib\Extensions\Diagnostic\DiagnosticExtension;
use PaypalPPBTlib\Extensions\ProcessLogger\ProcessLoggerHandler;
use Configuration;
use Context;
use Employee;
use Tools;

class ConnectActions extends DefaultActions
{
    public function ssoAvailable()
    {
        if ($this->getConfigVariable(DiagnosticExtension::CONNECT_SSO_AVAILABLE) != 1) {
            return false;
        }

        return true;
    }

    public function checkIp()
    {
        $restrictedIps = $this->getConfigVariable(DiagnosticExtension::CONNECT_RESTRICTED_IPS);
        if (empty($restrictedIps)) {
            return true;
        }

        $ip = Tools::getRemoteAddr();
        if (!empty($restrictedIps) && !in_array($ip, explode(',', $restrictedIps))) {
            ProcessLoggerHandler::logError(
                "Wrong IP : " . $ip . " tried to connect to your Back Office",
                null,
                null,
                'sso'
            );
            ProcessLoggerHandler::closeLogger();
            return false;
        }

        return true;
    }

    public function checkData()
    {
        $t = $this->conveyor['t'];
        $salt = $this->conveyor['salt'];
        $hash = $this->conveyor['hash'];
        $name = $this->conveyor['name'];

        if (!isset($salt) || !isset($hash)) {
            ProcessLoggerHandler::logInfo(
                'BAD DATA: Token doesn\'t have information to identify a user.',
                null,
                null,
                'sso'
            );
            ProcessLoggerHandler::closeLogger();
            return false;
        }

        $time = time() + 3 * 3600;
        if ($time < $t) {
            ProcessLoggerHandler::logInfo(
                "EXPIRED: The timestamp $t is expired (3h). Please verify also the date of the server.",
                null,
                null,
                'sso'
            );
            ProcessLoggerHandler::closeLogger();
            return false;
        }

        $expectedSignedData = http_build_query([
            'name' => $name,
            't' => $t,
            'salt' => $salt,
            'secret_key' => $this->getConfigVariable(DiagnosticExtension::CONNECT_SECURE_KEY),
        ]);
        $expectedHash = hash('sha256', $expectedSignedData);

        if ($expectedHash != $hash) {
            ProcessLoggerHandler::logError(
                'REJECTED: the hash computed is not equal to received.',
                null,
                null,
                'sso'
            );
            ProcessLoggerHandler::closeLogger();
            return false;
        }

        return true;
    }

    public function authenticateEmployee()
    {
        $this->context = new Context();
        $this->context->employee = new Employee($this->conveyor['id_employee']);

        $this->context->employee->remote_addr = (int) ip2long(Tools::getRemoteAddr());

        $cookie = Context::getContext()->cookie;
        $cookie->id_employee = $this->context->employee->id;
        $cookie->email = $this->context->employee->email;
        $cookie->profile = $this->context->employee->id_profile;
        $cookie->passwd = $this->context->employee->passwd;
        $cookie->remote_addr = $this->context->employee->remote_addr;
        if (version_compare(_PS_VERSION_, '1.7.6.5', '>')) {
            $cookie->registerSession(new \EmployeeSession());
        }

        $cookie->write();

        return true;
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
