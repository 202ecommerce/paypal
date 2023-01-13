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
require_once _PS_MODULE_DIR_ . 'paypal/vendor/autoload.php';

use PaypalAddons\classes\AbstractMethodPaypal;
use PaypalAddons\classes\AdminPayPalController;
use PrestaShop\PrestaShop\Core\Addon\Module\ModuleManagerBuilder;
use Symfony\Component\HttpFoundation\JsonResponse;

class AdminPayPalHelpController extends AdminPayPalController
{
    public function init()
    {
        parent::init();

        if (Tools::isSubmit('registerHooks')) {
            if ($this->registerHooks()) {
                $this->confirmations[] = $this->l('Hooks successfully registered');
            }
        }
    }

    public function initContent()
    {
        parent::initContent();

        $countryDefault = new Country((int) \Configuration::get('PS_COUNTRY_DEFAULT'), $this->context->language->id);
        $method = AbstractMethodPaypal::load($this->method);

        if ($method->isSandbox()) {
            $settingLink = 'https://www.sandbox.paypal.com/businessprofile/settings/info/edit';
        } else {
            $settingLink = 'https://www.paypal.com/businessprofile/settings/info/edit';
        }

        $this->context->smarty->assign('settingLink', $settingLink);
        $tpl_vars = [
            'psCheckoutBtnText' => $this->getCheckoutBtnText(),
            'showPsCheckout' => in_array($countryDefault->iso_code, $this->module->countriesApiCartUnavailable),
            'settingLink' => $settingLink,
        ];

        $this->context->smarty->assign($tpl_vars);
        $this->content = $this->context->smarty->fetch($this->getTemplatePath() . 'help.tpl');
        $this->context->smarty->assign('content', $this->content);
        $this->addJS(_PS_MODULE_DIR_ . $this->module->name . '/views/js/helpAdmin.js');
    }

    protected function getCheckoutBtnText()
    {
        $moduleManagerBuilder = ModuleManagerBuilder::getInstance();
        $moduleManager = $moduleManagerBuilder->build();

        if ($moduleManager->isInstalled('ps_checkout')) {
            $psCheckoutBtnText = $this->l('Configure PrestaShop Checkout');
        } elseif (is_dir(_PS_MODULE_DIR_ . 'ps_checkout')) {
            $psCheckoutBtnText = $this->l('Install PrestaShop Checkout');
        } else {
            $psCheckoutBtnText = $this->l('Download PrestaShop Checkout');
        }

        return $psCheckoutBtnText;
    }

    public function displayAjaxCheckCredentials()
    {
        $response = new JsonResponse($this->_checkRequirements());

        return $response->send();
    }

    public function registerHooks()
    {
        return $this->module->registerHooks();
    }
}
