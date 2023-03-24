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

namespace PaypalAddons\classes\SEPA;

use Configuration;
use Context;
use Module;
use PaypalAddons\classes\AbstractMethodPaypal;
use PaypalAddons\classes\Constants\PaypalConfigurations;

class SepaButton
{
    /** @var Context */
    protected $context;

    /** @var Module */
    protected $module;

    /** @var AbstractMethodPaypal */
    protected $method;

    public function __construct()
    {
        $this->context = Context::getContext();
        $this->module = Module::getInstanceByName('paypal');
        $this->method = AbstractMethodPaypal::load($this->getMethodType());
    }

    /**
     * @return string html
     */
    public function render()
    {
        $this->context->smarty->assign($this->getTplVars());
        $this->context->smarty->assign('JSvars', $this->getJSvars());
        $this->context->smarty->assign('JSscripts', $this->getJS());

        return $this->context->smarty->fetch($this->getTemplatePath());
    }

    /**
     * @return []
     */
    protected function getJSvars()
    {
        return [
            $vars[PaypalConfigurations::MOVE_BUTTON_AT_END] = (int) Configuration::get(PaypalConfigurations::MOVE_BUTTON_AT_END),
        ];
    }

    /**
     * @return []
     */
    protected function getJS()
    {
        $JSscripts = [];
        $srcLib = $this->method->getUrlJsSdkLib([
            'enable-funding' => 'sepa',
            'components' => 'buttons,marks',
        ]);

        if ($this->method->isSandbox()) {
            $srcLib .= '&buyer-country=DE';
        }

        $JSscripts[$this->getSdkNameSpace()] = [
            'src' => $srcLib,
            'data-namespace' => $this->getSdkNameSpace(),
            'data-partner-attribution-id' => $this->getPartnerId(),
        ];
        $JSscripts['sepaButton'] = [
            'src' => __PS_BASE_URI__ . 'modules/' . $this->module->name . '/views/js/sepaButton.js?v=' . $this->module->version,
        ];

        return $JSscripts;
    }

    protected function getSdkNameSpace()
    {
        return 'totPaypalSepaSdkButtons';
    }

    protected function getPartnerId()
    {
        return $this->method->getPaypalPartnerId();
    }

    /**
     * @return string|null
     */
    protected function getMethodType()
    {
        return null;
    }

    /**
     * @return string
     */
    protected function getTemplatePath()
    {
        return 'module:paypal/views/templates/sepa/button.tpl';
    }

    /**
     * @return []
     */
    protected function getTplVars()
    {
        return [
            'psPaypalDir' => _PS_MODULE_DIR_ . 'paypal',
            'sdkNameSpace' => $this->getSdkNameSpace(),
        ];
    }
}
