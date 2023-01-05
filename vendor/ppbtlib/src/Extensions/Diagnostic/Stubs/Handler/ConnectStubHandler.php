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

namespace PaypalPPBTlib\Extensions\Diagnostic\Stubs\Handler;

use PaypalPPBTlib\Extensions\Diagnostic\DiagnosticExtension;
use PaypalPPBTlib\Extensions\Diagnostic\Stubs\Concrete\AbstractStub;
use PaypalPPBTlib\Extensions\Diagnostic\Stubs\Concrete\ConnectStub;
use PaypalPPBTlib\Extensions\ProcessLogger\ProcessLoggerHandler;
use Configuration;
use Context;
use Employee;
use Tools;

class ConnectStubHandler extends AbstractStubHandler
{
    public function handle()
    {
        return [
            'connection' => $this->getConnectVariables(),
        ];
    }

    public function savePreferences($data)
    {
        $this->saveConfigValue(DiagnosticExtension::CONNECT_SSO_AVAILABLE, (int) $data['sso']);
        $this->saveConfigValue(DiagnosticExtension::CONNECT_EMPLOYEE, (int) $data['employee']);
        $this->saveConfigValue(DiagnosticExtension::CONNECT_RESTRICTED_IPS, $data['ips']);

        $slug = $this->getConfigVariable(DiagnosticExtension::CONNECT_SLUG);

        if (!empty($slug)) {
            return true;
        }

        return $this->makeShareRequest();
    }

    protected function makeShareRequest()
    {
        $params = $this->getStub()->getParameters();

        if (empty($params->getFolder()) || empty($params->getSyslay()) || empty($params->getHolder())) {
            return false;
        }

        $url = '';
        if (version_compare(_PS_VERSION_, '1.7', '<')) {
            $url = _PS_BASE_URL_ . '/' . basename(_PS_ADMIN_DIR_) . '/';
        }

        $url .= Context::getContext()->link->getAdminLink('AdminPaypalSSOConnect', false) . '&t=:timestamp&salt=:salt&hash=:hash&name=:displayname';
        $syslayUrl = $params->getSyslay() . '/p/' . $params->getHolder() . '/ssoclient/' . $params->getFolder() . '.json';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $syslayUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $datas['public_sso_client']['title'] = $_SERVER['HTTP_HOST'];
        $datas['public_sso_client']['url'] = $url;
        $datas['public_sso_client']['passwordClearString'] = $this->getConfigVariable(DiagnosticExtension::CONNECT_SECURE_KEY);

        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($datas));
        curl_setopt($ch, CURLOPT_POST, 1);

        $headers = [];
        $headers[] = 'Content-Type: application/x-www-form-urlencoded';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        $resultDatas = json_decode($result);

        if (isset($resultDatas->errors)) {
            ProcessLoggerHandler::logError(sprintf('Send configuration to Syslay failed. %s', $result), null, null, 'sso');
            ProcessLoggerHandler::closeLogger();
            curl_close($ch);
            return false;
        }

        if (!empty($resultDatas->data) && !empty($resultDatas->data->slug)) {
            $this->saveConfigValue(DiagnosticExtension::CONNECT_SLUG, $resultDatas->data->slug);
            ProcessLoggerHandler::logInfo(sprintf("Send configuration to Syslay success. Document created ID: %s", $resultDatas->data->slug), null, null, 'sso');
            ProcessLoggerHandler::closeLogger();
        }

        curl_close($ch);

        return true;
    }

    protected function getConnectVariables()
    {
        $ssoAvailable = $this->getConfigVariable(DiagnosticExtension::CONNECT_SSO_AVAILABLE);
        $employees = array_map(function ($employee) {
            return [
                'id' => $employee['id_employee'],
                'name' => $employee['firstname'] . ' ' . $employee['lastname'],
                'selected' => $employee['id_employee'] == $this->getConfigVariable(DiagnosticExtension::CONNECT_EMPLOYEE),
            ];
        }, (array) Employee::getEmployees());
        $secureKey = $this->getConfigVariable(DiagnosticExtension::CONNECT_SECURE_KEY);
        if (empty($secureKey)) {
            $this->saveConfigValue(DiagnosticExtension::CONNECT_SECURE_KEY, Tools::passwdGen(40));
        }
        $ips = (string) $this->getConfigVariable(DiagnosticExtension::CONNECT_RESTRICTED_IPS);

        return [
            'sso' => $ssoAvailable,
            'employees' => $employees,
            'ips' => $ips,
        ];
    }

    protected function getConfigVariable($key)
    {
        $value = Configuration::get($key);

        if (!empty($value)) {
            $value = json_decode($value, true);
        }

        if (empty($value)) {
            $value = [];
        }

        if (isset($value[$this->getStub()->getModule()->name])) {
            return $value[$this->getStub()->getModule()->name];
        }

        return null;
    }

    protected function saveConfigValue($key, $saveValue)
    {
        $value = Configuration::get($key);

        if (!empty($value)) {
            $value = json_decode($value, true);
        }

        if (empty($value)) {
            $value = [];
        }

        $value[$this->getStub()->getModule()->name] = $saveValue;

        Configuration::updateGlobalValue($key, json_encode($value));
    }

    public function export($download = true)
    {
        return null;
    }

    /**
     * @return ConnectStub|AbstractStub
     */
    public function getStub()
    {
        return $this->stub;
    }
}
