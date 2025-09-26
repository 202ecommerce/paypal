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
if (!defined('_PS_VERSION_')) {
    exit;
}

include_once _PS_MODULE_DIR_ . 'paypal/vendor/autoload.php';

use PaypalAddons\classes\AbstractMethodPaypal;
use PaypalAddons\classes\API\Onboarding\PaypalGetAuthToken;
use PaypalAddons\classes\API\Onboarding\PaypalGetCredentials;
use PaypalAddons\classes\Constants\PaypalConfigurations;
use PaypalAddons\classes\Form\AccountForm;
use PaypalAddons\classes\Form\CheckoutForm;
use PaypalAddons\classes\Form\FeatureChecklistForm;
use PaypalAddons\classes\Form\FormInstallment;
use PaypalAddons\classes\Form\FormInstallmentMessaging;
use PaypalAddons\classes\Form\OrderStatusForm;
use PaypalAddons\classes\Form\PrestaShopCloudSyncForm;
use PaypalAddons\classes\Form\ShortcutConfigurationForm;
use PaypalAddons\classes\Form\TechnicalChecklistForm;
use PaypalAddons\classes\Form\TrackingParametersForm;
use PaypalAddons\classes\Form\WhiteListForm;
use PaypalAddons\classes\InstallmentBanner\ConfigurationMap;
use PaypalAddons\classes\Shortcut\ShortcutPreview;
use PaypalAddons\classes\Vaulting\VaultingFunctionality;
use Symfony\Component\HttpFoundation\JsonResponse;

class AdminPaypalConfigurationController extends PaypalAddons\classes\AdminPayPalController
{
    public $bootstrap = false;

    protected $forms = [];

    protected $method;

    /** @var VaultingFunctionality */
    protected $vaultingFunctionality;

    private $is_shown_modal;

    public function __construct()
    {
        parent::__construct();
        $this->is_shown_modal = (int) Configuration::get(PaypalConfigurations::SHOW_MODAL_CONFIGURATION);
        if ((bool) Tools::getValue('isModal') === true) {
            $this->is_shown_modal = 1;
        }
        $this->initForms();
        $this->method = AbstractMethodPaypal::load();
        $this->vaultingFunctionality = new VaultingFunctionality();
    }

