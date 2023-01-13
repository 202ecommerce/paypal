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

use PaypalAddons\classes\AbstractMethodPaypal;
use PaypalAddons\classes\API\PaypalApiManager;
use PaypalAddons\classes\PUI\DataUserForm;
use PaypalAddons\classes\PuiMethodInterface;
use PaypalAddons\classes\WhiteList\WhiteListService;

/**
 * Class MethodPPP
 *
 * @see https://paypal.github.io/PayPal-PHP-SDK/ REST API sdk doc
 * @see https://developer.paypal.com/docs/api/payments/v1/ REST API references
 */
class MethodPPP extends AbstractMethodPaypal implements PuiMethodInterface
{
    public $errors = [];

    /** @var bool shortcut payment from product or cart page */
    public $short_cut;

    /** @var string payment payer ID returned by paypal */
    private $payerId;

    /** payment Object IDl*/
    public $paymentId;

    /** @var \PaypalAddons\classes\PUI\DataUserForm */
    protected $puiDataUser;

    /**
     * @return mixed
     */
    public function getPaymentId()
    {
        return (string) $this->paymentId;
    }

    /**
     * @param mixed $paymentId
     */
    public function setPaymentId($paymentId)
    {
        if (is_string($paymentId)) {
            $this->paymentId = $paymentId;
        }

        return $this;
    }

    protected $payment_method = 'PayPal';

    public $advancedFormParametres = [
        'paypal_os_accepted_two',
    ];

    /** @var WhiteListService */
    protected $whiteListService;

    public function __construct()
    {
        $this->paypalApiManager = new PaypalApiManager($this);
        $this->whiteListService = new WhiteListService();
    }

