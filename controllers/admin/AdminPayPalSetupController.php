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
use PaypalAddons\classes\API\Onboarding\PaypalGetAuthToken;
use PaypalAddons\classes\API\Onboarding\PaypalGetCredentials;
use PaypalAddons\classes\PUI\PuiFunctionality;
use PaypalAddons\classes\PUI\SignUpLinkButton;
use PaypalAddons\classes\PuiMethodInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class AdminPayPalSetupController extends AdminPayPalController
{
    protected $headerToolBar = true;

    public function __construct()
    {
        parent::__construct();
        $this->parametres = [
            'paypal_api_intent',
            'paypal_sandbox',
            'paypal_ec_secret_sandbox',
            'paypal_ec_secret_live',
            'paypal_ec_clientid_sandbox',
            'paypal_ec_clientid_live',
            'paypal_sandbox_clientid',
            'paypal_live_clientid',
            'paypal_sandbox_secret',
            'paypal_live_secret',
            'paypal_mb_sandbox_clientid',
            'paypal_mb_live_clientid',
            'paypal_mb_sandbox_secret',
            'paypal_mb_live_secret',
        ];
    }

    public function init()
    {
        parent::init();

        if (Tools::getValue('useWithoutBraintree')) {
            Configuration::updateValue('PAYPAL_USE_WITHOUT_BRAINTREE', 1);
        }

        // Don't show somes tabs if a module is not configured
        $method = AbstractMethodPaypal::load();
        $isShowTabs = $method->isConfigured();

        foreach (['AdminPayPalCustomizeCheckout', 'AdminPayPalLogs'] as $adminController) {
            $tab = Tab::getInstanceFromClassName($adminController);
            if ($isShowTabs != $tab->active) {
                $tab->active = $isShowTabs;
                $tab->save();
            }
        }

        // Activate/Disactivate AdminPaypalInstallment tab
        $this->module->hookActionLocalizationPageSave([]);
    }

    public function initContent()
    {
        parent::initContent();
        if ($this->module->showWarningForUserBraintree()) {
            $this->content = $this->context->smarty->fetch($this->getTemplatePath() . '_partials/messages/forBraintreeUsers.tpl');
            $this->context->smarty->assign('content', $this->content);

            return;
        }

        if ($this->method == 'PPP' && $this->module->showWarningForPayPalPlusUsers()) {
            $this->warnings[] = $this->context->smarty->fetch($this->getTemplatePath() . '_partials/messages/forPayPalPlusUsers.tpl');
        }

        $countryDefault = new Country((int) \Configuration::get('PS_COUNTRY_DEFAULT'), $this->context->language->id);
        $tpl_vars = [
            'country_iso' => $countryDefault->iso_code,
        ];
        $this->initAccountSettingsBlock();
        $formAccountSettings = $this->renderForm();
        $this->clearFieldsForm();
        $tpl_vars['formAccountSettings'] = $formAccountSettings;

        if (in_array($this->method, ['EC', 'MB'])) {
            $this->initPaymentSettingsBlock();
            $formPaymentSettings = $this->renderForm();
            $this->clearFieldsForm();
            $tpl_vars['formPaymentSettings'] = $formPaymentSettings;
        }

        $this->initStatusBlock();
        $formStatus = $this->renderForm();
        $this->clearFieldsForm();

        $tpl_vars['formStatus'] = $formStatus;

        if ($this->isShowInstallmentPopup()) {
            $tpl_vars['installmentController'] = $this->context->link->getAdminLink('AdminPayPalInstallment', true);
            $tpl_vars['showInstallmentPopup'] = true;
        }

        $this->context->smarty->assign($tpl_vars);
        $this->content = $this->context->smarty->fetch($this->getTemplatePath() . 'setup.tpl');
        $this->context->smarty->assign('content', $this->content);
        $this->addJS(_PS_MODULE_DIR_ . $this->module->name . '/views/js/adminSetup.js?v=' . $this->module->version);
    }

    public function initAccountSettingsBlock()
    {
        $this->fields_form['form']['form'] = [
            'legend' => [
                'title' => $this->l('Account settings'),
                'icon' => 'icon-cogs',
            ],
            'input' => [
                [
                    'type' => 'html',
                    'html_content' => $this->getHtmlBlockAccountSetting(),
                    'name' => '',
                    'col' => 12,
                    'label' => '',
                ],
            ],
            'id_form' => 'pp_config_account',
        ];

        $countryDefault = new Country((int) \Configuration::get('PS_COUNTRY_DEFAULT'), $this->context->language->id);

        if ($this->method == 'MB' || in_array($countryDefault->iso_code, ['IN', 'JP'])) {
            $this->fields_form['form']['form']['submit'] = [
                'title' => $this->l('Save'),
                'class' => 'btn btn-default pull-right button',
                'name' => 'saveMbCredentialsForm',
            ];
        }
    }

    public function getHtmlBlockAccountSetting()
    {
        $method = AbstractMethodPaypal::load($this->method);
        $tpl_vars = $method->getTplVars();
        $tpl_vars['method'] = $this->method;

        if ($method instanceof PuiMethodInterface) {
            if ($method->isConfigured()) {
                $tpl_vars['isPuiAvailable'] = $this->initPuiFunctionality()->isAvailable();
            } else {
                $tpl_vars['SignUpLinkButton'] = $this->initSignUpLinkButton($method);
            }
        }

        $this->context->smarty->assign($tpl_vars);
        $html_content = $this->context->smarty->fetch($this->getTemplatePath() . '_partials/accountSettingsBlock.tpl');

        return $html_content;
    }

    public function initPaymentSettingsBlock()
    {
        $inputGroup = [];

        if ($this->isPaymentModeSetted() == false) {
            $inputGroup[] = [
                'type' => 'html',
                'html_content' => $this->module->displayWarning($this->l('An error occurred while saving "Payment action" configuration. Please save this configuration again for avoiding any payment errors.')),
                'name' => '',
                'col' => 12,
                'label' => '',
            ];
        }

        $paymentModeInput = [
            'type' => 'select',
            'name' => 'paypal_api_intent',
            'options' => [
                'query' => [
                    [
                        'id' => 'sale',
                        'name' => $this->l('Sale'),
                    ],
                    [
                        'id' => 'authorize',
                        'name' => $this->l('Authorize'),
                    ],
                ],
                'id' => 'id',
                'name' => 'name',
            ],
        ];

        if ($this->method == 'MB') {
            $paymentModeInput['label'] = $this->l('Payment action (for PayPal Express Checkout only)');
            $paymentModeInput['hint'] = $this->l('You can change the payment action only for PayPal Express Checkout payments. If you are using PayPal Plus the "Sale" action is the only possible action.');
        } else {
            $paymentModeInput['label'] = $this->l('Payment action');
        }

        $inputGroup[] = $paymentModeInput;
        $inputGroup[] = [
            'type' => 'html',
            'name' => '',
            'col' => 12,
            'label' => '',
            'html_content' => $this->module->displayInformation($this->l('We recommend Authorize process only for lean manufacturers and craft products sellers.')),
        ];

        $this->fields_form['form']['form'] = [
            'legend' => [
                'title' => $this->l('Payment settings'),
                'icon' => 'icon-cogs',
            ],
            'input' => $inputGroup,
            'submit' => [
                'title' => $this->l('Save'),
                'class' => 'btn btn-default pull-right button',
            ],
            'id_form' => 'pp_config_payment',
        ];

        $values = [
            'paypal_api_intent' => Configuration::get('PAYPAL_API_INTENT'),
        ];
        $this->tpl_form_vars = array_merge($this->tpl_form_vars, $values);
    }

    public function initEnvironmentSettings()
    {
        $this->context->smarty->assign('sandbox', (int) \Configuration::get('PAYPAL_SANDBOX'));
        $html_content = $this->context->smarty->fetch($this->getTemplatePath() . '_partials/switchSandboxBlock.tpl');
        $this->fields_form['form']['form'] = [
            'legend' => [
                'title' => $this->l('Environment Settings'),
                'icon' => 'icon-cogs',
            ],
            'input' => [
                [
                    'type' => 'html',
                    'html_content' => $html_content,
                    'name' => '',
                    'col' => 12,
                    'label' => '',
                ],
                [
                    'type' => 'hidden',
                    'name' => 'paypal_sandbox',
                    'col' => 12,
                    'label' => '',
                ],
                ],
                'id_form' => 'pp_config_environment',
        ];
        $values = [
            'paypal_sandbox' => !(int) Configuration::get('PAYPAL_SANDBOX'),
        ];
        $this->tpl_form_vars = array_merge($this->tpl_form_vars, $values);
    }

    public function initStatusBlock()
    {
        $countryDefault = new \Country((int) \Configuration::get('PS_COUNTRY_DEFAULT'), $this->context->language->id);
        $method = AbstractMethodPaypal::load($this->method);

        $tpl_vars = [
            'merchantCountry' => $countryDefault->name,
            'tlsVersion' => $this->_checkTLSVersion(),
            'accountConfigured' => $method == null ? false : $method->isConfigured(),
            'sslActivated' => $this->module->isSslActive(),
        ];

        if ($this->getWebhookOption()->isEnable() && $this->getWebhookOption()->isEligibleContext()) {
            $webhookCheck = $this->_checkWebhook();
            $tpl_vars['showWebhookState'] = true;
            $tpl_vars['webhookState'] = $webhookCheck['state'];
            $tpl_vars['webhookStateMsg'] = $webhookCheck['message'];
        }

        $this->context->smarty->assign($tpl_vars);
        $html_content = $this->context->smarty->fetch($this->getTemplatePath() . '_partials/statusBlock.tpl');
        $this->fields_form[]['form'] = [
            'legend' => [
                'title' => $this->l('Status'),
                'icon' => 'icon-cogs',
            ],
            'input' => [
                [
                    'type' => 'html',
                    'html_content' => $html_content,
                    'name' => '',
                    'col' => 12,
                    'label' => '',
                ],
            ],
        ];
    }

    public function displayAjaxLogoutAccount()
    {
        $response = new JsonResponse();
        $content = [
            'status' => false,
            'redirectUrl' => '',
        ];
        if (Tools::getValue('token') == Tools::getAdminTokenLite($this->controller_name)) {
            $method = AbstractMethodPaypal::load($this->method);
            $method->logOut();
            $content['status'] = true;
            $content['redirectUrl'] = $this->context->link->getAdminLink($this->controller_name);
        }

        $response->setContent(json_encode($content));

        return $response->send();
    }

    public function displayAjaxCheckCredentials()
    {
        $this->initStatusBlock();
        $response = new JsonResponse($this->renderForm());

        return $response->send();
    }

    public function saveForm()
    {
        $result = parent::saveForm();

        $method = AbstractMethodPaypal::load($this->method);
        $method->checkCredentials();

        if (Tools::isSubmit('paypal_sandbox') == false) {
            $this->errors = array_merge($this->errors, $method->errors);
        }

        // We need use some functionality of EC method, so need also to configure MethodEC
        if (Tools::isSubmit('saveMbCredentialsForm')) {
            $methodEC = AbstractMethodPaypal::load('EC');
            $methodEC->setConfig([
                'clientId' => $method->getClientId(),
                'secret' => $method->getSecret(),
            ]);
            $methodEC->checkCredentials();
        }

        return $result;
    }

    public function displayAjaxHandleOnboardingResponse()
    {
        $method = AbstractMethodPaypal::load();
        $authCode = Tools::getValue('authCode');
        $sharedId = Tools::getValue('sharedId');
        $sellerNonce = $method->getSellerNonce();
        $paypalOnboarding = new PaypalGetAuthToken($authCode, $sharedId, $sellerNonce, $method->isSandbox());
        $result = $paypalOnboarding->execute();

        if ($result->isSuccess() === false) {
            $this->log($result->getError()->getMessage());

            return;
        }

        $partnerId = $method->isSandbox() ? PayPal::PAYPAL_PARTNER_ID_SANDBOX : PayPal::PAYPAL_PARTNER_ID_LIVE;
        $paypalGetCredentials = new PaypalGetCredentials($result->getAuthToken(), $partnerId, $method->isSandbox());
        $result = $paypalGetCredentials->execute();

        if ($result->isSuccess()) {
            $params = [
                'clientId' => $result->getClientId(),
                'secret' => $result->getSecret(),
            ];
            $method->setConfig($params);
        } else {
            $this->log($result->getError()->getMessage());
        }
    }

    protected function isPaymentModeSetted()
    {
        return in_array(Configuration::get('PAYPAL_API_INTENT'), ['sale', 'authorize']);
    }

    protected function isShowInstallmentPopup()
    {
        $countryDefault = new Country((int) Configuration::get('PS_COUNTRY_DEFAULT', null, null, $this->context->shop->id));

        if (Validate::isLoadedObject($countryDefault) === false) {
            return false;
        }

        if (Tools::strtolower($countryDefault->iso_code) != 'fr') {
            return false;
        }

        if (false == (int) Configuration::get('PAYPAL_SHOW_INSTALLMENT_POPUP', null, null, $this->context->shop->id)) {
            return false;
        }

        Configuration::updateValue('PAYPAL_SHOW_INSTALLMENT_POPUP', 0, false, null, $this->context->shop->id);

        return true;
    }

    protected function initSignUpLinkButton(PuiMethodInterface $method)
    {
        return new SignUpLinkButton($method);
    }

    protected function initPuiFunctionality()
    {
        return new PuiFunctionality();
    }
}
