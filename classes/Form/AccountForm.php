<?php

namespace PaypalAddons\classes\Form;

use Context;
use Module;
use PaypalAddons\classes\AbstractMethodPaypal;
use PaypalAddons\classes\PUI\PuiFunctionality;
use PaypalAddons\classes\PUI\SignUpLinkButton;
use PaypalAddons\classes\PuiMethodInterface;
use Tools;

if (!defined('_PS_VERSION_')) {
    exit;
}

class AccountForm implements FormInterface
{
    /** @var \Paypal */
    protected $module;

    protected $puiFunctionality;

    protected $method;

    public function __construct()
    {
        $this->module = Module::getInstanceByName('paypal');
        $this->puiFunctionality = new PuiFunctionality();
        $this->method = AbstractMethodPaypal::load();
    }

    public function getDescription()
    {
        $fields = [];

        $fields['account_form'] = [
            'type' => 'variable-set',
            'set' => $this->method->getVarsForAccountForm(),
        ];

        return [
            'legend' => [
                'title' => $this->module->l('PayPal account configuration', 'AccountForm'),
            ],
            'fields' => $fields,
            'submit' => [
                'title' => $this->module->l('Save', 'AccountForm'),
                'name' => 'accountForm',
            ],
            'id_form' => 'pp_account_form',
            'help' => $this->getHelpInfo(),
        ];
    }

    protected function initSignUpLinkButton(PuiMethodInterface $method)
    {
        return new SignUpLinkButton($method);
    }

    public function save($data = null)
    {
        if (is_null($data)) {
            $data = Tools::getAllValues();
        }

        if (empty($data['accountForm'])) {
            return;
        }

        return $this->method->saveAccountForm($data);
    }

    protected function getHelpInfo()
    {
        return Context::getContext()->smarty->fetch(_PS_MODULE_DIR_ . $this->module->name . '/views/templates/admin/_partials/messages/form-help-info/account.tpl');
    }
}
