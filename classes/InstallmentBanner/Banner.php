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

namespace PaypalAddons\classes\InstallmentBanner;

use PaypalAddons\classes\AbstractMethodPaypal;

if (!defined('_PS_VERSION_')) {
    exit;
}

class Banner
{
    /** @var \PayPal */
    protected $module;

    /** @var string */
    protected $placement = '';

    /** @var string */
    protected $layout = '';

    /** @var float */
    protected $amount = 0;

    /** @var string */
    protected $template = '';

    /** @var array */
    protected $jsVars = [];

    /** @var array */
    protected $tplVars = [];

    /** @var AbstractMethodPaypal */
    protected $method;

    /** @var string */
    protected $pageTypeAttribute;

    public function __construct()
    {
        /* @phpstan-ignore-next-line */
        $this->module = \Module::getInstanceByName('paypal');
        $this->setTemplate('module:paypal/views/templates/installmentBanner/banner.tpl');
        $this->method = AbstractMethodPaypal::load();
    }

    public function render()
    {
        if (!$this->method->isConfigured()) {
            return '';
        }

        $tpl = \Context::getContext()->smarty->createTemplate($this->getTemplate());
        $tpl->assign('paypalmessenging', $this->getConfig())
            ->assign($this->getTplVars())
            ->assign('JSscripts', $this->getJS());

        return $tpl->fetch();
    }

    public function getConfig()
    {
        $config = json_decode(str_replace('-', '_', \Configuration::get(ConfigurationMap::MESSENGING_CONFIG)), true);
        $placement = $this->getPlacement();
        if (isset($config['homepage'])) {
            $config['home'] = $config['homepage'];
            $config['home']['placement'] = 'home';
        }
        if (isset($config[$placement]) === false) {
            switch ($placement) {
                case 'cart':
                case 'product':
                case 'checkout':
                    $config[$placement] = [
                        'layout' => 'text',
                        'logo_type' => 'primary',
                        'logo_position' => 'left',
                        'placement' => $placement,
                        'text_color' => 'black',
                        'text_size' => '12',
                    ];
                    break;
                default:
                    $config[$placement] = [
                        'layout' => 'flex',
                        'color' => 'blue',
                        'placement' => $placement,
                        'ratio' => '20x1',
                    ];
            }
        }

        $configReturn = $config[$placement];
        $configReturn['amount'] = $this->amount;
        $configReturn['locale'] = str_replace('-', '_', \Context::getContext()->language->locale);
        $configReturn['buyercountry'] = $this->getBuyerCountry();

        return $configReturn;
    }

    protected function getJS()
    {
        $js = [
            'tot-paypal-sdk-messages' => [
                'src' => $this->getPaypalSdkLib(),
                'data-namespace' => 'totPaypalSdk',
                'data-page-type' => $this->getPageTypeAttribute(),
                'enable-funding' => 'paylater',
            ],
        ];

        if (false === defined('_PS_ADMIN_DIR_')) {
            $js['tot-paypal-sdk-messages']['data-partner-attribution-id'] = $this->getPartnerId();
        }

        return $js;
    }

    /**
     * @return string
     */
    public function getPlacement()
    {
        return $this->placement ? $this->placement : 'home';
    }

    /**
     * @return self
     */
    public function setPlacement($placement)
    {
        $this->placement = (string) $placement;

        return $this;
    }

    /**
     * @return float
     */
    public function getAmount()
    {
        return (float) $this->amount;
    }

    /**
     * @param float $amount
     *
     * @return Banner
     */
    public function setAmount($amount)
    {
        $this->amount = (float) $amount;

        return $this;
    }

    /**
     * @return string
     */
    public function getLayout()
    {
        return $this->layout ? $this->layout : 'flex';
    }

    /**
     * @param string $layout
     *
     * @return Banner
     */
    public function setLayout($layout)
    {
        $this->layout = (string) $layout;

        return $this;
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @param string $template
     *
     * @return Banner
     */
    public function setTemplate($template)
    {
        $this->template = $template;

        return $this;
    }

    /**
     * @param string $name
     * @param mixed $value
     *
     * @return Banner
     */
    public function addJsVar($name, $value)
    {
        $this->jsVars[$name] = $value;

        return $this;
    }

    /**
     * @return array
     */
    protected function getTplVars()
    {
        return $this->tplVars;
    }

    /**
     * @param string $name
     * @param mixed $value
     *
     * @return Banner
     */
    public function addTplVar($name, $value)
    {
        $this->tplVars[$name] = $value;

        return $this;
    }

    /**
     * @return string
     */
    protected function getPaypalSdkLib()
    {
        $params = [
            'client-id' => $this->method->getClientId(),
            'components' => 'messages',
        ];

        return 'https://www.paypal.com/sdk/js?' . http_build_query($params);
    }

    /**
     * @return string
     */
    public function getPageTypeAttribute()
    {
        return (string) $this->pageTypeAttribute;
    }

    /**
     * @return Banner
     */
    public function setPageTypeAttribute($pageTypeAttribute)
    {
        if (is_string($pageTypeAttribute)) {
            $this->pageTypeAttribute = $pageTypeAttribute;
        }

        return $this;
    }

    public function getPartnerId()
    {
        return 'PRESTASHOP_Cart_SPB';
    }

    protected function getBuyerCountry()
    {
        $isoLang = \Tools::strtoupper(\Context::getContext()->language->iso_code);
        $isoCurrency = \Tools::strtoupper(\Context::getContext()->currency->iso_code);

        if ($isoLang === 'FR') {
            return 'FR';
        }
        if ($isoLang === 'IT') {
            return 'IT';
        }
        if ($isoLang === 'ES') {
            return 'ES';
        }
        if ($isoLang === 'DE') {
            return 'DE';
        }
        if ($isoCurrency === 'AUD') {
            return 'AU';
        }
        if ($isoCurrency === 'GBP') {
            return 'GB';
        }
        if ($isoCurrency === 'USD') {
            return 'US';
        }

        return '';
    }
}
