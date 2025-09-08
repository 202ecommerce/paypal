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
use PaypalAddons\classes\API\Response\ResponseGetSellerStatus;
use PaypalAddons\classes\WhiteList\WhiteListService;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class MethodEC.
 *
 * @see https://developer.paypal.com/docs/classic/api/ NVP SOAP SDK
 * @see https://developer.paypal.com/docs/classic/api/nvpsoap-sdks/
 */
class MethodEC extends AbstractMethodPaypal
{
    /** @var bool pay with card without pp account */
    public $credit_card;

    /** @var bool shortcut payment from product or cart page */
    public $short_cut;

    protected $payment_method = 'PayPal';

    protected $transaction_detail;

    public $errors = [];

    /** payment Object IDl*/
    protected $paymentId;

    /** @var bool */
    protected $isSandbox;

    /** @var WhiteListService */
    protected $whiteListService;

    public function __construct()
    {
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

    public function setShortCut($shortCut)
    {
        $this->short_cut = (bool) $shortCut;

        return $this;
    }

    public function getShortCut()
    {
        return (bool) $this->short_cut;
    }

    /**
     * @see AbstractMethodPaypal::getConfig()
     */
    public function getConfig(PayPal $module)
    {
    }

    public function logOut($sandbox = null)
    {
        if ($sandbox == null) {
            $mode = $this->isSandbox() ? 'SANDBOX' : 'LIVE';
        } else {
            $mode = (int) $sandbox ? 'SANDBOX' : 'LIVE';
        }

        Configuration::updateValue('PAYPAL_EC_CLIENTID_' . $mode, '');
        Configuration::updateValue('PAYPAL_EC_SECRET_' . $mode, '');
        Configuration::updateValue('PAYPAL_CONNECTION_EC_CONFIGURED', 0);
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
            Configuration::updateValue('PAYPAL_EC_CLIENTID_SANDBOX', $params['clientId']);
            Configuration::updateValue('PAYPAL_EC_SECRET_SANDBOX', $params['secret']);
            Configuration::updateValue('PAYPAL_EC_MERCHANT_ID_SANDBOX', $params['merchantId']);
        } else {
            Configuration::updateValue('PAYPAL_EC_CLIENTID_LIVE', $params['clientId']);
            Configuration::updateValue('PAYPAL_EC_SECRET_LIVE', $params['secret']);
            Configuration::updateValue('PAYPAL_EC_MERCHANT_ID_LIVE', $params['merchantId']);
        }
    }

    /**
     * @return bool
     */
    public function useMobile()
    {
        if ((method_exists(Context::getContext(), 'getMobileDevice') && Context::getContext()->getMobileDevice())
            || Tools::getValue('ps_mobile_site')) {
            return true;
        }

        return false;
    }

    public function getDateTransaction()
    {
        $dateServer = new DateTime();
        $timeZonePayPal = new DateTimeZone('PST');
        $dateServer->setTimezone($timeZonePayPal);

        return $dateServer->format('Y-m-d H:i:s');
    }

    /**
     * @see AbstractMethodPaypal::confirmCapture()
     */
    public function confirmCapture($paypalOrder)
    {
        return $this->paypalApiManager->getCaptureAuthorizeRequest($paypalOrder)->execute();
    }

    /**
     * @see AbstractMethodPaypal::void()
     */
    public function void($orderPayPal)
    {
        $response = $this->paypalApiManager->getAuthorizationVoidRequest($orderPayPal)->execute();

        return $response;
    }

    /**
     * @return bool
     */
    public function isConfigured()
    {
        if (false == $this->whiteListService->isEligibleContext()) {
            return false;
        }

        if ($this->isCredentialsSetted() === false) {
            return false;
        }

        if ((bool) Configuration::get('PAYPAL_CONNECTION_EC_CONFIGURED')) {
            return true;
        }

        $this->checkCredentials();

        return (bool) Configuration::get('PAYPAL_CONNECTION_EC_CONFIGURED');
    }

    public function checkCredentials()
    {
        $response = $this->paypalApiManager->getAccessTokenRequest()->execute();

        if ($response->isSuccess()) {
            Configuration::updateValue('PAYPAL_CONNECTION_EC_CONFIGURED', 1);
        } else {
            $this->setConfig([
                'clientId' => '',
                'secret' => '',
                'merchantId' => '',
            ]);
            Configuration::updateValue('PAYPAL_CONNECTION_EC_CONFIGURED', 0);

            if ($response->getError()) {
                $this->errors[] = $response->getError()->getMessage();
            }
        }
    }

    public function getIntent()
    {
        return Configuration::get('PAYPAL_API_INTENT') == 'sale' ? self::SALE : self::AUTHORIZE;
    }

    public function getClientId($sandbox = null)
    {
        if ($sandbox === null) {
            $sandbox = $this->isSandbox();
        }

        if ($sandbox) {
            $clientId = Configuration::get('PAYPAL_EC_CLIENTID_SANDBOX');
        } else {
            $clientId = Configuration::get('PAYPAL_EC_CLIENTID_LIVE');
        }

        return $clientId;
    }

    public function getSecret($sandbox = null)
    {
        if ($sandbox === null) {
            $sandbox = $this->isSandbox();
        }

        if ($sandbox) {
            $secret = Configuration::get('PAYPAL_EC_SECRET_SANDBOX');
        } else {
            $secret = Configuration::get('PAYPAL_EC_SECRET_LIVE');
        }

        return (string) $secret;
    }

    public function getReturnUrl()
    {
        /* @phpstan-ignore-next-line */
        return Context::getContext()->link->getModuleLink($this->name, 'ecValidation', [], true);
    }

    public function getCancelUrl()
    {
        return Context::getContext()->link->getPageLink('order', true);
    }

    public function getPaypalPartnerId()
    {
        return (getenv('PLATEFORM') == 'PSREAD') ? 'PrestaShop_Cart_Ready_EC' : 'PRESTASHOP_Cart_SPB';
    }

    /** @return  string*/
    public function getLandingPage()
    {
        if ((int) $this->credit_card) {
            return 'BILLING';
        }

        return 'LOGIN';
    }

    public function getMerchantId()
    {
        if ($this->isSandbox()) {
            return Configuration::get('PAYPAL_EC_MERCHANT_ID_SANDBOX');
        } else {
            return Configuration::get('PAYPAL_EC_MERCHANT_ID_LIVE');
        }
    }

    public function getSellerStatus()
    {
        if (empty($this->getMerchantId())) {
            $response = new ResponseGetSellerStatus();
            $response->setSuccess(false);

            return $response;
        }

        return $this->paypalApiManager->getSellerStatusRequest()->execute();
    }
}
