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

namespace PaypalAddons\classes;

use PaypalAddons\classes\Constants\WebHookConf;
use PaypalAddons\classes\Webhook\CreateWebhook;
use PaypalAddons\classes\Webhook\WebhookAvailability;
use PaypalAddons\classes\Webhook\WebhookHandlerUrl;
use PaypalAddons\classes\Webhook\WebhookOption;
use PaypalPPBTlib\Extensions\ProcessLogger\ProcessLoggerHandler;
use PaypalPPBTlib\Install\ModuleInstaller;
use PrestaShop\PrestaShop\Core\Addon\Module\ModuleManagerBuilder;
use Symfony\Component\HttpFoundation\JsonResponse;

class AdminPayPalController extends \ModuleAdminController
{
    protected $parametres = [];

    protected $method;

    protected $headerToolBar = false;

    public function __construct()
    {
        parent::__construct();
        $this->bootstrap = true;
        $countryDefault = new \Country((int) \Configuration::get('PS_COUNTRY_DEFAULT'), $this->context->language->id);

        switch ($countryDefault->iso_code) {
            case 'DE':
                $this->method = 'PPP';
                break;
            case 'BR':
                $this->method = 'MB';
                break;
            case 'MX':
                $this->method = 'MB';
                break;
            default:
                $this->method = 'EC';
        }
    }

    public function init()
    {
        if (\Tools::getValue('action') === 'set_sandbox_mode') {
            \Configuration::updateValue('PAYPAL_SANDBOX', (int) \Tools::getValue('sandbox_mode'));
            $methodObj = AbstractMethodPaypal::load($this->method);
            $methodObj->isConfigured();
        }

        parent::init();

        if ((int) \Configuration::getGlobalValue(\PayPal::NEED_INSTALL_MODELS) === 1) {
            if ($this->module->installModels()) {
                \Configuration::updateGlobalValue(\PayPal::NEED_INSTALL_MODELS, 0);
            }
        }

        if ((int) \Configuration::getGlobalValue(\PayPal::NEED_INSTALL_EXTENSIONS) === 1) {
            $installer = new ModuleInstaller($this->module);
            $installer->installExtensions();
            \Configuration::deleteByName(\PayPal::NEED_INSTALL_EXTENSIONS);
        }
    }

    public function initContent()
    {
        if (false == $this->ajax) {
            header('Cache-Control: max-age=0');
            header('Clear-Site-Data: "cache"');
        }

        $method = AbstractMethodPaypal::load();

        if ((int) \Configuration::get('PAYPAL_SANDBOX') == 1) {
            $message = $this->module->l('Your PayPal account is currently configured to accept payments on Sandbox.', 'AdminPayPalController');
            $message .= ' (<b>' . $this->module->l('test environment', 'AdminPayPalController') . '</b>). ';
            $message .= $this->module->l('Any transaction will be fictitious. Disable the option to accept actual payments (live environment) and log in with your PayPal credentials.', 'AdminPayPalController');
            $this->warnings[] = $message;
        }

        if ((int) \Configuration::get('PAYPAL_NEED_CHECK_CREDENTIALS')) {
            $method->checkCredentials();
            \Configuration::updateValue('PAYPAL_NEED_CHECK_CREDENTIALS', 0);
        }

        $need_rounding = false;

        if (\Configuration::get('PS_ROUND_TYPE') != \Order::ROUND_ITEM
            || \Configuration::get('PS_PRICE_ROUND_MODE') != PS_ROUND_HALF_UP
            || \Configuration::get('PS_PRICE_DISPLAY_PRECISION') != 2) {
            $need_rounding = true;
        }

        $showWarningForUserBraintree = $this->module->showWarningForUserBraintree();
        $showPsCheckoutInfo = $this->module->showPsCheckoutMessage();
        $this->context->smarty->assign([
            'showWarningForUserBraintree' => $showWarningForUserBraintree,
            'methodType' => $this->method,
            'moduleDir' => _MODULE_DIR_,
            'showPsCheckoutInfo' => $showPsCheckoutInfo,
            'headerToolBar' => $this->headerToolBar,
            'showRestApiIntegrationMessage' => $this->isShowRestApiIntegrationMessage(),
            'psVersion' => _PS_VERSION_,
            'need_rounding' => $need_rounding,
            'isModeSandbox' => $method->isSandbox(),
        ]);
    }

    public function renderForm($fields_form = null)
    {
        if ($fields_form === null) {
            $fields_form = $this->fields_form;
        }
        $helper = new \HelperForm();
        $helper->token = \Tools::getAdminTokenLite($this->controller_name);
        $helper->currentIndex = \AdminController::$currentIndex;
        $helper->submit_action = $this->controller_name . '_config';
        $default_lang = (int) \Configuration::get('PS_LANG_DEFAULT');
        $helper->default_form_language = $default_lang;
        $helper->allow_employee_form_lang = $default_lang;
        $helper->tpl_vars = [
            'fields_value' => $this->tpl_form_vars,
            'id_language' => $this->context->language->id,
        ];

        return $helper->generateForm($fields_form);
    }

