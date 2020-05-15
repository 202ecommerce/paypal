<?php
/**
 * 2007-2019 PrestaShop
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
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author 2007-2019 PayPal
 *  @author 202 ecommerce <tech@202-ecommerce.com>
 *  @copyright PayPal
 *  @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 *
 */


use PaypalAddons\classes\API\PaypalApiManager;
use PaypalPPBTlib\Extensions\ProcessLogger\ProcessLoggerHandler;
use PaypalAddons\classes\AbstractMethodPaypal;

/**
 * Class MethodPPP
 * @see https://paypal.github.io/PayPal-PHP-SDK/ REST API sdk doc
 * @see https://developer.paypal.com/docs/api/payments/v1/ REST API references
 */
class MethodPPP extends AbstractMethodPaypal
{
    public $errors = array();

    /** @var boolean shortcut payment from product or cart page*/
    public $short_cut;

    /** @var string payment payer ID returned by paypal*/
    private $payerId;

    /** payment Object IDl*/
    public $paymentId;

    /**
     * @return mixed
     */
    public function getPaymentId()
    {
        return $this->paymentId;
    }

    /**
     * @param mixed $paymentId
     */
    public function setPaymentId($paymentId)
    {
        $this->paymentId = $paymentId;
        return $this;
    }

    protected $payment_method = 'PayPal';

    public $advancedFormParametres = array(
        'paypal_os_accepted_two'
    );