    protected function initForms()
    {
        $isoCountryDefault = Tools::strtolower(Country::getIsoById((int) Configuration::get('PS_COUNTRY_DEFAULT')));
        $this->forms['checkoutForm'] = new CheckoutForm((int) Tools::getValue('with_factory'));
        $this->forms['trackingForm'] = new TrackingParametersForm();

        if (in_array($isoCountryDefault, ConfigurationMap::getBnplAvailableCountries())) {
            $this->forms['formInstallment'] = new FormInstallment();

            if ((bool) $this->is_shown_modal === false) {
                $this->forms['formInstallmentMessaging'] = new FormInstallmentMessaging();
            }
        }

        $this->forms['whiteListForm'] = new WhiteListForm();
        $this->forms['accountForm'] = new AccountForm();
        $this->forms['orderStatusForm'] = new OrderStatusForm();
        $this->forms['shortcutConfigurationForm'] = new ShortcutConfigurationForm();
        $this->forms['technicalChecklistForm'] = new TechnicalChecklistForm();
        $this->forms['featureChecklistForm'] = new FeatureChecklistForm();
        $this->forms['cloudSyncForm'] = new PrestaShopCloudSyncForm();
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);
        Media::addJsDef([
            'controllerUrl' => AdminController::$currentIndex . '&token=' . Tools::getAdminTokenLite($this->controller_name),
            'paypal' => [
                'locale' => str_replace('-', '_', Context::getContext()->language->locale),
                'merchantId' => $this->method->getClientId($this->method->isSandbox()),
                'partnerName' => $this->getPartnerId(false),
                'partnerClientId' => $this->getPartnerId(true),
                'messagingConfig' => Configuration::get(ConfigurationMap::MESSENGING_CONFIG, null, null, null, '{}'),
            ],
        ]);
        $this->addJS(_MODULE_DIR_ . 'paypal/views/js/admin.js');
        $this->addJS('https://www.paypalobjects.com/merchant-library/merchant-configurator.js', false);
        $this->addCSS(_MODULE_DIR_ . 'paypal/views/css/paypal_bo.css');
    }

    public function initContent()
    {
        if (Tools::getValue('action') === 'onboarding-completed') {
            $this->method->setSandbox((int) Tools::getValue('sandbox'));
            $this->completeOnboarding();
        }
        $this->content .= $this->renderConfiguration();
        parent::initContent();
    }

    protected function renderConfiguration()
    {
        $tpl = $this->context->smarty->createTemplate($this->getTemplatePath() . 'admin.tpl');
        /** @var PaypalAddons\classes\Form\FormInterface $form */
        foreach ($this->forms as $formName => $form) {
            $tpl->assign($formName, $form->getDescription());
        }

        $tpl->assign([
            'moduleDir' => _MODULE_DIR_ . $this->module->name,
            'moduleFullDir' => _PS_MODULE_DIR_ . $this->module->name,
            'isShowModalConfiguration' => $this->is_shown_modal,
            'diagnosticPage' => $this->context->link->getAdminLink('AdminPaypalDiagnostic'),
            'loggerPage' => $this->context->link->getAdminLink('AdminPaypalProcessLogger'),
            'isConfigured' => $this->method->isConfigured(),
            'isSandbox' => $this->method->isSandbox(),
            'merchantId' => $this->method->getMerchantId(),
        ]);

        return $tpl->fetch();
    }

    protected function getPartnerId($clientId = false)
    {
        $isSandBox = $this->method->isSandbox();
        if ($clientId === true) {
            return $isSandBox === true
                ? PayPal::PAYPAL_PARTNER_CLIENT_ID_SANDBOX
                : PayPal::PAYPAL_PARTNER_CLIENT_ID_LIVE;
        }

        return $isSandBox === true
            ? PayPal::PAYPAL_PARTNER_ID_SANDBOX
            : PayPal::PAYPAL_PARTNER_ID_LIVE;
    }

    public function ajaxProcessSaveForm()
    {
        Configuration::updateValue(PaypalConfigurations::SHOW_MODAL_CONFIGURATION, 0);
        $data = [];
        $response = new JsonResponse();

        try {
            /** @var PaypalAddons\classes\Form\FormInterface $form */
            foreach ($this->forms as $form) {
                $form->save();
            }

            $data['success'] = true;
        } catch (Throwable $e) {
            $data['success'] = false;
        }

        $response->setData($data);
        $response->send();
        exit;
    }

    public function ajaxProcessResetCredentials()
    {
        $method = AbstractMethodPaypal::load();
        $isSandbox = (int) Tools::getValue('isSandbox');
        $method->logOut($isSandbox);

        (new JsonResponse())
            ->setData(['success' => true])
            ->send();
        exit;
    }

    public function ajaxProcessGenerateCredentials()
    {
        $response = new JsonResponse();
        $method = AbstractMethodPaypal::load();
        $authCode = Tools::getValue('authCode');
        $sharedId = Tools::getValue('sharedId');
        $isSandbox = (int) Tools::getValue('isSandbox');
        $sellerNonce = $method->getSellerNonce($isSandbox);
        $paypalOnboarding = new PaypalGetAuthToken($authCode, $sharedId, $sellerNonce, $isSandbox);
        $result = $paypalOnboarding->execute();

        $locale = Context::getContext()->language->locale;
        $errorMessages = [
            $this->module->l(
                'An error occured while trying to link your PayPal\'s account',
                'AdminPaypalConfigurationController',
                $locale
            ),
        ];
        $errorMessages[] = $this->module->l('More details: ', 'AdminPaypalConfigurationController', $locale);
        $tooManyRequestMessage = $this->module->l('Client error response [url]https://api.paypal.com/v1/oauth2/token [status code] 429 [reason phrase] Too many Requests. 429 Unsual activity from this IP address. Too many request to PayPal systems. This could come from a shared infrastructure', 'AdminPaypalConfigurationController', $locale);

        if ($result->isSuccess() == false) {
            if ((int) $result->getError()->getCode() === PayPal::PAYPAL_STATUS_CODE_TOO_MANY_REQUEST) {
                $errorMessages[] = $tooManyRequestMessage;
            } else {
                $errorMessages[] = $result->getError()->getMessage();
            }

            $this->errorTemplate($response, $errorMessages);
        }

        $authToken = $result->getAuthToken();

        $partnerId = $isSandbox ? PayPal::PAYPAL_PARTNER_ID_SANDBOX : PayPal::PAYPAL_PARTNER_ID_LIVE;
        $paypalGetCredentials = new PaypalGetCredentials($authToken, $partnerId, $isSandbox);
        $result = $paypalGetCredentials->execute();

        if ($result->isSuccess() == false) {
            if ((int) $result->getError()->getCode() === PayPal::PAYPAL_STATUS_CODE_TOO_MANY_REQUEST) {
                $errorMessages[] = $tooManyRequestMessage;
            } else {
                $errorMessages[] = $result->getError()->getMessage();
            }

            $this->errorTemplate($response, $errorMessages);
        }

        $params = [
            'clientId' => $result->getClientId(),
            'secret' => $result->getSecret(),
            'merchantId' => $result->getMerchantId(),
            'isSandbox' => $isSandbox,
        ];
        $method->setConfig($params);

        $response->setData([
            'success' => true,
            'clientid' => $result->getClientId(),
            'secret' => $result->getSecret(),
            'merchantId' => $result->getMerchantId(),
            'isSandbox' => $isSandbox,
        ])->send();
        exit;
    }

    /**
     * Send Error message
     *
     * @param JsonResponse $response
     * @param mixed $messages
     */
    private function errorTemplate($response, $messages)
    {
        $template = $this->context->smarty->createTemplate(
            $this->getTemplatePath() . '_partials/alert_ajax.tpl'
        );
        $template->assign('messages', $messages);

        $response->setData([
            'success' => false,
            'message' => $template->fetch(),
        ])->send();
        exit;
    }

    public function ajaxProcessGetShortcut()
    {
        $label = Tools::getValue('label', 'pay');
        $height = (int) Tools::getValue('height', 35);
        $width = (int) Tools::getValue('width', 200);
        $color = Tools::getValue('color', 'gold');
        $shape = Tools::getValue('shape', 'rect');

        $ShortCut = new ShortcutPreview(
            $label,
            $height,
            $width,
            $color,
            $shape
        );

        $response = new JsonResponse(['content' => $ShortCut->render()]);

        $response->send();
        exit;
    }

    public function ajaxProcessRenderTechnicalChecklist()
    {
        $response = new JsonResponse();
        $template = $this->context->smarty->createTemplate($this->getTemplatePath() . '_partials/statusBlock.tpl');
        $template->assign('vars', $this->forms['technicalChecklistForm']->getDescription()['fields']['technicalChecklist']['set']);
        $template->assign('moduleFullDir', _PS_MODULE_DIR_ . $this->module->name);
        $response->setData([
            'success' => true,
            'content' => $template->fetch(),
        ]);
        $response->send();
        exit;
    }

    public function ajaxProcessRenderFeatureChecklist()
    {
        $response = new JsonResponse();
        $template = $this->context->smarty->createTemplate($this->getTemplatePath() . '_partials/featureChecklist.tpl');
        $template->assign('vars', $this->forms['featureChecklistForm']->getDescription()['fields']['featureChecklist']['set']);
        $template->assign('moduleFullDir', _PS_MODULE_DIR_ . $this->module->name);
        $response->setData([
            'success' => true,
            'content' => $template->fetch(),
        ]);
        $response->send();
        exit;
    }

    public function ajaxProcessGetForms()
    {
        $response = new JsonResponse();
        $responseBody = [
            'success' => true,
            'forms' => [],
        ];

        foreach ($this->forms as $form) {
            $desc = $form->getDescription();

            if ($desc['id_form'] == 'pp_account_form') {
                continue;
            }

            if ($desc['id_form'] == 'pp_white_list_form') {
                $tmpPath = $this->getTemplatePath() . '_partials/forms/form.tpl';
            } else {
                $tmpPath = $this->getTemplatePath() . '_partials/forms/' . $desc['id_form'] . '.tpl';
            }

            if (false === file_exists($tmpPath)) {
                continue;
            }

            $template = $this->context->smarty->createTemplate($tmpPath);
            $template->assign('moduleFullDir', _PS_MODULE_DIR_ . $this->module->name);
            $template->assign('form', $desc);
            $template->assign('isModal', (int) Tools::getValue('isModal'));
            $responseBody['forms'][$desc['id_form']] = $template->fetch();
        }

        $response->setData($responseBody);
        $response->send();
        exit;
    }

    public function ajaxProcessGetWelcomeBoard()
    {
        $response = new JsonResponse();
        $template = $this->context->smarty->createTemplate($this->getTemplatePath() . '_partials/welcome-board.tpl');
        $template->assign([
            'diagnosticPage' => $this->context->link->getAdminLink('AdminPaypalDiagnostic'),
            'loggerPage' => $this->context->link->getAdminLink('AdminPaypalProcessLogger'),
            'isConfigured' => $this->method->isConfigured(),
            'isSandbox' => $this->method->isSandbox(),
            'merchantId' => $this->method->getMerchantId(),
            'moduleFullDir' => _PS_MODULE_DIR_ . $this->module->name,
        ]);
        $response->setData([
            'success' => true,
            'content' => $template->fetch(),
        ]);
        $response->send();
        exit;
    }

    public function ajaxProcessGetMessagingConfig()
    {
        $jsonResponse = new JsonResponse();

        if (false === $this->method->isConfigured()) {
            $jsonResponse->setData(['success' => false])->send();
            exit;
        }

        $confing = json_decode(Configuration::get(ConfigurationMap::MESSENGING_CONFIG), true);

        if (!is_array($confing)) {
            $confing = [];
        }

        $country = Configuration::get(ConfigurationMap::MESSAGING_BUYER_COUNTRY, (int) Language::getIdByIso('fr'));
        $locale = $country;
        switch ($country) {
            case 'au':
            case 'gb':
            case 'us':
                $locale = 'en';
                break;
            case '':
                $locale = 'fr';
                $country = 'fr';
                break;
        }
        $locale .= '_' . strtoupper($country);

        $messagingConfig = [
            'placements' => ['product', 'homepage', 'cart', 'checkout', 'category'],
            'merchantIdentifier' => $this->method->getClientId(),
            'partnerClientId' => ($this->method->isSandbox() ? PayPal::PAYPAL_PARTNER_CLIENT_ID_SANDBOX : PayPal::PAYPAL_PARTNER_CLIENT_ID_LIVE),
            'partnerName' => ($this->method->isSandbox() ? PayPal::PAYPAL_PARTNER_ID_SANDBOX : PayPal::PAYPAL_PARTNER_ID_LIVE),
            'bnCode' => $this->method->getPaypalPartnerId(),
            'locale' => $locale,
            'config' => $confing,
        ];

        $jsonResponse->setData(['success' => true, 'config' => $messagingConfig])->send();
        exit;
    }

    public function initPageHeaderToolbar()
    {
        parent::initPageHeaderToolbar();
        $this->context->smarty->clearAssign('help_link');
    }

    protected function completeOnboarding()
    {
        $start = time();
        $waitForCredentials = true;

        while ($waitForCredentials && (time() - $start < 10)) {
            if ($this->method->isCredentialsSetted()) {
                $waitForCredentials = false;
                Configuration::loadConfiguration();
            }
        }

        $this->method->checkCredentials();
        Tools::redirectAdmin($this->context->link->getAdminLink('AdminPaypalConfiguration'));
    }
}
