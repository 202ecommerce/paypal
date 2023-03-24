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

namespace PaypalAddons\classes\InstallmentBanner\BNPL;

use Configuration;
use Context;
use Country;
use Module;
use PaypalAddons\classes\AbstractMethodPaypal;
use PaypalAddons\classes\InstallmentBanner\ConfigurationMap;
use Tools;

abstract class BNPLAbstract
{
    /** @var Context */
    protected $context;

    /** @var Module */
    protected $module;

    /** @var AbstractMethodPaypal */
    protected $method;

    /** @var string */
    protected $id;

    public function __construct()
    {
        $this->context = Context::getContext();
        $this->module = Module::getInstanceByName('paypal');
        $this->method = AbstractMethodPaypal::load($this->getMethodType());
        $this->setId(uniqid());
    }

    /**
     * @return string html
     */
    public function render()
    {
        $this->context->smarty->assign($this->getTplVars());
        $this->context->smarty->assign('JSvars', $this->getJSvars());
        $this->context->smarty->assign('JSscripts', $this->getJS());
        $this->context->smarty->assign('psPaypalDir', _PS_MODULE_DIR_ . 'paypal');

        return $this->context->smarty->fetch($this->getTemplatePath());
    }

    /**
     * @return []
     */
    protected function getJSvars()
    {
        $JSvars = [];
        $JSvars['sc_init_url'] = $this->context->link->getModuleLink($this->module->name, 'ScInit', [], true);
        $JSvars['scOrderUrl'] = $this->context->link->getModuleLink($this->module->name, 'scOrder', [], true);
        $JSvars['bnplColor'] = $this->getColor();

        return $JSvars;
    }

    /**
     * @return []
     */
    protected function getJS()
    {
        $JSscripts = [];
        $srcLib = $this->method->getUrlJsSdkLib(['components' => 'buttons,marks']) . '&enable-funding=paylater';

        if ($this->method->isSandbox()) {
            $buyerCountry = $this->getBuyerCountry();

            if (false == empty($buyerCountry)) {
                $srcLib .= '&buyer-country=' . $buyerCountry;
            }
        }

        $JSscripts['tot-paypal-bnpl-sdk'] = [
            'src' => $srcLib,
            'data-namespace' => 'totPaypalBnplSdkButtons',
            'data-partner-attribution-id' => $this->getPartnerId(),
        ];
        $JSscripts['bnpl'] = [
            'src' => __PS_BASE_URI__ . 'modules/' . $this->module->name . '/views/js/bnpl.js?v=' . $this->module->version,
        ];

        return $JSscripts;
    }

    public function getPartnerId()
    {
        return 'PRESTASHOP_Cart_SPB';
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
        return 'module:paypal/views/templates/bnpl/bnpl-layout.tpl';
    }

    /**
     * @return []
     */
    abstract protected function getTplVars();

    protected function getStyleSetting()
    {
        $styleSetting = [];
        $styleSetting['label'] = 'pay';
        $styleSetting['height'] = 35;

        return $styleSetting;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return (string) $this->id;
    }

    /**
     * @param string $id
     *
     * @return self
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /** @return string*/
    public function getColor()
    {
        if ((int) Configuration::get(ConfigurationMap::ADVANCED_OPTIONS_INSTALLMENT)) {
            $bannerColor = Configuration::get(ConfigurationMap::COLOR);
        } else {
            $bannerColor = ConfigurationMap::COLOR_GRAY;
        }

        return isset(ConfigurationMap::getBnplColorMapping()[$bannerColor]) ? ConfigurationMap::getBnplColorMapping()[$bannerColor] : 'white';
    }

    public function getBuyerCountry()
    {
        $buyerCountry = Tools::strtoupper(Country::getIsoById(Configuration::get('PS_COUNTRY_DEFAULT')));
        // https://developer.paypal.com/docs/regional/th/checkout/reference/customize-sdk/
        // According a documentation the available countries are following 'US', 'CA', 'GB', 'DE', 'FR', 'IT', 'ES'
        // But an error was occurring using 'US', 'CA', 'GB' during the test
        if (in_array($buyerCountry, ['DE', 'FR', 'IT', 'ES'])) {
            return $buyerCountry;
        }

        return '';
    }
}