    public function __construct()
    {
        $this->paypalApiManager = new PaypalApiManager($this);
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
            $mode = (int)$sandbox ? 'SANDBOX' : 'LIVE';
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
     * @see AbstractMethodPaypal::init()
     */
    /*public function init()
    {
        //Todo
    }*/

    public function formatPrice($price)
    {
        $context = Context::getContext();
        $context_currency = $context->currency;
        $paypal = Module::getInstanceByName($this->name);
        if ($id_currency_to = $paypal->needConvert()) {
            $currency_to_convert = new Currency($id_currency_to);
            $price = Tools::convertPriceFull($price, $context_currency, $currency_to_convert);
        }
        $price = number_format($price, Paypal::getDecimal(), ".", '');
        return $price;
    }

    /**
     * @see AbstractMethodPaypal::validation()
     */
    public function validation()
    {
        $context = Context::getContext();
        $cart = $context->cart;
        $customer = new Customer($cart->id_customer);

        if (!Validate::isLoadedObject($customer)) {
            throw new Exception('Customer is not loaded object');
        }

        if ($this->getPaymentId() == false) {
            throw new Exception('Payment ID isn\'t setted');
        }

        $response = $this->paypalApiManager->getOrderCaptureRequest($this->getPaymentId())->execute();

        if ($response->isSuccess() == false) {
            throw new Exception($response->getError()->getMessage());
        }


        $this->setDetailsTransaction($response);
        $currency = $context->currency;
        $total = $response->getTotalPaid();
        $paypal = Module::getInstanceByName($this->name);
        $order_state = $this->getOrderStatus();
        $paypal->validateOrder($cart->id,
            $order_state,
            $total,
            $this->getPaymentMethod(),
            null,
            $this->getDetailsTransaction(),
            (int)$currency->id,
            false,
            $customer->secure_key);
    }

    public function getOrderStatus()
    {
        if ((int)Configuration::get('PAYPAL_CUSTOMIZE_ORDER_STATUS')) {
            $orderStatus = (int)Configuration::get('PAYPAL_OS_ACCEPTED_TWO');
        } else {
            $orderStatus = (int)Configuration::get('PS_OS_PAYMENT');
        }

        return $orderStatus;
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
     * Get payment details
     * @param $id_payment
     * @return bool|mixed
     */
    public function getInstructionInfo($id_payment)
    {
        if ($this->isConfigured() == false) {
            return false;
        }
        try {
            $sale = Payment::get($id_payment, $this->_getCredentialsInfo());
        } catch (Exception $e) {
            $backtrace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 1)[0];
            $message = 'Error in ' . $backtrace['file'];
            $message .= ' (line ' . $backtrace['line'] . '); ';
            $message .= 'Message: ' . $e->getMessage() . ';';
            $message .= 'File: ' . $e->getFile() . '; ';
            $message .= 'Line: ' . $e->getLine();

            ProcessLoggerHandler::openLogger();
            ProcessLoggerHandler::logError($message);
            ProcessLoggerHandler::closeLogger();

            return false;
        }

        return $sale->payment_instruction;
    }

    public function renderExpressCheckoutShortCut(&$context, $type, $page_source)
    {
        $lang = $context->language->iso_code;
        $environment = (Configuration::get('PAYPAL_SANDBOX')?'sandbox':'live');
        $img_esc = "modules/paypal/views/img/ECShortcut/".Tools::strtolower($lang)."/buy/buy.png";

        if (!file_exists(_PS_ROOT_DIR_.'/'.$img_esc)) {
            $img_esc = "modules/paypal/views/img/ECShortcut/us/buy/buy.png";
        }
        $shop_url = Context::getContext()->link->getBaseLink(Context::getContext()->shop->id, true);
        $context->smarty->assign(array(
            'shop_url' => $shop_url,
            'PayPal_payment_type' => $type,
            'PayPal_img_esc' => $shop_url.$img_esc,
            'action_url' => $context->link->getModuleLink($this->name, 'ScInit', array(), true),
            'environment' => $environment,
        ));
        if ($page_source == 'product') {
            $context->smarty->assign(array(
                'es_cs_product_attribute' => Tools::getValue('id_product_attribute')
            ));
        }
        $context->smarty->assign('source_page', $page_source);
        return $context->smarty->fetch('module:paypal/views/templates/hook/shortcut.tpl');
    }

    /**
     * @return bool
     */
    public function isConfigured()
    {
        return (bool)Configuration::get('PAYPAL_CONNECTION_PPP_CONFIGURED');
    }

    /**
     * @return bool
     */
    public function credentialsSetted($mode = null)
    {
        if ($mode == null) {
            $mode = (int) Configuration::get('PAYPAL_SANDBOX');
        }

        if ($mode) {
            return (bool)Configuration::get('PAYPAL_SANDBOX_CLIENTID') && (bool)Configuration::get('PAYPAL_SANDBOX_SECRET');
        } else {
            return (bool)Configuration::get('PAYPAL_LIVE_CLIENTID') && (bool)Configuration::get('PAYPAL_LIVE_SECRET');
        }
    }

    /**
     * Assign form data for Paypal Plus payment option
     * @return boolean
     */
    public function assignJSvarsPaypalPlus()
    {
        $context = Context::getContext();
        try {
            $approval_url = $this->init()->getApproveLink();
            $context->cookie->__set('paypal_plus_payment', $this->paymentId);
        } catch (Exception $e) {
            return false;
        }

        $paypal = Module::getInstanceByName('paypal');
        $address_invoice = new Address($context->cart->id_address_invoice);
        $country_invoice = new Country($address_invoice->id_country);

        Media::addJsDef(array(
            'approvalUrlPPP' => $approval_url,
            'idPaymentPPP' => $this->getPaymentId(),
            'modePPP' => Configuration::get('PAYPAL_SANDBOX')  ? 'sandbox' : 'live',
            'languageIsoCodePPP' => $context->language->iso_code,
            'countryIsoCodePPP' => $country_invoice->iso_code,
            'ajaxPatchUrl' => $context->link->getModuleLink('paypal', 'pppPatch', array(), true),
        ));
        Media::addJsDefL('waitingRedirectionMsg', $paypal->l('In few seconds, you will be redirected to PayPal. Please wait.', get_class($this)));

        return true;
    }

    public function getTplVars()
    {
        $tplVars = array();

        $tplVars['accountConfigured'] = $this->isConfigured();
        $tplVars['urlOnboarding'] = $this->getUrlOnboarding();

        \Media::addJsDef([
            'paypalOnboardingLib' => $this->isSandbox() ? 'https://www.sandbox.paypal.com/webapps/merchantboarding/js/lib/lightbox/partner.js' : 'https://www.paypal.com/webapps/merchantboarding/js/lib/lightbox/partner.js'
        ]);

        return $tplVars;



        $sandboxMode = (int)Configuration::get('PAYPAL_SANDBOX');

        if ($sandboxMode) {
            $tpl_vars = array(
                'paypal_sandbox_clientid' => Configuration::get('PAYPAL_SANDBOX_CLIENTID'),
                'paypal_sandbox_secret' => Configuration::get('PAYPAL_SANDBOX_SECRET'),
            );
        } else {
            $tpl_vars = array(
                'paypal_live_secret' => Configuration::get('PAYPAL_LIVE_SECRET'),
                'paypal_live_clientid' => Configuration::get('PAYPAL_LIVE_CLIENTID')
            );
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
            Configuration::updateValue('PAYPAL_CONNECTION_PPP_CONFIGURED', 0);

            if ($response->getError()) {
                $this->errors[] = $response->getError()->getMessage();
            }
        }
    }

    public function getAdvancedFormInputs()
    {
        $inputs = array();
        $module = Module::getInstanceByName($this->name);
        $orderStatuses = $module->getOrderStatuses();

        $inputs[] = array(
            'type' => 'select',
            'label' => $module->l('Payment accepted and transaction completed', get_class($this)),
            'name' => 'paypal_os_accepted_two',
            'hint' => $module->l('You are currently using the Sale mode (the authorization and capture occur at the same time as the sale). So the payement is accepted instantly and the new order is created in the "Payment accepted" status. You can customize the status for orders with completed transactions. Ex : you can create an additional status "Payment accepted via PayPal" and set it as the default status.', get_class($this)),
            'desc' => $module->l('Default status : Payment accepted', get_class($this)),
            'options' => array(
                'query' => $orderStatuses,
                'id' => 'id',
                'name' => 'name'
            )
        );

        return $inputs;
    }

    public function getClientId()
    {
        if ($this->clientId !== null) {
            return $this->clientId;
        }

        if ($this->isSandbox()) {
            $clientId = Configuration::get('PAYPAL_SANDBOX_CLIENTID');
        } else {
            $clientId = Configuration::get('PAYPAL_LIVE_CLIENTID');
        }

        $this->clientId = $clientId;
        return $this->clientId;
    }

    public function getSecret()
    {
        if ($this->secret !== null) {
            return $this->secret;
        }

        if ($this->isSandbox()) {
            $secret = Configuration::get('PAYPAL_SANDBOX_SECRET');
        } else {
            $secret = Configuration::get('PAYPAL_LIVE_SECRET');
        }

        $this->secret = $secret;
        return $this->secret;
    }

    public function getIntent()
    {
        return 'CAPTURE';
    }

    public function getReturnUrl()
    {
        return Context::getContext()->link->getModuleLink($this->name, 'pppValidation', array(), true);
    }

    public function getCancelUrl()
    {
        return Context::getContext()->link->getPageLink('order', true);
    }

    public function getPaypalPartnerId()
    {
        return getenv('PLATEFORM') == 'PSREAD' ? 'PrestaShop_Cart_Ready_PPP' : 'PrestaShop_Cart_PPP';
    }

    public function getShortCut()
    {
        return $this->short_cut;
    }

    public function setShortCut($shortCut)
    {
        $this->short_cut = $shortCut;
        return $this;
    }
}
