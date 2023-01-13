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
use PaypalAddons\classes\API\PaypalApiManagerMB;
use PaypalAddons\classes\WhiteList\WhiteListService;
use PaypalAddons\services\ServicePaypalVaulting;

/**
 * Class MethodPPP
 *
 * @see https://paypal.github.io/PayPal-PHP-SDK/ REST API sdk doc
 * @see https://developer.paypal.com/docs/api/payments/v1/ REST API references
 */
class MethodMB extends AbstractMethodPaypal
{
    /* @var string type of the payer tax*/
    const BR_CPF = 'BR_CPF';

    /* @var string type of the payer tax*/
    const BR_CNPJ = 'BR_CNPJ';

    protected $payment_method = 'PayPal';

    public $errors = [];

    /** payment Object IDl*/
    public $paymentId;

    /** @var string */
    protected $payerId;

    /** @var string hash of the remembered card ids */
    protected $rememeberedCards;

    protected $servicePaypalVaulting;

    public $advancedFormParametres = [
        'paypal_os_waiting_validation',
        'paypal_os_accepted_two',
        'paypal_os_processing',
        'paypal_os_validation_error',
        'paypal_os_refunded_paypal',
    ];

    /** @var WhiteListService */
    protected $whiteListService;

    public function __construct()
    {
        $this->servicePaypalVaulting = new ServicePaypalVaulting();
        $this->paypalApiManager = new PaypalApiManagerMB($this);
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

        Configuration::updateValue('PAYPAL_MB_' . $mode . '_CLIENTID', '');
        Configuration::updateValue('PAYPAL_MB_' . $mode . '_SECRET', '');
        Configuration::updateValue('PAYPAL_MB_EXPERIENCE', '');
    }

    /**
     * @see AbstractMethodPaypal::setConfig()
     */
    public function setConfig($params)
    {
    }

    public function getConfig(Paypal $paypal)
    {
    }

    /**
     * @see AbstractMethodPaypal::validation()
     */
    public function validation()
    {
        $context = Context::getContext();
        $cart = $context->cart;
        $customer = new Customer($cart->id_customer);

        if (Validate::isLoadedObject($customer) && $this->getRememberedCards()) {
            $this->servicePaypalVaulting->createOrUpdatePaypalVaulting($customer->id, $this->getRememberedCards());
        }

        parent::validation();
    }

