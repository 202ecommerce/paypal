<?php

use PaypalAddons\classes\AbstractMethodPaypal;
use PaypalAddons\classes\API\Onboarding\PaypalGetAuthToken;
use PaypalAddons\classes\API\Onboarding\PaypalGetCredentials;
use PaypalAddons\classes\Constants\PaypalConfigurations;
use PaypalAddons\classes\Form\AccountForm;
use PaypalAddons\classes\Form\CheckoutForm;
use PaypalAddons\classes\Form\FeatureChecklistForm;
use PaypalAddons\classes\Form\FormInstallment;
use PaypalAddons\classes\Form\OrderStatusForm;
use PaypalAddons\classes\Form\ShortcutConfigurationForm;
use PaypalAddons\classes\Form\TechnicalChecklistForm;
use PaypalAddons\classes\Form\TrackingParametersForm;
use PaypalAddons\classes\Form\WhiteListForm;
use PaypalAddons\classes\InstallmentBanner\ConfigurationMap;
use PaypalAddons\classes\Shortcut\ShortcutPreview;
use Symfony\Component\HttpFoundation\JsonResponse;

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
class AdminPaypalConfigurationController extends \ModuleAdminController
{
    public $bootstrap = false;

    protected $forms = [];

    protected $method;

    public function __construct()
    {
        parent::__construct();

        $this->initForms();
        $this->method = AbstractMethodPaypal::load();
    }

    protected function initForms()
    {
        $isoCountryDefault = Tools::strtolower(Country::getIsoById(Configuration::get('PS_COUNTRY_DEFAULT')));
        $this->forms['checkoutForm'] = new CheckoutForm();
        $this->forms['trackingForm'] = new TrackingParametersForm();

        if (in_array($isoCountryDefault, ConfigurationMap::getBnplAvailableCountries())) {
            $this->forms['formInstallment'] = new FormInstallment();
        }

        $this->forms['whiteListForm'] = new WhiteListForm();
        $this->forms['accountForm'] = new AccountForm();
        $this->forms['orderStatusForm'] = new OrderStatusForm();
        $this->forms['shortcutConfigurationForm'] = new ShortcutConfigurationForm();
        $this->forms['technicalChecklistForm'] = new TechnicalChecklistForm();
        $this->forms['featureChecklistForm'] = new FeatureChecklistForm();
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);
        \Media::addJsDef([
            'controllerUrl' => \AdminController::$currentIndex . '&token=' . \Tools::getAdminTokenLite($this->controller_name),
        ]);
        $this->addJS(_PS_MODULE_DIR_ . 'paypal/views/js/admin.js');
        $this->addCSS(_PS_MODULE_DIR_ . 'paypal/views/css/paypal_bo.css');
    }

    public function initContent()
    {
        $this->content .= $this->renderConfiguration();
        parent::initContent();
    }

    protected function renderConfiguration()
    {
        $tpl = $this->context->smarty->createTemplate($this->getTemplatePath() . 'admin.tpl');
        /** @var \PaypalAddons\classes\Form\FormInterface $form */
        foreach ($this->forms as $formName => $form) {
            $tpl->assign($formName, $form->getDescription());
        }

        $tpl->assign([
            'moduleDir' => _MODULE_DIR_ . $this->module->name,
            'isShowModalConfiguration' => (int) Configuration::get(PaypalConfigurations::SHOW_MODAL_CONFIGURATION),
            'diagnosticPage' => $this->context->link->getAdminLink('AdminPaypalDiagnostic'),
            'loggerPage' => $this->context->link->getAdminLink('AdminPaypalProcessLogger'),
            'isConfigured' => $this->method->isConfigured(),
            'isSandbox' => $this->method->isSandbox(),
            'merchantId' => $this->method->getMerchantId(),
        ]);

        return $tpl->fetch();
    }

    public function ajaxProcessSaveForm()
    {
        Configuration::updateValue(PaypalConfigurations::SHOW_MODAL_CONFIGURATION, 0);
        $data = [];
        $response = new JsonResponse();

        try {
            /** @var \PaypalAddons\classes\Form\FormInterface $form */
            foreach ($this->forms as $form) {
                $form->save();
            }

            $data['success'] = true;
        } catch (Throwable $e) {
            $data['success'] = false;
        } catch (Exception $e) {
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

        if ($result->isSuccess() == false) {
            $response->setData(['success' => false, 'message' => $result->getError()->getMessage()])->send();
            exit;
        }

        $authToken = $result->getAuthToken();

        $partnerId = $isSandbox ? PayPal::PAYPAL_PARTNER_ID_SANDBOX : PayPal::PAYPAL_PARTNER_ID_LIVE;
        $paypalGetCredentials = new PaypalGetCredentials($authToken, $partnerId, $isSandbox);
        $result = $paypalGetCredentials->execute();

        if ($result->isSuccess() == false) {
            $response->setData(['success' => false, 'messeage' => $result->getError()->getMessage()])->send();
            exit;
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
        ]);
        $response->setData([
            'success' => true,
            'content' => $template->fetch(),
        ]);
        $response->send();
        exit;
    }

    public function initPageHeaderToolbar()
    {
        parent::initPageHeaderToolbar();
        $this->context->smarty->clearAssign('help_link');
    }
}
