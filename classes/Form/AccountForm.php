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

namespace PaypalAddons\classes\Form;

use PaypalAddons\classes\AbstractMethodPaypal;
use PaypalAddons\classes\PUI\PuiFunctionality;
use PaypalAddons\classes\PUI\SignUpLinkButton;
use PaypalAddons\classes\PuiMethodInterface;

if (!defined('_PS_VERSION_')) {
    exit;
}

class AccountForm implements FormInterface
{
    /** @var \PayPal */
    protected $module;

    protected $puiFunctionality;

    protected $method;

    public function __construct()
    {
        /* @phpstan-ignore-next-line */
        $this->module = \Module::getInstanceByName('paypal');
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
            $data = \Tools::getAllValues();
        }

        if (empty($data['accountForm'])) {
            return true;
        }

        $this->method->saveAccountForm($data);

        return true;
    }

    protected function getHelpInfo()
    {
        return \Context::getContext()->smarty->fetch(_PS_MODULE_DIR_ . $this->module->name . '/views/templates/admin/_partials/messages/form-help-info/account.tpl');
    }
}
