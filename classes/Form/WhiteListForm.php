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

use PaypalAddons\classes\Constants\WhiteList;
use PaypalAddons\classes\WhiteList\WhiteListService;
use Symfony\Component\HttpFoundation\Request;

if (!defined('_PS_VERSION_')) {
    exit;
}

class WhiteListForm implements FormInterface
{
    protected $module;

    protected $className;

    public function __construct()
    {
        $this->module = \Module::getInstanceByName('paypal');
        $this->className = 'WhiteListForm';
    }

    /**
     * @return array
     */
    public function getDescription()
    {
        $request = Request::createFromGlobals();

        return [
            'legend' => [
                'title' => $this->module->l('Restriction mode', $this->className),
            ],
            'fields' => [
                WhiteList::LIST_IP => [
                    'type' => 'text',
                    'label' => $this->module->l('List of IPs', $this->className),
                    'name' => WhiteList::LIST_IP,
                    'value' => implode(';', $this->initWhiteListService()->getListIP()),
                    'hint' => $request->getClientIp(),
                    'variant' => 'primary',
                    'placeholder' => \Tools::getRemoteAddr(),
                ],
            ],
            'submit' => [
                'title' => $this->module->l('Save', $this->className),
                'name' => 'whiteListForm',
            ],
            'id_form' => 'pp_white_list_form',
            'help' => $this->getHelpInfo(),
        ];
    }

    /**
     * @return bool
     */
    public function save($data = null)
    {
        if (is_null($data)) {
            $data = \Tools::getAllValues();
        }

        $service = $this->initWhiteListService();
        $service->setEnabled(
            isset($data[WhiteList::ENABLED]) ? (int) $data[WhiteList::ENABLED] : 0
        );
        $service->setListIP(
            explode(
                ';',
                isset($data[WhiteList::LIST_IP]) ? $data[WhiteList::LIST_IP] : ''
            )
        );

        return true;
    }

    protected function initWhiteListService()
    {
        return new WhiteListService();
    }

    protected function getHelpInfo()
    {
        return \Context::getContext()->smarty->fetch(_PS_MODULE_DIR_ . $this->module->name . '/views/templates/admin/_partials/messages/form-help-info/white-list-ip.tpl');
    }
}