    public function clearFieldsForm()
    {
        $this->fields_form = [];
        $this->tpl_form_vars = [];
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);
        \Media::addJsDef([
            'controllerUrl' => \AdminController::$currentIndex . '&token=' . \Tools::getAdminTokenLite($this->controller_name),
        ]);
        $this->addCSS(_PS_MODULE_DIR_ . $this->module->name . '/views/css/paypal_bo.css');
    }

    protected function _checkRequirements()
    {
        $response = [
            'success' => true,
            'message' => [],
        ];
        $hooksUnregistered = $this->module->getHooksUnregistered();
        if (empty($hooksUnregistered) == false) {
            $response['success'] = false;
            $response['message'][] = $this->getHooksUnregisteredMessage($hooksUnregistered);
        }

        if ((int) \Configuration::get('PS_COUNTRY_DEFAULT') == false) {
            $response['success'] = false;
            $response['message'][] = $this->module->l('To activate a payment solution, please select your default country.', 'AdminPayPalController');
        }

        if ($this->module->isSslActive() == false) {
            $response['success'] = false;
            $response['message'][] = $this->module->l('SSL should be enabled on your website.', 'AdminPayPalController');
        }

        $tls_check = $this->_checkTLSVersion();
        if ($tls_check['status'] == false) {
            $response['success'] = false;
            $response['message'][] = $this->module->l('Tls verification failed.', 'AdminPayPalController') . ' ' . $tls_check['error_message'];
        }

        if ($this->getWebhookOption()->isEnable()) {
            $webhookCheck = $this->_checkWebhook();

            if ($webhookCheck['state'] == false) {
                $response['success'] = false;
                $response['message'][] = $webhookCheck['message'];
            }
        }

        if ($response['success']) {
            $response['message'][] = $this->module->l('Your shop configuration is OK. You can start configuring your PayPal module.', 'AdminPayPalController');
        }

        return $response;
    }

    /**
     * Check TLS version 1.2 compability : CURL request to server
     */
    protected function _checkTLSVersion()
    {
        $return = [
            'status' => false,
            'error_message' => '',
        ];

        if (defined('CURL_SSLVERSION_TLSv1_2') == false) {
            define('CURL_SSLVERSION_TLSv1_2', 6);
        }

        $tls_server = $this->context->link->getModuleLink($this->module->name, 'tlscurltestserver');
        $curl = curl_init($tls_server);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($curl, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
        $response = curl_exec($curl);
        if (trim($response) != 'ok') {
            $return['status'] = false;
            $curl_info = curl_getinfo($curl);
            if ($curl_info['http_code'] == 401) {
                $return['error_message'] = $this->module->l('401 Unauthorised. Please note that the TLS verification can\'t be done if you have htaccess password protection, debug or maintenance mode enabled on your web site.', 'AdminPayPalController');
            } else {
                $return['error_message'] = curl_error($curl);
            }
        } else {
            $return['status'] = true;
        }

        return $return;
    }

    public function postProcess()
    {
        if (\Tools::isSubmit('checkCredentials')) {
            $method = AbstractMethodPaypal::load($this->method);
            $method->checkCredentials();
            $this->errors = array_merge($this->errors, $method->errors);
        }

        if (\Tools::isSubmit($this->controller_name . '_config')) {
            if ($this->saveForm()) {
                $this->confirmations[] = $this->module->l('Successful update.', 'AdminPayPalController');
            }
        }

        if (empty($this->errors) == false) {
            $this->errors = array_unique($this->errors);
            foreach ($this->errors as $error) {
                $this->log($error);
            }
        }

        parent::postProcess();
    }

    public function saveForm()
    {
        $result = true;

        foreach (\Tools::getAllValues() as $fieldName => $fieldValue) {
            if (in_array($fieldName, $this->parametres)) {
                $result &= \Configuration::updateValue(\Tools::strtoupper($fieldName), pSQL($fieldValue));
            }
        }

        return $result;
    }

    public function log($message)
    {
        ProcessLoggerHandler::openLogger();
        ProcessLoggerHandler::logError($message, null, null, null, null, null, (int) \Configuration::get('PAYPAL_SANDBOX'));
        ProcessLoggerHandler::closeLogger();
    }

    /**
     *  @param array $hooks array of the hooks name
     *
     *  @return string
     */
    public function getHooksUnregisteredMessage($hooks)
    {
        if (is_array($hooks) == false || empty($hooks)) {
            return '';
        }

        $this->context->smarty->assign('hooks', $hooks);

        return $this->context->smarty->fetch($this->getTemplatePath() . '_partials/messages/unregisteredHooksMessage.tpl');
    }

    public function displayAjaxHandlePsCheckoutAction()
    {
        $action = \Tools::getValue('actionHandled');
        $response = [];

        switch ($action) {
            case 'close':
                $this->module->setPsCheckoutMessageValue(true);
                break;
            case 'install':
                if (is_dir(_PS_MODULE_DIR_ . 'ps_checkout') == false) {
                    $response = [
                        'redirect' => true,
                        'url' => 'https://addons.prestashop.com/en/payment-card-wallet/46347-prestashop-checkout-built-with-paypal.html',
                    ];
                } else {
                    if ($this->installPsCheckout()) {
                        $response = [
                            'redirect' => true,
                            'url' => $this->context->link->getAdminLink('AdminModules', true, [], ['configure' => 'ps_checkout']),
                        ];
                    } else {
                        $response = [
                            'redirect' => false,
                            'url' => 'someUrl',
                        ];
                    }
                }
                break;
        }

        $jsonResponse = new JsonResponse($response);

        return $jsonResponse->send();
    }

    public function displayAjaxUpdateRoundingSettings()
    {
        if (\Shop::getContext() == \Shop::CONTEXT_ALL) {
            $idShops = array_column(\Shop::getShops(), 'id_shop');
            $idShops[] = null;
        } else {
            $idShops = [$this->context->shop->id];
        }

        foreach ($idShops as $idShop) {
            \Configuration::updateValue(
                'PS_ROUND_TYPE',
                '1',
                false,
                null,
                $idShop
            );

            \Configuration::updateValue(
                'PS_PRICE_ROUND_MODE',
                '2',
                false,
                null,
                $idShop
            );

            \Configuration::updateValue(
                'PS_PRICE_DISPLAY_PRECISION',
                '2',
                false,
                null,
                $idShop
            );
        }

        $message = $this->module->l('Settings updated. Your rounding settings are compatible with PayPal!', 'AdminPayPalController');

        $this->ajaxDie($message);
    }

    public function installPsCheckout()
    {
        $moduleManagerBuilder = ModuleManagerBuilder::getInstance();
        $moduleManager = $moduleManagerBuilder->build();

        if ($moduleManager->isInstalled('ps_checkout') == true) {
            return true;
        }

        return $moduleManager->install('ps_checkout');
    }

    protected function isShowRestApiIntegrationMessage()
    {
        $method = AbstractMethodPaypal::load();

        if (version_compare('5.2.0', \Configuration::get('PAYPAL_PREVIOUS_VERSION'), '>') && $method->isConfigured() === false) {
            return true;
        }

        return false;
    }

    public function initPageHeaderToolbar()
    {
        $query = [
            'token' => $this->token,
            'action' => 'set_sandbox_mode',
            'sandbox_mode' => \Configuration::get('PAYPAL_SANDBOX') ? 0 : 1,
        ];
        $this->page_header_toolbar_btn['switch_sandbox'] = [
            'desc' => $this->module->l('Sandbox mode', 'AdminPayPalController'),
            'icon' => 'process-icon-toggle-' . (\Configuration::get('PAYPAL_SANDBOX') ? 'on' : 'off'),
            'help' => $this->module->l('Sandbox mode is the test environment where you\'ll be not able to collect any real payments.', 'AdminPayPalController'),
            'href' => self::$currentIndex . '?' . http_build_query($query),
        ];

        parent::initPageHeaderToolbar();
        $this->context->smarty->clearAssign('help_link');
    }

    /**
     * @return WebhookOption
     */
    protected function getWebhookOption()
    {
        return new WebhookOption();
    }

    /**
     * @return bool
     */
    protected function isWebhookCreated()
    {
        $response = (new CreateWebhook(AbstractMethodPaypal::load()))
            ->setUpdate(false)
            ->execute();

        return $response->isSuccess();
    }

    /**
     * @return string
     */
    protected function getWebhookHandler()
    {
        return (new WebhookHandlerUrl())->get();
    }

    protected function _checkWebhook()
    {
        $return = [
            'state' => true,
            'message' => $this->module->l('PayPal webhooks are enabled with success.', 'AdminPayPalController'),
        ];

        $webhookAvailable = $this->getWebhookAvalability()->check();

        if ($webhookAvailable->isSuccess() == false) {
            $return['state'] = false;
            $return['message'] = $this->context->smarty->fetch(
                $this->getTemplatePath() . '_partials/messages/webhookhandler_not_available.tpl'
            );
        }

        if ($return['state'] && !$this->isWebhookCreated()) {
            $return['state'] = false;
            $return['message'] = $this->module->l('PayPal webhooks can not be enabled. The webhook listener was not created. Webhooks are not used by the module until the moment the problem will be fixed. Please try to refresh the page and click on \'check requirements\' again.', 'AdminPayPalController');
        }

        \Configuration::updateValue(WebHookConf::AVAILABLE, (int) $return['state']);

        return $return;
    }

    protected function getWebhookAvalability()
    {
        return new WebhookAvailability();
    }
}