    public function getOrderStatus()
    {
        if ((int) Configuration::get('PAYPAL_CUSTOMIZE_ORDER_STATUS')) {
            $orderStatus = (int) Configuration::get('PAYPAL_OS_PROCESSING');
        } else {
            $orderStatus = (int) Configuration::get('PAYPAL_OS_WAITING');
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
     * @return bool
     */
    public function isConfigured($mode = null)
    {
        if ($this->whiteListService->isEnabled() && !$this->whiteListService->isEligibleContext()) {
            return false;
        }

        $isMbConfigured = (bool) Configuration::get('PAYPAL_MB_EXPERIENCE');

        // If a payment by PayPal account is enabled and the credentials for EC are not setted, so it should use MB credentials
        if ($isMbConfigured && (bool) Configuration::get('PAYPAL_MB_EC_ENABLED')) {
            $methodEC = self::load('EC');

            if ($methodEC->isCredentialsSetted() === false) {
                $methodEC->setConfig([
                    'clientId' => $this->getClientId(),
                    'secret' => $this->getSecret(),
                ]);
            }
        }

        return $isMbConfigured;
    }

    public function getTplVars()
    {
        if ($this->isSandbox()) {
            $tpl_vars = [
                'paypal_mb_sandbox_clientid' => Configuration::get('PAYPAL_MB_SANDBOX_CLIENTID'),
                'paypal_mb_sandbox_secret' => Configuration::get('PAYPAL_MB_SANDBOX_SECRET'),
                'paypal_ec_clientid' => Configuration::get('PAYPAL_EC_CLIENTID_SANDBOX'),
                'paypal_ec_secret' => Configuration::get('PAYPAL_EC_SECRET_SANDBOX'),
                'mode' => 'SANDBOX',
            ];
        } else {
            $tpl_vars = [
                'paypal_mb_live_clientid' => Configuration::get('PAYPAL_MB_LIVE_CLIENTID'),
                'paypal_mb_live_secret' => Configuration::get('PAYPAL_MB_LIVE_SECRET'),
                'paypal_ec_clientid' => Configuration::get('PAYPAL_EC_CLIENTID_LIVE'),
                'paypal_ec_secret' => Configuration::get('PAYPAL_EC_SECRET_LIVE'),
                'mode' => 'LIVE',
            ];
        }

        return $tpl_vars;
    }

    public function checkCredentials()
    {
        $response = $this->paypalApiManager->getAccessTokenRequest()->execute();

        if ($response->isSuccess()) {
            Configuration::updateValue('PAYPAL_MB_EXPERIENCE', $response->getIdProfileExperience());
        } else {
            Configuration::updateValue('PAYPAL_MB_EXPERIENCE', '');

            if ($response->getError()) {
                $this->errors[] = $response->getError()->getMessage();
            }
        }
    }

    /**
     * Assign form data for Paypal Plus payment option
     *
     * @return bool
     */
    public function assignJSvarsPaypalMB()
    {
        $context = Context::getContext();
        $module = Module::getInstanceByName($this->name);
        Media::addJsDef([
            'ajaxPatch' => $context->link->getModuleLink('paypal', 'mbValidation', [], true),
            'EMPTY_TAX_ID' => $module->l('For processing you payment via PayPal it is required to add a VAT number to your address. Please fill it and complete your payment.', get_class($this)),
            'INVALID_PAYER_TAX_ID' => $module->l('For processing you payment via PayPal it is required to add a valid Tax ID to your address. Please verify if your Tax ID is correct, change it if needed and complete your payment.', get_class($this)),
            'PAYMENT_SUCCESS' => $module->l('Payment successful! You will be redirected to the payment confirmation page in a couple of seconds.', get_class($this)),
        ]);
    }

    protected function getPayerInfo()
    {
        $payerInfo = [];
        $customer = Context::getContext()->customer;
        $taxInfo = $this->getPayerTaxInfo();
        $payerInfo['email'] = $customer->email;
        $payerInfo['first_name'] = $customer->firstname;
        $payerInfo['last_name'] = $customer->lastname;

        if (empty($taxInfo)) {
            $payerInfo['tax_id'] = '';
            $payerInfo['tax_id_type'] = '';
        } else {
            $payerInfo['tax_id'] = $taxInfo['tax_id'];
            $payerInfo['tax_id_type'] = $taxInfo['tax_id_type'];
        }

        return $payerInfo;
    }

    public function getPayerTaxInfo()
    {
        $taxInfo = [];

        if (Validate::isLoadedObject(Context::getContext()->customer) == false) {
            return $taxInfo;
        }

        if (Validate::isLoadedObject(Context::getContext()->cart) == false) {
            return $taxInfo;
        }

        if ((int) Context::getContext()->cart->id_address_delivery == 0) {
            return $taxInfo;
        }

        $addressCustomer = new Address(Context::getContext()->cart->id_address_delivery);
        $countryCustomer = new Country($addressCustomer->id_country);

        if ($countryCustomer->iso_code != 'BR') {
            return $taxInfo;
        }

        $taxId = str_replace(['.', '-', '/'], '', $addressCustomer->vat_number);
        $taxInfo['tax_id'] = $taxId;
        $taxInfo['tax_id_type'] = $this->getTaxIdType($taxId);

        return $taxInfo;
    }

    public function getPaymentInfo()
    {
        $context = Context::getContext();

        try {
            $response = $this->init();
            $context->cookie->__set('paypal_plus_mb_payment', $this->paymentId);
        } catch (Throwable $e) {
            return false;
        } catch (Exception $e) {
            return false;
        }

        $addressCustomer = new Address(Context::getContext()->cart->id_address_delivery);
        $countryCustomer = new Country($addressCustomer->id_country);

        $paymentInfo = [
            'approvalUrlPPP' => $response->getApproveLink(),
            'paymentId' => $response->getPaymentId(),
            'paypalMode' => $this->isSandbox() ? 'sandbox' : 'live',
            'payerInfo' => $this->getPayerInfo(),
            'language' => str_replace('-', '_', $context->language->locale),
            'country' => $countryCustomer->iso_code,
            'disallowRememberedCards' => (bool) Configuration::get('PAYPAL_VAULTING') == false,
            'rememberedCards' => $this->servicePaypalVaulting->getRememberedCardsByIdCustomer($context->customer->id),
            'merchantInstallmentSelectionOptional' => (int) Configuration::get('PAYPAL_MERCHANT_INSTALLMENT'),
        ];

        return $paymentInfo;
    }

    public function setPaymentId($paymentId)
    {
        if (is_string($paymentId)) {
            $this->paymentId = $paymentId;
        }

        return $this;
    }

    public function getPaymentId()
    {
        return (string) $this->paymentId;
    }

    public function setPayerId($payerId)
    {
        if (is_string($payerId)) {
            $this->payerId = $payerId;
        }

        return $this;
    }

    public function getPayerId()
    {
        return (string) $this->payerId;
    }

    public function setRememberedCards($rememberedCards)
    {
        if (is_string($rememberedCards)) {
            $this->rememeberedCards = $rememberedCards;
        }

        return $this;
    }

    public function getRememberedCards()
    {
        return (string) $this->rememeberedCards;
    }

    /**
     * @param $vatNumber string
     *
     * @return string
     */
    public function getTaxIdType($vatNumber)
    {
        if (is_string($vatNumber) == false || empty($vatNumber)) {
            return '';
        }

        $vatNumberArray = str_split($vatNumber);

        if (count($vatNumberArray) != 11) {
            return self::BR_CNPJ;
        }

        foreach ($vatNumberArray as $symbol) {
            if (is_numeric($symbol) == false) {
                return self::BR_CNPJ;
            }
        }

        return self::BR_CPF;
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
            'hint' => $module->l('You are currently using the Authorize mode. It means that you separate the payment authorization from the capture of the authorized payment. For capturing the authorized payement you have to change the order status to "payment accepted" (or to a custom status with the same meaning). Here you can choose a custom order status for accepting the order and validating transaction in Authorize mode.', get_class($this)),
            'desc' => $module->l('Default status : Payment accepted', get_class($this)),
            'options' => [
                'query' => $orderStatuses,
                'id' => 'id',
                'name' => 'name',
            ],
        ];

        if ($this->getIntent() == 'AUTHORIZE') {
            $inputs[] = [
                'type' => 'select',
                'label' => $module->l('Payment authorized, waiting for validation by admin (paid via PayPal express checkout)', get_class($this)),
                'name' => 'paypal_os_waiting_validation',
                'hint' => $module->l('You are currently using the Authorize mode. It means that you separate the payment authorization from the capture of the authorized payment. By default the orders will be created in the "Waiting for PayPal payment" but you can customize it if needed.', get_class($this)),
                'desc' => $module->l('Default status : Waiting for PayPal payment', get_class($this)),
                'options' => [
                    'query' => $orderStatuses,
                    'id' => 'id',
                    'name' => 'name',
                ],
            ];
        }

        $inputs[] = [
            'type' => 'select',
            'label' => $module->l('Payment processing (only for the payments by card)', get_class($this)),
            'name' => 'paypal_os_processing',
            'hint' => $module->l('The transaction paid by card can be in the pending status. If the payment is processing the order will be created in the temporary status.', get_class($this)),
            'desc' => $module->l('Default status : Waiting for PayPal payment', get_class($this)),
            'options' => [
                'query' => $orderStatuses,
                'id' => 'id',
                'name' => 'name',
            ],
        ];

        $inputs[] = [
            'type' => 'select',
            'label' => $module->l('Payment validation error or transaction rejected (only for payments by card)', get_class($this)),
            'name' => 'paypal_os_validation_error',
            'hint' => $module->l('For the rejected transactions the "Canceled" status is applied automatically. You can modify it and to set your status instead.', get_class($this)),
            'desc' => $module->l('Default status : Canceled', get_class($this)),
            'options' => [
                'query' => $orderStatuses,
                'id' => 'id',
                'name' => 'name',
            ],
        ];

        $inputs[] = [
            'type' => 'select',
            'label' => $module->l('Payment refunded via PayPal merchant account (only for payments by card)', get_class($this)),
            'name' => 'paypal_os_refunded_paypal',
            'hint' => $module->l('If the transaction was refunded via PayPal interface the corresponding order will pass to the "Refunded" status automatically. You can modify it and to set your status instead.', get_class($this)),
            'desc' => $module->l('Default status : Refunded', get_class($this)),
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
            $clientId = Configuration::get('PAYPAL_MB_SANDBOX_CLIENTID');
        } else {
            $clientId = Configuration::get('PAYPAL_MB_LIVE_CLIENTID');
        }

        return $clientId;
    }

    public function getSecret($sandbox = null)
    {
        if ($sandbox === null) {
            $sandbox = $this->isSandbox();
        }

        if ($sandbox) {
            $secret = Configuration::get('PAYPAL_MB_SANDBOX_SECRET');
        } else {
            $secret = Configuration::get('PAYPAL_MB_LIVE_SECRET');
        }

        return $secret;
    }

    /**
     * @return string
     */
    public function getReturnUrl()
    {
        return (string) Context::getContext()->link->getModuleLink($this->name, 'mbValidation', [], true);
    }

    /**
     * @return string
     */
    public function getCancelUrl()
    {
        return (string) Context::getContext()->link->getPageLink('order', true);
    }

    /**
     * @return string
     */
    public function getIpnUrl()
    {
        return (string) Context::getContext()->link->getModuleLink($this->name, 'ipn');
    }

    /**
     * @return string
     */
    public function getIdProfileExperience()
    {
        return (string) Configuration::get('PAYPAL_MB_EXPERIENCE');
    }

    public function getPaypalPartnerId()
    {
        if (Country::getIsoById(Configuration::get('PS_COUNTRY_DEFAULT')) == 'MX') {
            $bnCodeSuffix = 'Mexico';
        } else {
            $bnCodeSuffix = 'Brazil';
        }

        return (getenv('PLATEFORM') == 'PSREAD') ? 'PrestaShop_Cart_Ready_' . $bnCodeSuffix : 'PrestaShop_Cart_' . $bnCodeSuffix;
    }

    public function getIntent()
    {
        return Configuration::get('PAYPAL_API_INTENT') == 'sale' ? 'CAPTURE' : 'AUTHORIZE';
    }
}
