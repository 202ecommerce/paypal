<?php

namespace PaypalAddons\classes\Form;

use Module;
use PaypalAddons\classes\AbstractMethodPaypal;
use PaypalAddons\classes\PUI\PuiFunctionality;
use PaypalAddons\classes\PUI\SignUpLinkButton;
use PaypalAddons\classes\PuiMethodInterface;
use Tools;

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
            'set' => $this->method->getVarsForAccountForm()
        ];

        if ($this->method instanceof PuiMethodInterface) {
            if ($this->method->isConfigured()) {
                $fields['account_form']['set']['isPuiAvailable'] = $this->puiFunctionality->isAvailable();
            } else {
                $fields['account_form']['set']['SignUpLinkButton'] = $this->initSignUpLinkButton($this->method);
            }
        }

        return [
            'legend' => [
                'title' => $this->module->l('Account', 'AccountForm'),
            ],
            'fields' => $fields,
            'submit' => [
                'title' => $this->module->l('Save', 'AccountForm'),
                'name' => 'accountForm',
            ],
            'id_form' => 'pp_account_form',
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

        $this->method->saveAccountForm($data);
    }
}
