<?php
/**
 * 2007-2023 PayPal
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
 *  @author 2007-2023 PayPal
 *  @author 202 ecommerce <tech@202-ecommerce.com>
 *  @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 *  @copyright PayPal
 */

namespace PaypalAddons\classes\Form;

use Context;
use Module;
use PaypalAddons\classes\Constants\WhiteList;
use PaypalAddons\classes\WhiteList\WhiteListService;
use Symfony\Component\HttpFoundation\Request;
use Tools;

class WhiteListForm implements FormInterface
{
    protected $module;

    protected $className;

    protected $context;

    public function __construct()
    {
        $this->module = Module::getInstanceByName('paypal');
        $this->className = 'WhiteListForm';
        $this->context = Context::getContext();
    }

    /**
     * @return array
     */
    public function getFields()
    {
        $input = [
            [
                'type' => 'switch',
                'label' => $this->module->l('Enable restriction by IP', $this->className),
                'name' => WhiteList::ENABLED,
                'is_bool' => true,
                'values' => [
                    [
                        'id' => WhiteList::ENABLED . '_on',
                        'value' => 1,
                        'label' => $this->module->l('Enabled', $this->className),
                    ],
                    [
                        'id' => WhiteList::ENABLED . '_off',
                        'value' => 0,
                        'label' => $this->module->l('Disabled', $this->className),
                    ],
                ],
            ],
            [
                'type' => 'html',
                'html_content' => $this->getListHTML(),
                'name' => '',
                'label' => $this->module->l('List of IPs', $this->className),
            ],
        ];

        $fields = [
            'legend' => [
                'title' => $this->module->l('White list', $this->className),
                'icon' => 'icon-cogs',
            ],
            'input' => $input,
            'submit' => [
                'title' => $this->module->l('Save', $this->className),
                'class' => 'btn btn-default pull-right button',
                'name' => 'whiteListForm',
            ],
            'id_form' => 'pp_white_list',
        ];

        return $fields;
    }

    /**
     * @return array
     */
    public function getValues()
    {
        $values = [
            WhiteList::ENABLED => $this->initWhiteListService()->isEnabled(),
        ];

        return $values;
    }

    /**
     * @return bool
     */
    public function save()
    {
        if (Tools::isSubmit('whiteListForm') == false) {
            return false;
        }

        $service = $this->initWhiteListService();
        $service->setEnabled((int) Tools::getValue(WhiteList::ENABLED));
        $service->setListIP(
            explode(';', Tools::getValue(WhiteList::LIST_IP, ''))
        );

        return true;
    }

    protected function initWhiteListService()
    {
        return new WhiteListService();
    }

    protected function getListHTML()
    {
        $request = Request::createFromGlobals();
        Context::getContext()->smarty->assign([
                WhiteList::LIST_IP => implode(';', $this->initWhiteListService()->getListIP()),
                'paypal_current_ip' => $request->getClientIp(),
        ]);

        return Context::getContext()
            ->smarty
            ->fetch(_PS_MODULE_DIR_ . $this->module->name . '/views/templates/admin/_partials/white-list.tpl');
    }
}