    /**
     * @param $values array replace for tools::getValues()
     */
    public function setParameters($values)
    {
        foreach ($values as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    public function logOut($sandbox = null)
    {
        if ($sandbox == null) {
            $mode = Configuration::get('PAYPAL_SANDBOX') ? 'SANDBOX' : 'LIVE';
        } else {
            $mode = (int) $sandbox ? 'SANDBOX' : 'LIVE';
        }

        Configuration::updateValue('PAYPAL_' . $mode . '_CLIENTID', '');
        Configuration::updateValue('PAYPAL_' . $mode . '_SECRET', '');
        Configuration::updateValue('PAYPAL_CONNECTION_PPP_CONFIGURED', 0);
    }

    /**
     * @see AbstractMethodPaypal::setConfig()
     */
    public function setConfig($params)
    {
        if ($this->isSandbox()) {
            Configuration::updateValue('PAYPAL_SANDBOX_CLIENTID', $params['clientId']);
            Configuration::updateValue('PAYPAL_SANDBOX_SECRET', $params['secret']);
        } else {
            Configuration::updateValue('PAYPAL_LIVE_CLIENTID', $params['clientId']);
            Configuration::updateValue('PAYPAL_LIVE_SECRET', $params['secret']);
        }
    }

    public function getConfig(Paypal $paypal)
    {
    }

    /**
     * @see AbstractMethodPaypal::confirmCapture()
     */
    public function confirmCapture($orderPayPal)
    {
    }

    /**
     * @see AbstractMethodPaypal::void()
     */
    public function void($orderPayPal)
    {
    }

    /**
     * @return bool
     */
    public function isConfigured()
    {
        if ($this->whiteListService->isEnabled() && !$this->whiteListService->isEligibleContext()) {
            return false;
        }

        if ($this->isCredentialsSetted() === false) {
            return false;
        }

        if ((bool) Configuration::get('PAYPAL_CONNECTION_PPP_CONFIGURED')) {
            return true;
        }

        $this->checkCredentials();

        return (bool) Configuration::get('PAYPAL_CONNECTION_PPP_CONFIGURED');
    }

    /**
     * Assign form data for Paypal Plus payment option
     *
     * @return bool
     */
    public function assignJSvarsPaypalPlus()
    {
        $context = Context::getContext();
        try {
            $approval_url = $this->init()->getApproveLink();
            $context->cookie->__set('paypal_plus_payment', $this->paymentId);
        } catch (Throwable $e) {
            return false;
        } catch (Exception $e) {
            return false;
        }

        $paypal = Module::getInstanceByName('paypal');
        $address_invoice = new Address($context->cart->id_address_invoice);
        $country_invoice = new Country($address_invoice->id_country);

        Media::addJsDef([
            'approvalUrlPPP' => $approval_url,
            'idPaymentPPP' => $this->getPaymentId(),
            'modePPP' => Configuration::get('PAYPAL_SANDBOX') ? 'sandbox' : 'live',
            'languageIsoCodePPP' => $context->language->iso_code,
            'countryIsoCodePPP' => $country_invoice->iso_code,
            'ajaxPatchUrl' => $context->link->getModuleLink('paypal', 'pppPatch', [], true),
        ]);
        Media::addJsDefL('waitingRedirectionMsg', $paypal->l('In few seconds, you will be redirected to PayPal. Please wait.', get_class($this)));

        return true;
    }

    public function getTplVars()
    {
        $tplVars = [];

        $tplVars['accountConfigured'] = $this->isConfigured();
        $tplVars['urlOnboarding'] = $this->getUrlOnboarding();
        $tplVars['paypalOnboardingLib'] = $this->isSandbox() ?
            'https://www.sandbox.paypal.com/webapps/merchantboarding/js/lib/lightbox/partner.js' :
            'https://www.paypal.com/webapps/merchantboarding/js/lib/lightbox/partner.js';

        return $tplVars;

        $sandboxMode = (int) Configuration::get('PAYPAL_SANDBOX');

        if ($sandboxMode) {
            $tpl_vars = [
                'paypal_sandbox_clientid' => Configuration::get('PAYPAL_SANDBOX_CLIENTID'),
                'paypal_sandbox_secret' => Configuration::get('PAYPAL_SANDBOX_SECRET'),
            ];
        } else {
            $tpl_vars = [
                'paypal_live_secret' => Configuration::get('PAYPAL_LIVE_SECRET'),
                'paypal_live_clientid' => Configuration::get('PAYPAL_LIVE_CLIENTID'),
            ];
        }

        $tpl_vars['accountConfigured'] = $this->isConfigured();
        $tpl_vars['sandboxMode'] = $sandboxMode;

        return $tpl_vars;
    }

    public function checkCredentials()
    {
        $response = $this->paypalApiManager->getAccessTokenRequest()->execute();

        if ($response->isSuccess()) {
            Configuration::updateValue('PAYPAL_CONNECTION_PPP_CONFIGURED', 1);
        } else {
            $this->setConfig([
                'clientId' => '',
                'secret' => '',
            ]);
            Configuration::updateValue('PAYPAL_CONNECTION_PPP_CONFIGURED', 0);

            if ($response->getError()) {
                $this->errors[] = $response->getError()->getMessage();
            }
        }
    }

    public function getAdvancedFormInputs()
    {
        $inputs = [];
        $module = Module::getInstanceByName($this->name);
        $orderStatuses = $module->getOrderStatuses();

        $inputs[] = [
            'type' => 'select',
            'label' => $module->l('Payment accepted and transaction completed', get_class($this)),
            'name' => 'paypal_os_accepted_two',
            'hint' => $module->l('You are currently using the Sale mode (the authorization and capture occur at the same time as the sale). So the payement is accepted instantly and the new order is created in the "Payment accepted" status. You can customize the status for orders with completed transactions. Ex : you can create an additional status "Payment accepted via PayPal" and set it as the default status.', get_class($this)),
            'desc' => $module->l('Default status : Payment accepted', get_class($this)),
            'options' => [
                'query' => $orderStatuses,
                'id' => 'id',
                'name' => 'name',
            ],
        ];

        return $inputs;
    }

    public function getClientId($sandbox = null)
    {
        if ($sandbox === null) {
            $sandbox = $this->isSandbox();
        }

        if ($sandbox) {
            $clientId = Configuration::get('PAYPAL_SANDBOX_CLIENTID');
        } else {
            $clientId = Configuration::get('PAYPAL_LIVE_CLIENTID');
        }

        return $clientId;
    }

    public function getSecret($sandbox = null)
    {
        if ($sandbox === null) {
            $sandbox = $this->isSandbox();
        }

        if ($sandbox) {
            $secret = Configuration::get('PAYPAL_SANDBOX_SECRET');
        } else {
            $secret = Configuration::get('PAYPAL_LIVE_SECRET');
        }

        return $secret;
    }

    public function getIntent()
    {
        return 'CAPTURE';
    }

    public function getReturnUrl()
    {
        return Context::getContext()->link->getModuleLink($this->name, 'pppValidation', [], true);
    }

    public function getCancelUrl()
    {
        return Context::getContext()->link->getPageLink('order', true);
    }

    public function getPaypalPartnerId()
    {
        return getenv('PLATEFORM') == 'PSREAD' ? 'PrestaShop_Cart_Ready_PPP' : 'PRESTASHOP_Cart_SPB';
    }

    public function getShortCut()
    {
        return (bool) $this->short_cut;
    }

    public function setShortCut($shortCut)
    {
        $this->short_cut = (bool) $shortCut;

        return $this;
    }

    public function createPartnerReferrals()
    {
        return $this->paypalApiManager->getPartnerReferralsRequest()->execute();
    }

    public function initPui()
    {
        if ($this->isConfigured() == false) {
            throw new Exception('Module is not configured');
        }

        /** @var $response \PaypalAddons\classes\API\Response\ResponseOrderCreate */
        $response = $this->paypalApiManager->getOrderPuiRequest()->execute();

        if ($response->isSuccess() == false) {
            throw new \Exception($response->getError()->getMessage());
        }

        $getOrderResponse = $this->paypalApiManager->getOrderGetRequest($response->getPaymentId())->execute();

        if ($getOrderResponse->isSuccess() == false) {
            throw new \Exception($getOrderResponse->getError()->getMessage());
        }

        $transactionDetails = [
            'method' => 'PPP',
            'transaction_id' => null,
            'id_payment' => $response->getPaymentId(),
            'payment_method' => $this->getPaymentMethod(),
            'currency' => $getOrderResponse->getPurchaseUnit()->getCurrency(),
            'payment_status' => $getOrderResponse->getStatus(),
            'payment_tool' => 'PAY_UPON_INVOICE',
            'intent' => $this->getIntent(),
            'capture' => false,
        ];
        $paypal = Module::getInstanceByName($this->name);
        $paypal->validateOrder(
            Context::getContext()->cart->id,
            $this->getOrderStatus(),
            $getOrderResponse->getPurchaseUnit()->getAmount(),
            $this->getPaymentMethod(),
            null,
            $transactionDetails
        );
    }

    public function getPuiDataUser()
    {
        return $this->puiDataUser;
    }

    public function setPuiDataUser(DataUserForm $data)
    {
        $this->puiDataUser = $data;
    }

    public function getSellerStatus()
    {
        return $this->paypalApiManager->getSellerStatusRequest()->execute();
    }

    public function acdcGenerateToken()
    {
        return $this->paypalApiManager->getAcdcGenerateTokenRequest()->execute();
    }
}
