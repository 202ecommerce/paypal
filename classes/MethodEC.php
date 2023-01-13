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
use PaypalAddons\classes\WhiteList\WhiteListService;

/**
 * Class MethodEC.
 *
 * @see https://developer.paypal.com/docs/classic/api/ NVP SOAP SDK
 * @see https://developer.paypal.com/docs/classic/api/nvpsoap-sdks/
 */
class MethodEC extends AbstractMethodPaypal
{
    const AUTHORIZE = 'AUTHORIZE';

    const SALE = 'CAPTURE';

    /** @var bool pay with card without pp account */
    public $credit_card;

    /** @var bool shortcut payment from product or cart page */
    public $short_cut;

    protected $payment_method = 'PayPal';

    protected $transaction_detail;

    public $errors = [];

    public $advancedFormParametres = [
        'paypal_os_accepted_two',
        'paypal_os_waiting_validation',
    ];

    /** payment Object IDl*/
    protected $paymentId;

    /** @var bool */
    protected $isSandbox;

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
            $mode = Configuration::get('PAYPAL_SANDBOX') ? 'SANDBOX' : 'LIVE';
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
        if ($this->isSandbox()) {
            Configuration::updateValue('PAYPAL_EC_CLIENTID_SANDBOX', $params['clientId']);
            Configuration::updateValue('PAYPAL_EC_SECRET_SANDBOX', $params['secret']);
        } else {
            Configuration::updateValue('PAYPAL_EC_CLIENTID_LIVE', $params['clientId']);
            Configuration::updateValue('PAYPAL_EC_SECRET_LIVE', $params['secret']);
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
        if ($this->whiteListService->isEnabled() && !$this->whiteListService->isEligibleContext()) {
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
            ]);
            Configuration::updateValue('PAYPAL_CONNECTION_EC_CONFIGURED', 0);

            if ($response->getError()) {
                $this->errors[] = $response->getError()->getMessage();
            }
        }
    }

    public function getTplVars()
    {
        $tplVars = [];
        $countryDefault = new Country((int) \Configuration::get('PS_COUNTRY_DEFAULT'), Context::getContext()->language->id);

        $tplVars['accountConfigured'] = $this->isConfigured();
        $tplVars['urlOnboarding'] = $this->getUrlOnboarding();
        $tplVars['country_iso'] = $countryDefault->iso_code;
        $tplVars['idShop'] = Context::getContext()->shop->id;
        $tplVars['mode'] = $this->isSandbox() ? 'SANDBOX' : 'LIVE';
        $tplVars['paypal_ec_clientid'] = $this->getClientId();
        $tplVars['paypal_ec_secret'] = $this->getSecret();
        $tplVars['paypalOnboardingLib'] = $this->isSandbox() ?
            'https://www.sandbox.paypal.com/webapps/merchantboarding/js/lib/lightbox/partner.js' :
            'https://www.paypal.com/webapps/merchantboarding/js/lib/lightbox/partner.js';

        return $tplVars;
    }

    public function getAdvancedFormInputs()
    {
        $inputs = [];
        $module = Module::getInstanceByName($this->name);
        $orderStatuses = $module->getOrderStatuses();

        if ($this->getIntent() == 'AUTHORIZE') {
            $inputs[] = [
                'type' => 'select',
                'label' => $module->l('Payment authorized and waiting for validation by admin', get_class($this)),
                'name' => 'paypal_os_waiting_validation',
                'hint' => $module->l('You are currently using the Authorize mode. It means that you separate the payment authorization from the capture of the authorized payment. By default the orders will be created in the "Waiting for PayPal payment" but you can customize it if needed.', get_class($this)),
                'desc' => $module->l('Default status : Waiting for PayPal payment', get_class($this)),
                'options' => [
                    'query' => $orderStatuses,
                    'id' => 'id',
                    'name' => 'name',
                ],
            ];
        } else {
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
        }

        return $inputs;
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
}
