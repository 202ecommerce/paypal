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

use PaypalAddons\classes\AbstractMethodPaypal;
use PaypalAddons\classes\API\PaypalApiManager;
use PaypalAddons\classes\WhiteList\WhiteListService;
use PaypalAddons\services\ServicePaypalVaulting;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class MethodPPP
 *
 * @see https://paypal.github.io/PayPal-PHP-SDK/ REST API sdk doc
 * @see https://developer.paypal.com/docs/api/payments/v1/ REST API references
 */
class MethodMB extends AbstractMethodPaypal
{
    /* @var string type of the payer tax */
    const BR_CPF = 'BR_CPF';

    /* @var string type of the payer tax */
    const BR_CNPJ = 'BR_CNPJ';

    protected $payment_method = 'PayPal';

    public $errors = [];

    /** payment Object IDl*/
    public $paymentId;

    /** @var string */
    protected $payerId;

    /** @var string hash of the remembered card ids */
    protected $rememeberedCards;

    /** @var bool shortcut payment from product or cart page */
    public $short_cut;

    protected $servicePaypalVaulting;

    /** @var WhiteListService */
    protected $whiteListService;

    public function __construct()
    {
        $this->servicePaypalVaulting = new ServicePaypalVaulting($this);
        $this->whiteListService = new WhiteListService();
        $this->initApiManager();
    }

    protected function initApiManager()
    {
        $this->paypalApiManager = new PaypalApiManager($this);

        return $this;
    }

    public function setSandbox($isSandbox)
    {
        parent::setSandbox($isSandbox);
        $this->initApiManager();
        $this->checkCredentials();

        return $this;
    }

    /**
     * @param array $values replace for tools::getValues()
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
            $mode = $this->isSandbox() ? 'SANDBOX' : 'LIVE';
        } else {
            $mode = (int) $sandbox ? 'SANDBOX' : 'LIVE';
        }

        Configuration::updateValue('PAYPAL_MB_' . $mode . '_CLIENTID', '');
        Configuration::updateValue('PAYPAL_MB_' . $mode . '_SECRET', '');
        Configuration::updateValue('PAYPAL_CONNECTION_MB_CONFIGURED', '');
    }

    /**
     * @see AbstractMethodPaypal::setConfig()
     */
    public function setConfig($params)
    {
        if (isset($params['isSandbox'])) {
            $isSandbox = $params['isSandbox'];
        } else {
            $isSandbox = $this->isSandbox();
        }

        if ($isSandbox) {
            Configuration::updateValue('PAYPAL_MB_SANDBOX_CLIENTID', $params['clientId']);
            Configuration::updateValue('PAYPAL_MB_SANDBOX_SECRET', $params['secret']);
            Configuration::updateValue('PAYPAL_MB_MERCHANT_ID_SANDBOX', $params['merchantId']);
        } else {
            Configuration::updateValue('PAYPAL_MB_LIVE_CLIENTID', $params['clientId']);
            Configuration::updateValue('PAYPAL_MB_LIVE_SECRET', $params['secret']);
            Configuration::updateValue('PAYPAL_MB_MERCHANT_ID_LIVE', $params['merchantId']);
        }
    }

    public function getConfig(PayPal $module)
    {
    }

    /**
     * @see AbstractMethodPaypal::validation()
     */
    public function validation()
    {
        $context = Context::getContext();
        $cart = $context->cart;
        /* @phpstan-ignore-next-line */
        $customer = new Customer($cart->id_customer);

        if (Validate::isLoadedObject($customer) && $this->getRememberedCards()) {
            $this->servicePaypalVaulting->saveRememberedCards($customer->id, $this->getRememberedCards());
        }

        parent::validation();
    }

    /**
     * @see AbstractMethodPaypal::confirmCapture()
     */
    public function confirmCapture($orderPayPal)
    {
        return $this->paypalApiManager->getCaptureAuthorizeRequest($orderPayPal)->execute();
    }

    /**
     * @see AbstractMethodPaypal::void()
     */
    public function void($orderPayPal)
    {
        return $this->paypalApiManager->getAuthorizationVoidRequest($orderPayPal)->execute();
    }

    /**
     * @return bool
     */
    public function isConfigured($mode = null)
    {
        if (false == $this->whiteListService->isEligibleContext()) {
            return false;
        }
        if ($this->isCredentialsSetted() === false) {
            return false;
        }
        if ((bool) Configuration::get('PAYPAL_CONNECTION_MB_CONFIGURED')) {
            return true;
        }

        $this->checkCredentials();

        return (bool) Configuration::get('PAYPAL_CONNECTION_MB_CONFIGURED');
    }

    public function checkCredentials()
    {
        $response = $this->paypalApiManager->getAccessTokenRequest()->execute();

        if ($response->isSuccess()) {
            Configuration::updateValue('PAYPAL_CONNECTION_MB_CONFIGURED', 1);
        } else {
            $this->setConfig([
                'clientId' => '',
                'secret' => '',
                'merchantId' => '',
            ]);
            Configuration::updateValue('PAYPAL_CONNECTION_MB_CONFIGURED', 0);

            if ($response->getError()) {
                $this->errors[] = $response->getError()->getMessage();
            }
        }
    }

    /**
     * Assign form data for Paypal Plus payment option
     *
     * @return void
     */
    public function assignJSvarsPaypalMB()
    {
        $context = Context::getContext();
        $module = Module::getInstanceByName($this->name);
        Media::addJsDef([
            /* @phpstan-ignore-next-line */
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
        $taxIdType = $this->getTaxIdType($taxId);

        if (empty($taxIdType) === false) {
            $taxInfo['tax_id'] = $taxId;
            $taxInfo['tax_id_type'] = $this->getTaxIdType($taxId);
        }

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
     * @param string $vatNumber
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
        /* @phpstan-ignore-next-line */
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
        if (Country::getIsoById((int) Configuration::get('PS_COUNTRY_DEFAULT')) == 'MX') {
            $bnCodeSuffix = 'Mexico';
        } else {
            $bnCodeSuffix = 'Brazil';
        }

        return (getenv('PLATEFORM') == 'PSREAD') ? 'PrestaShop_Cart_Ready_' . $bnCodeSuffix : 'PrestaShop_Cart_' . $bnCodeSuffix;
    }

    public function getIntent()
    {
        return Configuration::get('PAYPAL_API_INTENT') == 'sale' ? self::SALE : self::AUTHORIZE;
    }

    public function getMerchantId()
    {
        if ($this->isSandbox()) {
            return Configuration::get('PAYPAL_MB_MERCHANT_ID_SANDBOX');
        } else {
            return Configuration::get('PAYPAL_MB_MERCHANT_ID_LIVE');
        }
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
}
