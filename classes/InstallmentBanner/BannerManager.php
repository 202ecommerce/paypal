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

use PaypalAddons\services\CurrencyConverter;

if (!defined('_PS_VERSION_')) {
    exit;
}

class BannerManager
{
    /** @var Banner */
    protected $banner;

    /** @var \Context */
    protected $context;

    public function __construct()
    {
        $this->banner = new Banner();
        $this->context = \Context::getContext();
    }

    /**
     * @return bool
     */
    public function isEligibleContext()
    {
        $isoLang = substr(\Tools::strtolower($this->context->language->language_code), -2, 2);
        $isoCurrency = \Tools::strtolower($this->context->currency->iso_code);

        foreach (ConfigurationMap::getBnplLanguageCurrencyMap() as $langCurrency) {
            if (isset($langCurrency[$isoLang]) && $langCurrency[$isoLang] == $isoCurrency) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isEligiblePage()
    {
        foreach (ConfigurationMap::getPageConfMap() as $page => $conf) {
            if (is_a($this->context->controller, $page) && (bool) \Configuration::get($conf)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isEligibleConf()
    {
        if (false === $this->isEligibleCountry()) {
            return false;
        }

        return true;
    }

    public function isEligibleCountry()
    {
        $isoCountryDefault = \Country::getIsoById(
            (int) \Configuration::get(
                'PS_COUNTRY_DEFAULT',
                null,
                null,
                $this->context->shop->id
            )
        );

        if (false === in_array(\Tools::strtolower($isoCountryDefault), ConfigurationMap::getAllowedCountries())) {
            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    public function isBannerAvailable()
    {
        if ($this->isEligibleConf() === false) {
            return false;
        }

        if ($this->isEligibleContext() === false) {
            return false;
        }

        if ($this->isEligiblePage() === false) {
            return false;
        }

        return true;
    }

    /**
     * @param string $placement Placement expected for the message
     *
     * @return string
     */
    public function renderBanner($placement = 'home')
    {
        $banner = $this->banner
            ->setPlacement($placement)
            ->setTemplate('module:paypal/views/templates/installmentBanner/banner.tpl');

        if ($this->context->controller instanceof \ProductController && $placement == 'product') {
            if (\Validate::isLoadedObject($this->context->controller->getProduct())) {
                /** @var \Product $product */
                $product = $this->context->controller->getProduct();
                $banner->setAmount($product->getPrice());
                $banner->setTemplate('module:paypal/views/templates/installmentBanner/product-banner.tpl');
            }
        }

        if (in_array($placement, ['cart', 'checkout'])) {
            $banner->setAmount($this->getCurrencyConverter()->convert($this->context->cart->getOrderTotal(true)));
        }

        return $banner->render();
    }

    /**
     * @return string
     */
    public function renderForHomePage()
    {
        return $this->renderBanner('home');
    }

    /**
     * @return string
     */
    public function renderForCartPage()
    {
        return $this->renderBanner('cart');
    }

    /** @return CurrencyConverter*/
    public function getCurrencyConverter()
    {
        return new CurrencyConverter();
    }

    /**
     * @return string
     */
    public function renderForCheckoutPage()
    {
        return $this->renderBanner('checkout');
    }

    /**
     * @return string
     */
    public function renderForProductPage()
    {
        return $this->renderBanner('product');
    }
}
