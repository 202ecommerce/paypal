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

namespace PaypalAddons\classes\APM;

use Configuration;
use Context;
use Country;
use Module;
use PaypalAddons\classes\AbstractMethodPaypal;
use PaypalAddons\classes\Constants\APM;
use Tools;

class ApmCollection
{
    /** @var Context */
    protected $context;

    /** @var Module */
    protected $module;

    /** @var AbstractMethodPaypal */
    protected $method;

    /** @var string */
    protected $id;

    /** @var string */
    protected $methodCollection;

    public function __construct($methodCollection = null)
    {
        $this->context = Context::getContext();
        $this->module = Module::getInstanceByName('paypal');
        $this->method = AbstractMethodPaypal::load($this->getMethodType());

        if (is_null($methodCollection)) {
            $this->methodCollection = $this->initDefaultCollection();
        } else {
            $this->methodCollection = $methodCollection;
        }

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

        return $this->context->smarty->fetch($this->getTemplatePath());
    }

    /**
     * @return []
     */
    protected function getJSvars()
    {
        return [];
    }

    /**
     * @return []
     */
    protected function getJS()
    {
        $JSscripts = [];
        $srcLib = $this->method->getUrlJsSdkLib([
            'enable-funding' => implode(',', $this->methodCollection),
            'components' => 'buttons,marks',
        ]);

        if ($this->method->isSandbox()) {
            $buyerCountry = $this->getBuyerCountry();

            if (false == empty($buyerCountry)) {
                $srcLib .= '&buyer-country=' . $buyerCountry;
            }
        }

        $JSscripts[$this->getSdkNameSpace()] = [
            'src' => $srcLib,
            'data-namespace' => $this->getSdkNameSpace(),
            'data-partner-attribution-id' => $this->getPartnerId(),
        ];
        $JSscripts['apmButton'] = [
            'src' => __PS_BASE_URI__ . 'modules/' . $this->module->name . '/views/js/apmButton.js?v=' . $this->module->version,
        ];

        return $JSscripts;
    }

    protected function getSdkNameSpace()
    {
        return 'totPaypalApmSdkButtons_' . md5(implode('', $this->methodCollection));
    }

    protected function getPartnerId()
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
        return 'module:paypal/views/templates/apm/method-collection.tpl';
    }

    /**
     * @return []
     */
    protected function getTplVars()
    {
        return [
            'psPaypalDir' => _PS_MODULE_DIR_ . 'paypal',
            'methodCollection' => $this->methodCollection,
            'sdkNameSpace' => $this->getSdkNameSpace(),
        ];
    }

    /**
     * @return int
     */
    protected function getId()
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

    protected function getBuyerCountry()
    {
        $buyerCountry = Tools::strtoupper(Country::getIsoById(Configuration::get('PS_COUNTRY_DEFAULT')));
        // https://developer.paypal.com/docs/regional/th/checkout/reference/customize-sdk/
        // According a documentation the available countries are following 'US', 'CA', 'GB', 'DE', 'FR'
        // But an error was occurring using 'US', 'CA', 'GB' during the test
        if (in_array($buyerCountry, ['DE', 'FR'])) {
            return $buyerCountry;
        }

        return '';
    }

    protected function getNameSpace()
    {
    }

    protected function initDefaultCollection()
    {
        return [
            APM::GIROPAY,
            APM::SOFORT,
        ];
    }
}
