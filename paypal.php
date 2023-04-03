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
if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_ . 'paypal/config_dev.php';
include_once _PS_MODULE_DIR_ . 'paypal/vendor/autoload.php';

use PaypalAddons\classes\AbstractMethodPaypal;
use PaypalAddons\classes\ACDC\AcdcFunctionality;
use PaypalAddons\classes\ACDC\AcdcPaymentMethod;
use PaypalAddons\classes\APM\ApmCollection;
use PaypalAddons\classes\APM\ApmFunctionality;
use PaypalAddons\classes\Constants\APM;
use PaypalAddons\classes\Constants\PaypalConfigurations;
use PaypalAddons\classes\Constants\WebHookConf;
use PaypalAddons\classes\InstallmentBanner\BannerManager;
use PaypalAddons\classes\InstallmentBanner\BNPL\BnplAvailabilityManager;
use PaypalAddons\classes\InstallmentBanner\BNPL\BNPLCart;
use PaypalAddons\classes\InstallmentBanner\BNPL\BNPLDummy;
use PaypalAddons\classes\InstallmentBanner\BNPL\BNPLOption;
use PaypalAddons\classes\InstallmentBanner\BNPL\BNPLPaymentStep;
use PaypalAddons\classes\InstallmentBanner\BNPL\BNPLProduct;
use PaypalAddons\classes\InstallmentBanner\BNPL\BNPLSignup;
use PaypalAddons\classes\InstallmentBanner\ConfigurationMap;
use PaypalAddons\classes\InstallmentBanner\ConfigurationMap as InstallmentConfiguration;
use PaypalAddons\classes\PaypalPaymentMode;
use PaypalAddons\classes\PUI\FraudNetForm;
use PaypalAddons\classes\PUI\FraudSessionId;
use PaypalAddons\classes\PUI\PuiFunctionality;
use PaypalAddons\classes\SEPA\SepaButton;
use PaypalAddons\classes\Shortcut\ShortcutConfiguration;
use PaypalAddons\classes\Shortcut\ShortcutPaymentStep;
use PaypalAddons\classes\Shortcut\ShortcutSignup;
use PaypalAddons\classes\Venmo\VenmoButton;
use PaypalAddons\classes\Venmo\VenmoFunctionality;
use PaypalAddons\classes\Webhook\WebhookOption;
use PaypalAddons\classes\Widget\DummyWidget;
use PaypalAddons\classes\Widget\InstallmentWidget;
use PaypalAddons\classes\Widget\ShortcutWidget;
use PaypalAddons\services\PaymentRefundAmount;
use PaypalAddons\services\PaypalContext;
use PaypalAddons\services\ServicePaypalOrder;
use PaypalAddons\services\StatusMapping;
use PaypalAddons\services\WebhookService;
use PaypalPPBTlib\Extensions\AbstractModuleExtension;
use PaypalPPBTlib\Extensions\Diagnostic\DiagnosticExtension;
use PaypalPPBTlib\Extensions\ProcessLogger\ProcessLoggerExtension;
use PaypalPPBTlib\Extensions\ProcessLogger\ProcessLoggerHandler;
use PaypalPPBTlib\Install\ModuleInstaller;
use PrestaShop\PrestaShop\Core\Module\WidgetInterface;
use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

define('BT_CARD_PAYMENT', 'card-braintree');
define('BT_PAYPAL_PAYMENT', 'paypal-braintree');
define('PAYPAL_PAYMENT_CUSTOMER_CURRENCY', -1);
// Method Alias :
// EC = express checkout
// ECS = express checkout sortcut
// BT = Braintree
// PPP = PayPal Plus

class PayPal extends \PaymentModule implements WidgetInterface
{
    const PAYPAL_PARTNER_CLIENT_ID_LIVE = 'ATgR8ZE5M_Jd7F_XMMQDqMfFFgr7hJHFw8yKfklWU4TwzReENgydr5I042YfS1nRTDey7C1NbuFfKo_o';

    const PAYPAL_PARTNER_ID_LIVE = 'B3PVCXSW2J8JN';

    const PAYPAL_PARTNER_CLIENT_ID_SANDBOX = 'AVJ8YvTxw5Clf5CyJXIX6mnSSNgpzFFRaZh0KekLIMVe2vlkrWDMgaOTbvNds1U2bXVcjX4JGaP_jDM1';

    const PAYPAL_PARTNER_ID_SANDBOX = 'J7Q7R6V9MQZUG';

    const NEED_INSTALL_MODELS = 'PAYPAL_NEED_INSTALL_MODELS';

    const NEED_INSTALL_EXTENSIONS = 'PAYPAL_NEED_INSTALL_EXTENSIONS';

    public static $dev = true;
    public $express_checkout;
    public $message;
    public $amount_paid_paypal;
    public $module_link;
    public $errors;
    public $currencyMB = ['USD', 'MXN', 'EUR', 'BRL'];
    public $paypal_method;
    public $countriesApiCartUnavailable = ['FR', 'ES', 'IT', 'GB', 'PL', 'BE', 'NL', 'LU', 'US', 'GR', 'DK', 'CZ', 'PT', 'FI', 'SE', 'NO', 'SK', 'CY', 'EE', 'LV', 'LT', 'MT', 'SI'];

    /** @var array matrix of state iso codes between paypal and prestashop */
    public static $state_iso_code_matrix = [
        'MX' => [
            'AGS' => 'AGS',
            'BCN' => 'BC',
            'BCS' => 'BCS',
            'CAM' => 'CAMP',
            'CHP' => 'CHIS',
            'CHH' => 'CHIH',
            'COA' => 'COAH',
            'COL' => 'COL',
            'DIF' => 'DF',
            'DUR' => 'DGO',
            'GUA' => 'GTO',
            'GRO' => 'GRO',
            'HID' => 'HGO',
            'JAL' => 'JAL',
            'MEX' => 'MEX',
            'MIC' => 'MICH',
            'MOR' => 'MOR',
            'NAY' => 'NAY',
            'NLE' => 'NL',
            'OAX' => 'OAX',
            'PUE' => 'PUE',
            'QUE' => 'QRO',
            'ROO' => 'Q ROO',
            'SLP' => 'SLP',
            'SIN' => 'SIN',
            'SON' => 'SON',
            'TAB' => 'TAB',
            'TAM' => 'TAMPS',
            'TLA' => 'TLAX',
            'VER' => 'VER',
            'YUC' => 'YUC',
            'ZAC' => 'ZAC',
        ],
        'JP' => [
            'Aichi' => 'Aichi-KEN',
            'Akita' => 'Akita-KEN',
            'Aomori' => 'Aomori-KEN',
            'Chiba' => 'Chiba-KEN',
            'Ehime' => 'Ehime-KEN',
            'Fukui' => 'Fukui-KEN',
            'Fukuoka' => 'Fukuoka-KEN',
            'Fukushima' => 'Fukushima-KEN',
            'Gifu' => 'Gifu-KEN',
            'Gunma' => 'Gunma-KEN',
            'Hiroshima' => 'Hiroshima-KEN',
            'Hokkaido' => 'Hokkaido-KEN',
            'Hyogo' => 'Hyogo-KEN',
            'Ibaraki' => 'Ibaraki-KEN',
            'Ishikawa' => 'Ishikawa-KEN',
            'Iwate' => 'Iwate-KEN',
            'Kagawa' => 'Kagawa-KEN',
            'Kagoshima' => 'Kagoshima-KEN',
            'Kanagawa' => 'Kanagawa-KEN',
            'Kochi' => 'Kochi-KEN',
            'Kumamoto' => 'Kumamoto-KEN',
            'Kyoto' => 'Kyoto-KEN',
            'Mie' => 'Mie-KEN',
            'Miyagi' => 'Miyagi-KEN',
            'Miyazaki' => 'Miyazaki-KEN',
            'Nagano' => 'Nagano-KEN',
            'Nagasaki' => 'Nagasaki-KEN',
            'Nara' => 'Nara-KEN',
            'Niigata' => 'Niigata-KEN',
            'Oita' => 'Oita-KEN',
            'Okayama' => 'Okayama-KEN',
            'Okinawa' => 'Okinawa-KEN',
            'Osaka' => 'Osaka-KEN',
            'Saga' => 'Saga-KEN',
            'Saitama' => 'Saitama-KEN',
            'Shiga' => 'Shiga-KEN',
            'Shimane' => 'Shimane-KEN',
            'Shizuoka' => 'Shizuoka-KEN',
            'Tochigi' => 'Tochigi-KEN',
            'Tokushima' => 'Tokushima-KEN',
            'Tokyo' => 'Tokyo-KEN',
            'Tottori' => 'Tottori-KEN',
            'Toyama' => 'Toyama-KEN',
            'Wakayama' => 'Wakayama-KEN',
            'Yamagata' => 'Yamagata-KEN',
            'Yamaguchi' => 'Yamaguchi-KEN',
            'Yamanashi' => 'Yamanashi-KEN',
        ],
    ];

    /**
     * List of objectModel used in this Module
     *
     * @var array
     */
    public $objectModels = [
        'PaypalCapture',
        'PaypalOrder',
        'PaypalVaulting',
        'PaypalIpn',
        'PaypalWebhook',
    ];

    /**
     * List of ppbtlib extentions
     */
    public $extensions = [
        ProcessLoggerExtension::class,
        DiagnosticExtension::class,
    ];

    /**
     * List of hooks used in this Module
     */
    public $hooks = [
        'paymentOptions',
        'displayOrderConfirmation',
        'displayAdminOrder',
        'actionOrderStatusPostUpdate',
        'actionOrderStatusUpdate',
        'displayHeader',
        'displayFooterProduct',
        'actionBeforeCartUpdateQty',
        'displayReassurance',
        'displayInvoiceLegalFreeText',
        'displayShoppingCartFooter',
        'actionOrderSlipAdd',
        'displayAdminOrderTabOrder',
        'displayAdminOrderContentOrder',
        'displayAdminCartsView',
        'displayAdminOrderTop',
        'displayAdminOrderTabLink',
        'displayAdminOrderTabContent',
        'displayOrderPreview',
        'displayNavFullWidth',
        'actionLocalizationPageSave',
        'actionAdminOrdersTrackingNumberUpdate',
        ShortcutConfiguration::HOOK_REASSURANCE,
        ShortcutConfiguration::HOOK_AFTER_PRODUCT_ADDITIONAL_INFO,
        ShortcutConfiguration::HOOK_AFTER_PRODUCT_THUMBS,
        ShortcutConfiguration::HOOK_EXPRESS_CHECKOUT,
        ShortcutConfiguration::HOOK_FOOTER_PRODUCT,
        ShortcutConfiguration::HOOK_PRODUCT_ACTIONS,
        ShortcutConfiguration::HOOK_SHOPPING_CART_FOOTER,
        ShortcutConfiguration::HOOK_PERSONAL_INFORMATION_TOP,
    ];

    /**
     * @var array
     */
    public $moduleConfigs = [];

    /**
     * List of admin tabs used in this Module
     */
    public $moduleAdminControllers = [
        [
            'name' => [
                'en' => 'PayPal Official',
                'fr' => 'PayPal Officiel',
            ],
            'class_name' => 'AdminParentPaypalConfiguration',
            'parent_class_name' => 'SELL',
            'visible' => false,
            'icon' => 'payment',
        ],
        [
            'name' => [
                'en' => 'Configuration',
                'fr' => 'Configuration',
            ],
            'class_name' => 'AdminPaypalConfiguration',
            'parent_class_name' => 'AdminParentPaypalConfiguration',
            'visible' => false,
        ],
        [
            'name' => [
                'en' => 'Setup',
                'fr' => 'Paramètres',
                'pt' => 'Definições',
                'pl' => 'Ustawienia',
                'nl' => 'Instellingen',
                'it' => 'Impostazioni',
                'es' => 'Configuración',
                'de' => 'Einstellungen',
                'mx' => 'Configuración',
                'br' => 'Definições',
            ],
            'class_name' => 'AdminPayPalSetup',
            'parent_class_name' => 'AdminPayPalConfiguration',
            'visible' => true,
        ],
        [
            'name' => [
                'en' => 'Experience',
                'fr' => 'Expérience',
                'de' => 'User Experience',
                'pt' => 'Experiência',
                'pl' => 'Doświadczenie',
                'nl' => 'Ervaring',
                'it' => 'Percorso Cliente',
                'es' => 'Experiencia',
                'mx' => 'Experiencia',
                'br' => 'Experiência',
            ],
            'class_name' => 'AdminPayPalCustomizeCheckout',
            'parent_class_name' => 'AdminPayPalConfiguration',
            'visible' => true,
        ],
        [
            'name' => [
                'en' => 'Pay in X times',
                'fr' => 'Paiement en X fois',
                'de' => 'Pay in X times',
            ],
            'class_name' => 'AdminPayPalInstallment',
            'parent_class_name' => 'AdminPayPalConfiguration',
            'visible' => true,
        ],
        [
            'name' => [
                'en' => 'Help',
                'fr' => 'Aide',
                'pt' => 'Ajuda',
                'pl' => 'Pomoc',
                'nl' => 'Hulp',
                'it' => 'Aiuto',
                'es' => 'Ayuda',
                'de' => 'Hilfe',
                'mx' => 'Ayuda',
                'br' => 'Ajuda',
            ],
            'class_name' => 'AdminPayPalHelp',
            'parent_class_name' => 'AdminPayPalConfiguration',
            'visible' => true,
        ],
        [
            'name' => [
                'en' => 'Logs',
                'fr' => 'Logs',
                'de' => 'Logs',
                'pt' => 'Logs',
                'pl' => 'Dzienniki',
                'nl' => 'Logs',
                'it' => 'Logs',
                'es' => 'Logs',
                'mx' => 'Logs',
                'br' => 'Logs',
            ],
            'class_name' => 'AdminPayPalLogs',
            'parent_class_name' => 'AdminPayPalConfiguration',
            'visible' => true,
        ],
        [
            'name' => [
                'en' => 'Get Credentials',
            ],
            'class_name' => 'AdminPaypalGetCredentials',
            'parent_class_name' => 'AdminParentPaypalConfiguration',
            'visible' => false,
        ],
        [
            'name' => [
                'en' => 'PUI listener',
            ],
            'class_name' => 'AdminPayPalPUIListener',
            'parent_class_name' => 'AdminParentPaypalConfiguration',
            'visible' => false,
        ],
    ];

    public function __construct()
    {
        $this->name = 'paypal';
        $this->tab = 'payments_gateways';
        $this->version = '@version@';
        $this->author = '202 ecommerce';
        $this->module_key = '336225a5988ad434b782f2d868d7bfcd';
        $this->ps_versions_compliancy = ['min' => '1.7', 'max' => _PS_VERSION_];
        $this->controllers = ['payment', 'validation'];
        $this->bootstrap = true;

        $this->currencies = true;
        $this->currencies_mode = 'radio';

        parent::__construct();

        require_once realpath(dirname(__FILE__) . '/smarty/plugins') . '/modifier.paypalreplace.php';
        $this->displayName = $this->l('PayPal');
        $this->description = $this->l('Allow your customers to pay with PayPal - the safest, quickest and easiest way to pay online.');
        $this->confirmUninstall = $this->l('Are you sure you want to delete your details?');
        $this->express_checkout = $this->l('PayPal Express Checkout ');

        $this->errors = '';
        $countryDefault = new \Country((int) \Configuration::get('PS_COUNTRY_DEFAULT'), $this->context->language->id);

        switch ($countryDefault->iso_code) {
            case 'DE':
                $this->paypal_method = 'PPP';
                break;
            case 'BR':
                $this->paypal_method = 'MB';
                break;
            case 'MX':
                $this->paypal_method = 'MB';
                break;
            default:
                $this->paypal_method = 'EC';
        }

        $this->moduleConfigs = [
            'PAYPAL_MERCHANT_ID_SANDBOX' => '',
            'PAYPAL_MERCHANT_ID_LIVE' => '',
            'PAYPAL_USERNAME_SANDBOX' => '',
            'PAYPAL_PSWD_SANDBOX' => '',
            'PAYPAL_SIGNATURE_SANDBOX' => '',
            'PAYPAL_SANDBOX_ACCESS' => 0,
            'PAYPAL_USERNAME_LIVE' => '',
            'PAYPAL_PSWD_LIVE' => '',
            'PAYPAL_SIGNATURE_LIVE' => '',
            'PAYPAL_LIVE_ACCESS' => 0,
            'PAYPAL_SANDBOX' => 0,
            'PAYPAL_API_INTENT' => 'sale',
            'PAYPAL_API_ADVANTAGES' => 1,
            'PAYPAL_API_CARD' => 1,
            'PAYPAL_METHOD' => '',
            'PAYPAL_EXPRESS_CHECKOUT_SHORTCUT' => 0,
            'PAYPAL_EXPRESS_CHECKOUT_SHORTCUT_CART' => 1,
            'PAYPAL_CRON_TIME' => '',
            'PAYPAL_BY_BRAINTREE' => 0,
            'PAYPAL_EXPRESS_CHECKOUT_IN_CONTEXT' => 1,
            'PAYPAL_VAULTING' => 0,
            'PAYPAL_REQUIREMENTS' => 0,
            'PAYPAL_MB_EC_ENABLED' => 1,
            'PAYPAL_CUSTOMIZE_ORDER_STATUS' => 0,
            PaypalConfigurations::OS_REFUNDED => (int) Configuration::get('PS_OS_REFUND'),
            PaypalConfigurations::OS_CANCELED => (int) Configuration::get('PS_OS_CANCELED'),
            PaypalConfigurations::OS_ACCEPTED => (int) Configuration::get('PS_OS_PAYMENT'),
            PaypalConfigurations::OS_CAPTURE_CANCELED => (int) Configuration::get('PS_OS_CANCELED'),
            PaypalConfigurations::OS_ACCEPTED_TWO => (int) Configuration::get('PS_OS_PAYMENT'),
            PaypalConfigurations::OS_WAITING_VALIDATION => (int) Configuration::get('PAYPAL_OS_WAITING'),
            PaypalConfigurations::OS_PROCESSING => (int) Configuration::get('PAYPAL_OS_WAITING'),
            PaypalConfigurations::OS_VALIDATION_ERROR => (int) Configuration::get('PS_OS_CANCELED'),
            PaypalConfigurations::OS_REFUNDED_PAYPAL => (int) Configuration::get('PS_OS_REFUND'),
            \PaypalAddons\classes\InstallmentBanner\ConfigurationMap::ENABLE_BNPL => 1,
            \PaypalAddons\classes\InstallmentBanner\ConfigurationMap::BNPL_CART_PAGE => 1,
            \PaypalAddons\classes\InstallmentBanner\ConfigurationMap::BNPL_PAYMENT_STEP_PAGE => 1,
            'PAYPAL_NOT_SHOW_PS_CHECKOUT' => json_encode([$this->version, 0]),
            WebHookConf::ENABLE => 1,
        ];

        if (version_compare(_PS_VERSION_, '1.7.6', '<')) {
            $this->moduleConfigs[ShortcutConfiguration::PRODUCT_PAGE_HOOK] = ShortcutConfiguration::HOOK_REASSURANCE;
        } else {
            $this->moduleConfigs[ShortcutConfiguration::PRODUCT_PAGE_HOOK] = ShortcutConfiguration::HOOK_PRODUCT_ACTIONS;
        }

        $this->moduleConfigs[ShortcutConfiguration::CART_PAGE_HOOK] = ShortcutConfiguration::HOOK_EXPRESS_CHECKOUT;

        foreach ($this->extensions as $extensionName) {
            if (false === class_exists($extensionName)) {
                continue;
            }

            $extension = new $extensionName($this);
            $extension->initExtension();
            $this->hooks = array_merge($this->hooks, $extension->hooks);
        }
    }

    public function install()
    {
        $installer = new ModuleInstaller($this);

        $isPhpVersionCompliant = false;
        try {
            $isPhpVersionCompliant = $installer->checkPhpVersion();
        } catch (\Exception $e) {
            $this->_errors[] = Tools::displayError($e->getMessage());
        }

        if (($isPhpVersionCompliant && parent::install() && $installer->install()) == false) {
            return false;
        }
        // Registration order status
        if (!$this->installOrderState()) {
            return false;
        }

        $this->registerHooks();
        $this->moduleConfigs['PAYPAL_OS_WAITING_VALIDATION'] = (int) Configuration::get('PAYPAL_OS_WAITING');
        $this->moduleConfigs['PAYPAL_OS_PROCESSING'] = (int) Configuration::get('PAYPAL_OS_WAITING');
        $shops = Shop::getShops();

        foreach ($this->moduleConfigs as $key => $value) {
            if (Shop::isFeatureActive()) {
                foreach ($shops as $shop) {
                    if (!Configuration::updateValue($key, $value, false, null, (int) $shop['id_shop'])) {
                        return false;
                    }
                }
            } else {
                if (!Configuration::updateValue($key, $value)) {
                    return false;
                }
            }
        }

        if (Shop::isFeatureActive()) {
            $shops = Shop::getShops();
            foreach ($shops as $shop) {
                Configuration::updateValue('PAYPAL_CRON_TIME', date('Y-m-d H:m:s'), false, null, (int) $shop['id_shop']);
                Configuration::updateValue('PAYPAL_PREVIOUS_VERSION', $this->version, false, null, (int) $shop['id_shop']);
            }
        } else {
            Configuration::updateValue('PAYPAL_CRON_TIME', date('Y-m-d H:m:s'));
            Configuration::updateValue('PAYPAL_PREVIOUS_VERSION', $this->version);
        }

        $this->hookActionLocalizationPageSave([]);

        return true;
    }

    /**
     * Set default currency restriction to "customer currency"
     *
     * @return bool
     */
    public function updateRadioCurrencyRestrictionsForModule()
    {
        $shops = Shop::getShops(true, null, true);
        foreach ($shops as $s) {
            if (!Db::getInstance()->execute('UPDATE `' . _DB_PREFIX_ . 'module_currency` SET `id_currency` = -1
                WHERE `id_shop` = "' . (int) $s . '" AND `id_module` = ' . (int) $this->id)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Create order state
     *
     * @return bool
     */
    public function installOrderState()
    {
        if (!Configuration::get('PAYPAL_OS_WAITING')
            || !Validate::isLoadedObject(new OrderState(Configuration::get('PAYPAL_OS_WAITING')))) {
            $order_state = new OrderState();
            $order_state->name = [];
            foreach (Language::getLanguages() as $language) {
                if (Tools::strtolower($language['iso_code']) == 'fr') {
                    $order_state->name[$language['id_lang']] = 'En attente de paiement PayPal';
                } else {
                    $order_state->name[$language['id_lang']] = 'Awaiting for PayPal payment';
                }
            }
            $order_state->send_email = false;
            $order_state->color = '#4169E1';
            $order_state->hidden = false;
            $order_state->delivery = false;
            $order_state->logable = false;
            $order_state->invoice = false;
            $order_state->module_name = $this->name;
            if ($order_state->add()) {
                $source = _PS_MODULE_DIR_ . 'paypal/views/img/os_paypal.png';
                $destination = _PS_ROOT_DIR_ . '/img/os/' . (int) $order_state->id . '.gif';
                copy($source, $destination);
            }

            if (Shop::isFeatureActive()) {
                $shops = Shop::getShops();
                foreach ($shops as $shop) {
                    Configuration::updateValue('PAYPAL_OS_WAITING', (int) $order_state->id, false, null, (int) $shop['id_shop']);
                }
            } else {
                Configuration::updateValue('PAYPAL_OS_WAITING', (int) $order_state->id);
            }
        }

        return true;
    }

    public function uninstall()
    {
        $installer = new ModuleInstaller($this);

        foreach ($this->moduleConfigs as $key => $value) {
            if (!Configuration::deleteByName($key)) {
                return false;
            }
        }

        if (parent::uninstall() == false) {
            return false;
        }

        if ($installer->uninstallModuleAdminControllers() == false) {
            return false;
        }

        return true;
    }

    /**
     * Delete order states
     *
     * @return bool
     */
    public function uninstallOrderStates()
    {
        /* @var $orderState OrderState */
        $result = true;
        $collection = new PrestaShopCollection('OrderState');
        $collection->where('module_name', '=', $this->name);
        $orderStates = $collection->getResults();

        if ($orderStates == false) {
            return $result;
        }

        foreach ($orderStates as $orderState) {
            $result &= $orderState->delete();
        }

        return $result;
    }

    public function getUrl()
    {
        if (Configuration::get('PAYPAL_SANDBOX')) {
            return 'https://www.sandbox.paypal.com/';
        } else {
            return 'https://www.paypal.com/';
        }
    }

    public function hookDisplayShoppingCartFooter()
    {
        if ($this->context->controller instanceof CartController === false) {
            return '';
        }

        return $this->displayShortcutButton([
            'sourcePage' => ShortcutConfiguration::SOURCE_PAGE_CART,
            'hook' => ShortcutConfiguration::HOOK_SHOPPING_CART_FOOTER,
        ]);
    }

    public function hookDisplayProductActions($params)
    {
        $content = '';
        $bannerManager = $this->getBannerManager();

        if ($bannerManager->isBannerAvailable()) {
            $content .= $bannerManager->renderForProductPage();
        }

        $content .= $this->displayShortcutButton([
            'sourcePage' => ShortcutConfiguration::SOURCE_PAGE_PRODUCT,
            'hook' => ShortcutConfiguration::HOOK_PRODUCT_ACTIONS,
        ]);
        $content .= $this->renderBnpl(['sourcePage' => ShortcutConfiguration::SOURCE_PAGE_PRODUCT]);

        return $content;
    }

    public function hookDisplayAfterProductThumbs($params)
    {
        if ((int) Configuration::get(ShortcutConfiguration::DISPLAY_MODE_PRODUCT) !== ShortcutConfiguration::DISPLAY_MODE_TYPE_HOOK) {
            return '';
        }

        return $this->displayShortcutButton([
            'sourcePage' => ShortcutConfiguration::SOURCE_PAGE_PRODUCT,
            'hook' => ShortcutConfiguration::HOOK_AFTER_PRODUCT_THUMBS,
        ]);
    }

    public function hookDisplayProductAdditionalInfo($params)
    {
        return $this->displayShortcutButton([
            'sourcePage' => ShortcutConfiguration::SOURCE_PAGE_PRODUCT,
            'hook' => ShortcutConfiguration::HOOK_AFTER_PRODUCT_ADDITIONAL_INFO,
        ]);
    }

    public function hookDisplayFooterProduct($params)
    {
        return $this->displayShortcutButton([
            'sourcePage' => ShortcutConfiguration::SOURCE_PAGE_PRODUCT,
            'hook' => ShortcutConfiguration::HOOK_FOOTER_PRODUCT,
        ]);
    }

    public function hookDisplayExpressCheckout($params)
    {
        $returnContent = $this->displayShortcutButton([
            'sourcePage' => ShortcutConfiguration::SOURCE_PAGE_CART,
            'hook' => ShortcutConfiguration::HOOK_EXPRESS_CHECKOUT,
        ]);
        $returnContent .= $this->renderBnpl(['sourcePage' => ShortcutConfiguration::SOURCE_PAGE_CART]);
        $bannerManager = $this->getBannerManager();

        if ($bannerManager->isBannerAvailable()) {
            $returnContent .= $bannerManager->renderForCartPage();
        }

        return $returnContent;
    }

    public function hookDisplayPersonalInformationTop($params)
    {
        if ($this->context->customer->isLogged() || $this->context->customer->is_guest) {
            return '';
        }

        $content = $this->renderBnpl(['sourcePage' => ShortcutConfiguration::SOURCE_PAGE_SIGNUP]);
        $content .= $this->displayShortcutButton([
            'sourcePage' => ShortcutConfiguration::SOURCE_PAGE_SIGNUP,
            'hook' => ShortcutConfiguration::HOOK_PERSONAL_INFORMATION_TOP,
        ]);

        return $content;
    }

    public function hookDisplayNavFullWidth()
    {
        if ($this->context->controller instanceof IndexController
            || $this->context->controller instanceof CategoryController) {
            $bannerManager = $this->getBannerManager();

            if ($bannerManager->isBannerAvailable()) {
                return $bannerManager->renderForHomePage();
            }
        }
    }

    public function getContent()
    {
        return Tools::redirectAdmin($this->context->link->getAdminLink('AdminPayPalSetup'));
    }

    protected function initVenmoFunctionality()
    {
        return new VenmoFunctionality();
    }

    /**
     * @param $params
     *
     * @return array
     *
     * @throws Exception
     * @throws SmartyException
     */
    public function hookPaymentOptions($params)
    {
        if (Module::isEnabled('braintreeofficial') && (int) Configuration::get('BRAINTREEOFFICIAL_ACTIVATE_PAYPAL')) {
            return [];
        }

        $isoCountryDefault = Country::getIsoById(Configuration::get('PS_COUNTRY_DEFAULT'));
        $payments_options = [];
        $method = AbstractMethodPaypal::load();
        $bnplAvailabilityManager = $this->getBnplAvailabilityManager();
        $bnplOption = $this->getBnplOption();
        $venmoFunctionality = $this->initVenmoFunctionality();

        switch ($this->paypal_method) {
            case 'EC':
                if ($method->isConfigured()) {
                    $paymentOptionsEc = $this->renderEcPaymentOptions($params);
                    $payments_options = array_merge($payments_options, $paymentOptionsEc);

                    if (Configuration::get('PAYPAL_API_CARD') && (in_array($isoCountryDefault, $this->countriesApiCartUnavailable) == false)) {
                        $payment_option = new PaymentOption();
                        $action_text = $this->l('Pay with debit or credit card');
                        $payment_option->setLogo(Media::getMediaPath(_PS_MODULE_DIR_ . $this->name . '/views/img/logo_card.png'));
                        $payment_option->setCallToActionText($action_text);
                        $payment_option->setModuleName('paypal_cb');
                        $payment_option->setAction($this->context->link->getModuleLink($this->name, 'ecInit', ['credit_card' => '1'], true));
                        $payment_option->setAdditionalInformation($this->context->smarty->fetch('module:paypal/views/templates/front/payment_infos_card.tpl'));
                        $payments_options[] = $payment_option;
                    }
                }
                break;
            case 'PPP':
                if ($method->isConfigured()) {
                    if ((Configuration::get('PAYPAL_EXPRESS_CHECKOUT_SHORTCUT') || Configuration::get('PAYPAL_EXPRESS_CHECKOUT_SHORTCUT_CART')) && isset($this->context->cookie->paypal_pSc)) {
                        $payment_option = new PaymentOption();
                        $action_text = $this->l('Pay with paypal plus shortcut');
                        $payment_option->setCallToActionText($action_text);
                        $payment_option->setModuleName('paypal_plus_schortcut');
                        $payment_option->setAction($this->context->link->getModuleLink($this->name, 'pppValidation', ['short_cut' => '1', 'token' => $this->context->cookie->paypal_pSc], true));
                        $payments_options[] = $payment_option;
                    }

                    $payments_options[] = $this->buildPaypalWallet($params);
                }

                break;
            case 'MB':
                if (in_array($this->context->currency->iso_code, $this->currencyMB)) {
                    if ((int) Configuration::get('PAYPAL_MB_EC_ENABLED')) {
                        $methodEC = AbstractMethodPaypal::load('EC');
                        if ($methodEC->isConfigured()) {
                            $paymentOptionsEc = $this->renderEcPaymentOptions($params);
                            $payments_options = array_merge($payments_options, $paymentOptionsEc);
                        }
                    }

                    if ($method->isConfigured() && (int) Configuration::get('PAYPAL_API_CARD') && (in_array($isoCountryDefault, $this->countriesApiCartUnavailable) == false)) {
                        $payment_option = new PaymentOption();
                        $action_text = $this->l('Pay with credit or debit card');
                        $payment_option->setCallToActionText($action_text);
                        $payment_option->setModuleName('paypal_plus_mb');
                        try {
                            $this->context->smarty->assign('path', $this->_path);
                            $payment_option->setAdditionalInformation($this->context->smarty->fetch('module:paypal/views/templates/front/payment_mb.tpl'));
                        } catch (Exception $e) {
                            return;
                        }
                        $payments_options[] = $payment_option;
                    }
                }

                break;
        }

        if ($method->isConfigured()) {
            if ($bnplOption->isEnable() && $bnplOption->displayOnPaymentStep()) {
                if ($bnplAvailabilityManager->isEligibleCountryConfiguration() && $bnplAvailabilityManager->isEligibleContext()) {
                    $payments_options[] = $this->buildBnplPaymentOption($params);
                }
            }

            if ($venmoFunctionality->isAvailable() && $venmoFunctionality->isEnabled() && $venmoFunctionality->isEligibleContext($this->context)) {
                $payments_options[] = $this->buildVenmoPaymentOption($params);
            }

            if ($this->initAcdcFunctionality()->isAvailable() && $this->initAcdcFunctionality()->isEnabled()) {
                $payments_options[] = $this->buildAcdcPaymentOption($params);
            }

            if ($this->initApmFunctionality()->isEnabled() && $this->initApmFunctionality()->isAvailable()) {
                $payments_options = array_merge($payments_options, $this->buildApmPaymentOptions($params));
            }

            if ($this->paypal_method == 'PPP') {
                $payments_options[] = $this->renderSepaOption($params);

                if ($this->getWebhookOption()->isAvailable() && $this->getWebhookOption()->isEnable()) {
                    if ($this->initPuiFunctionality()->isAvailable(false) && $this->initPuiFunctionality()->isEligibleContext($this->context)) {
                        $payments_options[] = $this->renderPuiOption($params);
                    }
                }
            }
        }

        if ($method->isSandbox() && false === empty($payments_options)) {
            foreach ($payments_options as $paymentOption) {
                if ($paymentOption instanceof PaymentOption) {
                    $additionalInformantion = $this->displayAlert(
                        $this->l('Sandbox mode: all transactions will be fictitious.'),
                        'info',
                        false
                    );
                    $additionalInformantion .= $paymentOption->getAdditionalInformation();
                    $paymentOption->setAdditionalInformation($additionalInformantion);
                }
            }
        }

        return $payments_options;
    }

    protected function buildVenmoPaymentOption($params = [])
    {
        $paymentOption = new PaymentOption();
        $action_text = $this->l('Venmo');
        $paymentOption->setCallToActionText($action_text);
        $paymentOption->setAction(
            sprintf(
                'javascript:alert(\'%s\');',
                $this->l('Should use the button "Venmo"') // todo: specify message
            )
        );
        $paymentOption->setModuleName('paypal_venmo');
        $paymentOption->setAdditionalInformation($this->initVenmoButton()->render());
        $paymentOption->setLogo(Media::getMediaPath(_PS_MODULE_DIR_ . $this->name . '/views/img/paypal_logo.png'));

        return $paymentOption;
    }

    protected function initVenmoButton()
    {
        return new VenmoButton();
    }

    protected function renderSepaOption($params)
    {
        $paymentOption = new PaymentOption();
        $paymentOption->setModuleName('paypal_sepa');
        $paymentOption->setCallToActionText($this->l('SEPA'));
        $paymentOption->setAction(
            sprintf(
                'javascript:alert(\'%s\');',
                $this->l('Should use the SEPS payment button')
            )
        );
        $paymentOption->setAdditionalInformation($this->initSepaButton()->render());

        return $paymentOption;
    }

    protected function initSepaButton()
    {
        return new SepaButton();
    }

    public function renderPuiOption($params)
    {
        $paymentOption = new PaymentOption();
        $action_text = $this->l('Pay upon invoice with Paypal');
        $paymentOption->setLogo(Media::getMediaPath(_PS_MODULE_DIR_ . $this->name . '/views/img/paypal_logo.png'));
        $paymentOption->setModuleName('paypal_pui');
        $paymentOption->setCallToActionText($action_text);
        $paymentOption->setAdditionalInformation($this->getFraudNetForm()->render());

        return $paymentOption;
    }

    protected function getFraudSessionId()
    {
        return new FraudSessionId();
    }

    /** @return FraudNetForm*/
    public function getFraudNetForm()
    {
        return new FraudNetForm();
    }

    protected function initApmFunctionality()
    {
        return new ApmFunctionality();
    }

    protected function buildApmPaymentOptions($params)
    {
        $paymentOptions = [];
        $optionsMap = $this->buildOptionsMapAccordingToContext();

        foreach ($optionsMap as $optionMap) {
            $paymentOption = new PaymentOption();
            $paymentOption->setCallToActionText($optionMap['label']);
            $paymentOption->setModuleName('paypal_' . $optionMap['method']);

            if (Configuration::get('PAYPAL_EXPRESS_CHECKOUT_IN_CONTEXT')) {
                $paymentOption->setAdditionalInformation($this->initApmCollection([$optionMap['method']])->render());
            } else {
                $paymentOption->setAction(
                    $this->context->link->getModuleLink(
                        $this->name,
                        'ecInit',
                        [
                            'credit_card' => '0',
                            'methodType' => 'PPP',
                            'apmMethod' => $optionMap['method'],
                        ],
                        true
                    )
                );

                if (isset($optionMap['logo'])) {
                    $paymentOption->setLogo($optionMap['logo']);
                }
            }

            $paymentOptions[] = $paymentOption;
        }

        return $paymentOptions;
    }

    protected function initApmCollection($method = null)
    {
        return new ApmCollection($method);
    }

    protected function initAcdcFunctionality()
    {
        return new AcdcFunctionality();
    }

    protected function buildAcdcPaymentOption($params)
    {
        $paymentOption = new PaymentOption();
        $paymentOption->setCallToActionText($this->l('Credit/Debit card'));
        $paymentOption->setAction(
            sprintf(
                'javascript:alert(\'%s\');',
                $this->l('Should use the ACDC button') // todo: specify message
            )
        );
        $paymentOption->setModuleName('paypal_acdc');
        $paymentOption->setAdditionalInformation($this->initAcdcPaymentMethod()->render());

        return $paymentOption;
    }

    protected function initAcdcPaymentMethod()
    {
        return new AcdcPaymentMethod();
    }

    protected function buildPaypalWallet($params)
    {
        $paymentOption = new PaymentOption();
        $paymentOption->setCallToActionText($this->l('PayPal'));
        $additionalInformation = '';

        $paymentOption->setAction(
            sprintf(
                'javascript:alert(\'%s\');',
                $this->l('Should use the PayPal wallet button ') // todo: specify message
            )
        );
        $is_virtual = 0;
        foreach ($params['cart']->getProducts() as $product) {
            if ($product['is_virtual']) {
                $is_virtual = 1;
                break;
            }
        }

        if (Configuration::get('PAYPAL_EXPRESS_CHECKOUT_IN_CONTEXT')) {
            $additionalInformation .= $this->getShortcutPaymentStep()->render();
        } else {
            $paymentOption->setAction(
                $this->context->link->getModuleLink(
                    $this->name,
                    'ecInit',
                    [
                        'credit_card' => '0',
                        'methodType' => 'PPP',
                    ],
                    true
                )
            );
        }

        if (!$is_virtual && Configuration::get('PAYPAL_API_ADVANTAGES')) {
            $additionalInformation .= $this->context->smarty->fetch('module:paypal/views/templates/front/payment_infos.tpl');
        }

        $paymentOption->setModuleName($this->name);
        $paymentOption->setAdditionalInformation($additionalInformation);

        return $paymentOption;
    }

    /**
     * @param $params
     *
     * @return array of the PaymentOption objects
     *
     * @throws Exception
     * @throws SmartyException
     */
    public function renderEcPaymentOptions($params)
    {
        $paymentOptions = [];
        $is_virtual = 0;
        $additionalInformation = '';
        foreach ($params['cart']->getProducts() as $key => $product) {
            if ($product['is_virtual']) {
                $is_virtual = 1;
                break;
            }
        }
        $paymentOption = new PaymentOption();
        $action_text = $this->l('Pay with Paypal');
        $paymentOption->setModuleName($this->name);
        if (Configuration::get('PAYPAL_API_ADVANTAGES') && $this->isMobile() == false) {
            $action_text .= ' | ' . $this->l('It\'s simple, fast and secure');
        }
        $this->context->smarty->assign([
            'path' => $this->_path,
        ]);
        $paymentOption->setCallToActionText($action_text);
        if (Configuration::get('PAYPAL_EXPRESS_CHECKOUT_IN_CONTEXT')) {
            $additionalInformation .= $this->getShortcutPaymentStep()->render();
        } else {
            $paymentOption->setLogo(Media::getMediaPath(_PS_MODULE_DIR_ . $this->name . '/views/img/paypal_logo.png'));
            $paymentOption->setAction($this->context->link->getModuleLink($this->name, 'ecInit', ['credit_card' => '0'], true));
        }
        if (!$is_virtual && Configuration::get('PAYPAL_API_ADVANTAGES')) {
            $additionalInformation .= $this->context->smarty->fetch('module:paypal/views/templates/front/payment_infos.tpl');
        }

        $paymentOption->setAdditionalInformation($additionalInformation);
        $paymentOptions[] = $paymentOption;

        if ((Configuration::get('PAYPAL_EXPRESS_CHECKOUT_SHORTCUT') || Configuration::get('PAYPAL_EXPRESS_CHECKOUT_SHORTCUT_CART') || Configuration::get('PAYPAL_EXPRESS_CHECKOUT_SHORTCUT_SIGNUP')) && isset($this->context->cookie->paypal_ecs)) {
            $paymentOption = new PaymentOption();
            $action_text = $this->l('Pay with paypal express checkout');
            $paymentOption->setCallToActionText($action_text);
            $paymentOption->setModuleName('express_checkout_schortcut');
            $paymentOption->setAction($this->context->link->getModuleLink($this->name, 'ecValidation', ['short_cut' => '1', 'token' => $this->context->cookie->paypal_ecs], true));
            $paymentOptions[] = $paymentOption;
        }

        return $paymentOptions;
    }

    public function hookHeader()
    {
        $returnContent = '';
        $this->context->controller->registerStylesheet($this->name . '-fo', 'modules/' . $this->name . '/views/css/paypal_fo.css');
        $resources = [];
        $method = AbstractMethodPaypal::load($this->paypal_method);

        if ((int) Configuration::get('PAYPAL_NEED_CHECK_CREDENTIALS')) {
            $method->checkCredentials();
            Configuration::updateValue('PAYPAL_NEED_CHECK_CREDENTIALS', 0);
        }

        if (Tools::getValue('controller') == 'order') {
            if (!$this->checkActiveModule()) {
                return;
            }

            $method = AbstractMethodPaypal::load($this->paypal_method);

            if ($method->isConfigured() == false) {
                return false;
            }

            $this->context->controller->registerJavascript($this->name . '-paypal-info', 'modules/' . $this->name . '/views/js/paypal-info.js');
            $resources[] = _MODULE_DIR_ . $this->name . '/views/js/paypal-info.js';

            // Show Shortcut on signup page if need
            // if ps version is '1.7.6' and bigger than use native hook displayPersonalInformationTop
            if ($this->isShowShortcut() && !$this->context->customer->isLogged() && !$this->context->customer->is_guest) {
                if (version_compare(_PS_VERSION_, '1.7.6', '<')
                    && ((bool) Configuration::get(ShortcutConfiguration::CUSTOMIZE_STYLE) === false || (int) Configuration::get(ShortcutConfiguration::DISPLAY_MODE_SIGNUP) == ShortcutConfiguration::DISPLAY_MODE_TYPE_HOOK)) {
                    $Shortcut = new ShortcutSignup();
                    $returnContent .= $this->renderBnpl(['sourcePage' => ShortcutConfiguration::SOURCE_PAGE_SIGNUP]) . $Shortcut->render();
                }
                $returnContent .= $this->context->smarty->fetch('module:paypal/views/templates/front/prefetch.tpl');

                return $returnContent;
            }

            if ((Configuration::get(ShortcutConfiguration::SHOW_ON_PRODUCT_PAGE) || Configuration::get(ShortcutConfiguration::SHOW_ON_CART_PAGE) || Configuration::get(ShortcutConfiguration::SHOW_ON_SIGNUP_STEP))
                && (isset($this->context->cookie->paypal_ecs) || isset($this->context->cookie->paypal_pSc))) {
                $this->context->controller->registerJavascript($this->name . '-paypal-ec-sc', 'modules/' . $this->name . '/views/js/shortcut_payment.js');
                $resources[] = _MODULE_DIR_ . $this->name . '/views/js/shortcut_payment.js' . '?v=' . $this->version;
                if (isset($this->context->cookie->paypal_ecs)) {
                    Media::addJsDef([
                        'paypalCheckedMethod' => 'express_checkout_schortcut',
                    ]);
                    $cookie_paypal_email = $this->context->cookie->paypal_ecs_email;
                } elseif (isset($this->context->cookie->paypal_pSc)) {
                    Media::addJsDef([
                        'paypalCheckedMethod' => 'paypal_plus_schortcut',
                    ]);
                    $cookie_paypal_email = $this->context->cookie->paypal_pSc_email;
                } else {
                    $cookie_paypal_email = '';
                }

                $this->context->smarty->assign('paypalEmail', $cookie_paypal_email);
                $this->context->smarty->assign('isSandbox', $method->isSandbox());
                $carrierFees = $this->context->cart->getOrderTotal(true, Cart::ONLY_SHIPPING);

                if ($carrierFees == 0) {
                    $messageForCustomer = $this->context->smarty->fetch('module:paypal/views/templates/front/_partials/messageForCustomerOne.tpl');
                } else {
                    $this->context->smarty->assign('carrierFees', Tools::displayPrice($carrierFees));
                    $messageForCustomer = $this->context->smarty->fetch('module:paypal/views/templates/front/_partials/messageForCustomerTwo.tpl');
                }

                Media::addJsDefL('scPaypalCheckedMsg', $messageForCustomer);
            }

            if ($this->paypal_method == 'MB') {
                $method->assignJSvarsPaypalMB();
                $this->context->controller->registerJavascript($this->name . '-plusdcc-minjs', 'https://www.paypalobjects.com/webstatic/ppplusdcc/ppplusdcc.min.js', ['server' => 'remote']);
                $this->context->controller->registerJavascript($this->name . '-mb-payment-js', 'modules/' . $this->name . '/views/js/payment_mb.js');
                $resources[] = _MODULE_DIR_ . $this->name . '/views/js/payment_mb.js' . '?v=' . $this->version;
                $resources[] = 'https://www.paypalobjects.com/webstatic/ppplusdcc/ppplusdcc.min.js' . '?v=' . $this->version;
            }
        } elseif (Tools::getValue('controller') == 'cart') {
            if (!$this->checkActiveModule()) {
                return;
            }

            if ($this->paypal_method == 'MB') {
                $resources[] = 'https://www.paypalobjects.com/webstatic/ppplusdcc/ppplusdcc.min.js' . '?v=' . $this->version;
            }
        }

        $this->context->smarty->assign('resources', $resources);
        $returnContent .= $this->context->smarty->fetch('module:paypal/views/templates/front/prefetch.tpl');

        return $returnContent;
    }

    public function hookDisplayInvoiceLegalFreeText($params)
    {
        $paypal_order = PaypalOrder::loadByOrderId($params['order']->id);

        if (!Validate::isLoadedObject($paypal_order) || $paypal_order->method != 'PPP'
            || $paypal_order->payment_tool != 'PAY_UPON_INVOICE') {
            return '';
        }

        $method = AbstractMethodPaypal::load();
        $response = $method->getInfo($paypal_order->id_payment);

        if ($response->isSuccess() == false) {
            return '';
        }

        $bankDetails = $response->getDepositBankDetails();
        $tab = $this->l('The bank name') . ' : ' . $bankDetails->getBankName() . ';
        ' . $this->l('Account holder name') . ' : ' . $bankDetails->getAccountHolderName() . ';
        ' . $this->l('IBAN') . ' : ' . $bankDetails->getIban() . ';
        ' . $this->l('BIC') . ' : ' . $bankDetails->getBic();

        return $tab;
    }

    public function checkActiveModule()
    {
        $active = false;
        $modules = Hook::getHookModuleExecList('paymentOptions');
        if (empty($modules)) {
            return;
        }
        foreach ($modules as $module) {
            if ($module['module'] == $this->name) {
                $active = true;
            }
        }

        return $active;
    }

    /**
     * Get url for BT onboarding
     *
     * @param object $ps_order PS order object
     * @param string $transaction_id payment transaction ID
     */
    public function setTransactionId($ps_order, $transaction_id)
    {
        Db::getInstance()->update('order_payment', [
            'transaction_id' => pSQL($transaction_id),
        ], 'order_reference = "' . pSQL($ps_order->reference) . '"');
    }

    public function hookDisplayOrderConfirmation($params)
    {
        $paypal_order = PaypalOrder::loadByOrderId($params['order']->id);
        if (!Validate::isLoadedObject($paypal_order)) {
            return;
        }

        $this->context->smarty->assign([
            'transaction_id' => $paypal_order->id_transaction,
            'method' => $paypal_order->method,
        ]);

        $this->context->controller->registerJavascript($this->name . '-order_confirmation_js', $this->_path . '/views/js/order_confirmation.js');

        return $this->context->smarty->fetch('module:paypal/views/templates/hook/order_confirmation.tpl');
    }

    public function hookDisplayOrderPreview($params)
    {
        $params['class_logger'] = 'PaypalLog';
        if ($result = $this->handleExtensionsHook(__FUNCTION__, $params)) {
            if (!is_null($result)) {
                return $result;
            }
        }
    }

    public function hookDisplayReassurance()
    {
        if ($this->context->controller instanceof ProductController) {
            $content = '';
            $bannerManager = $this->getBannerManager();

            if ($bannerManager->isBannerAvailable() && version_compare(_PS_VERSION_, '1.7.6', '<')) {
                $content .= $bannerManager->renderForProductPage();
            }

            $content .= $this->displayShortcutButton([
                'sourcePage' => ShortcutConfiguration::SOURCE_PAGE_PRODUCT,
                'hook' => ShortcutConfiguration::HOOK_REASSURANCE,
            ]);

            if (version_compare(_PS_VERSION_, '1.7.6', '<')) {
                $content .= $this->renderBnpl(['sourcePage' => ShortcutConfiguration::SOURCE_PAGE_PRODUCT]);
            }

            return $content;
        }

        if ($this->context->controller instanceof CartController) {
            return $this->displayShortcutButton([
                'sourcePage' => ShortcutConfiguration::SOURCE_PAGE_CART,
                'hook' => ShortcutConfiguration::HOOK_REASSURANCE,
            ]);
        }

        if ($this->context->controller instanceof OrderController) {
            $bannerManager = $this->getBannerManager();

            if ($bannerManager->isBannerAvailable()) {
                return $bannerManager->renderForCheckoutPage();
            }
        }

        return '';
    }

    /**
     * @param array $data
     *
     * @return string
     */
    public function renderBnpl($data)
    {
        $bnplOption = new BNPLOption();
        $bnplAvailabilityManager = $this->getBnplAvailabilityManager();
        $bnpl = new BNPLDummy();

        if (false == $bnplAvailabilityManager->isEligibleCountryConfiguration()) {
            return '';
        }

        if ($bnplOption->isEnable() == false) {
            return '';
        }

        if (false == $bnplAvailabilityManager->isEligibleContext()) {
            return '';
        }

        if ($data['sourcePage'] == ConfigurationMap::PAGE_TYPE_PAYMENT_STEP) {
            if ($bnplOption->displayOnPaymentStep() == false) {
                return '';
            }

            $bnpl = new BNPLPaymentStep();
        }

        if ($data['sourcePage'] == ShortcutConfiguration::SOURCE_PAGE_CART) {
            if ($bnplOption->displayOnCart() == false) {
                return '';
            }

            if ($this->context->cart->nbProducts() == 0 || $this->context->cart->checkQuantities() === false) {
                return '';
            }

            $bnpl = new BNPLCart();
        }

        if ($data['sourcePage'] == ShortcutConfiguration::SOURCE_PAGE_PRODUCT) {
            if ($bnplOption->displayOnProduct() == false) {
                return '';
            }

            $bnpl = new BNPLProduct((int) Tools::getValue('id_product'));
        }

        if ($data['sourcePage'] == ShortcutConfiguration::SOURCE_PAGE_SIGNUP) {
            if ($bnplOption->displayOnSignup() == false) {
                return '';
            }

            $bnpl = new BNPLSignup();
        }

        if ($this->paypal_method == 'MB') {
            $methodType = 'EC';
        } else {
            $methodType = $this->paypal_method;
        }

        $method = AbstractMethodPaypal::load($methodType);

        if ($method->isConfigured() == false) {
            return '';
        }

        return $bnpl->render();
    }

    /**
     * @param array $data
     *
     * @return string
     */
    public function displayShortcutButton($data)
    {
        if ($this->isShowShortcut() === false) {
            return '';
        }

        if (false === isset($data['sourcePage'])) {
            return '';
        }

        if (isset($data['hook'])) {
            if ($data['sourcePage'] == ShortcutConfiguration::SOURCE_PAGE_PRODUCT) {
                if (Configuration::get(ShortcutConfiguration::CUSTOMIZE_STYLE)
                    && (int) Configuration::get(ShortcutConfiguration::DISPLAY_MODE_PRODUCT)
                    !== ShortcutConfiguration::DISPLAY_MODE_TYPE_HOOK) {
                    return '';
                }
                // Take a hook by default
                if (version_compare(_PS_VERSION_, '1.7.6', '<')
                    || (int) Configuration::getGlobalValue(ShortcutConfiguration::USE_OLD_HOOK)) {
                    $hookSetted = ShortcutConfiguration::HOOK_REASSURANCE;
                } else {
                    $hookSetted = ShortcutConfiguration::HOOK_PRODUCT_ACTIONS;
                }

                // If a style customization conf is active, take a hook configured
                if (Configuration::get(ShortcutConfiguration::CUSTOMIZE_STYLE)) {
                    $hookSetted = Configuration::get(ShortcutConfiguration::PRODUCT_PAGE_HOOK);
                }

                if ($hookSetted != $data['hook']) {
                    return '';
                }
            }

            if ($data['sourcePage'] == ShortcutConfiguration::SOURCE_PAGE_CART) {
                if ($this->context->cart->nbProducts() == 0 || $this->context->cart->checkQuantities() === false) {
                    return '';
                }

                if (Configuration::get(ShortcutConfiguration::CUSTOMIZE_STYLE)
                    && (int) Configuration::get(ShortcutConfiguration::DISPLAY_MODE_CART)
                    !== ShortcutConfiguration::DISPLAY_MODE_TYPE_HOOK) {
                    return '';
                }
                // Take a hook by default
                if ((int) Configuration::getGlobalValue(ShortcutConfiguration::USE_OLD_HOOK)) {
                    $hookSetted = ShortcutConfiguration::HOOK_SHOPPING_CART_FOOTER;
                } else {
                    $hookSetted = ShortcutConfiguration::HOOK_EXPRESS_CHECKOUT;
                }

                // If a style customization conf is active, take a hook configured
                if (Configuration::get(ShortcutConfiguration::CUSTOMIZE_STYLE)) {
                    $hookSetted = Configuration::get(ShortcutConfiguration::CART_PAGE_HOOK);
                }

                if ($hookSetted != $data['hook']) {
                    return '';
                }
            }

            if ($data['sourcePage'] == ShortcutConfiguration::SOURCE_PAGE_SIGNUP) {
                if (Configuration::get(ShortcutConfiguration::CUSTOMIZE_STYLE)
                    && (int) Configuration::get(ShortcutConfiguration::DISPLAY_MODE_SIGNUP)
                    !== ShortcutConfiguration::DISPLAY_MODE_TYPE_HOOK) {
                    return '';
                }
            }
        }

        if ($this->paypal_method == 'MB') {
            $methodType = 'EC';
        } else {
            $methodType = $this->paypal_method;
        }

        $method = AbstractMethodPaypal::load($methodType);

        if ($method->isConfigured() == false) {
            return '';
        }

        return $method->renderExpressCheckoutShortCut($data['sourcePage'], (isset($data['isWidget']) ? $data['isWidget'] : false));
    }

    /**
     * Check if we need convert currency
     *
     * @return bool|int currency id
     */
    public function needConvert()
    {
        $currency_mode = Currency::getPaymentCurrenciesSpecial($this->id);
        $mode_id = $currency_mode['id_currency'];
        if ($mode_id == -2) {
            return (int) Configuration::get('PS_CURRENCY_DEFAULT');
        } elseif ($mode_id == -1) {
            return false;
        } elseif ($mode_id != $this->context->currency->id) {
            return (int) $mode_id;
        } else {
            return false;
        }
    }

    /**
     * Get payment currency iso code
     *
     * @return string currency iso code
     */
    public function getPaymentCurrencyIso()
    {
        if ($id_currency = $this->needConvert()) {
            $currency = new Currency((int) $id_currency);
        } else {
            if (Validate::isLoadedObject(Context::getContext()->currency)) {
                $currency = Context::getContext()->currency;
            } else {
                $currency = new Currency((int) Configuration::get('PS_CURRENCY_DEFAULT'));
            }
        }

        return $currency->iso_code;
    }

    public function validateOrder(
        $id_cart, $id_order_state,
        $amount_paid,
        $payment_method = 'Unknown',
        $message = null,
        $transaction = [],
        $currency_special = null,
        $dont_touch_amount = false,
        $secure_key = false,
        Shop $shop = null,
        $order_reference = null
    ) {
        if ($this->needConvert()) {
            $amount_paid_curr = Tools::ps_round(Tools::convertPrice($amount_paid, new Currency($currency_special), true), 2);
        } else {
            $amount_paid_curr = Tools::ps_round($amount_paid, 2);
        }
        $amount_paid = Tools::ps_round($amount_paid, 2);

        $cart = new Cart((int) $id_cart);
        $total_ps = (float) $cart->getOrderTotal(true, Cart::BOTH);
        if ($amount_paid_curr > $total_ps + 0.10 || $amount_paid_curr < $total_ps - 0.10) {
            $total_ps = $amount_paid_curr;
        }

        try {
            parent::validateOrder(
                (int) $id_cart,
                (int) $id_order_state,
                (float) $total_ps,
                $payment_method,
                $message,
                ['transaction_id' => isset($transaction['transaction_id']) ? $transaction['transaction_id'] : ''],
                $currency_special,
                $dont_touch_amount,
                $secure_key,
                $shop
            );
        } catch (Exception $e) {
            $log = 'Order validation error : ' . $e->getMessage() . ';';
            $log .= ' File: ' . $e->getFile() . ';';
            $log .= ' Line: ' . $e->getLine() . ';';
            ProcessLoggerHandler::openLogger();
            ProcessLoggerHandler::logError(
                $log,
                isset($transaction['transaction_id']) ? $transaction['transaction_id'] : null,
                null,
                (int) $id_cart,
                $this->context->shop->id,
                isset($transaction['payment_tool']) && $transaction['payment_tool'] ? $transaction['payment_tool'] : 'PayPal',
                (int) Configuration::get('PAYPAL_SANDBOX'),
                isset($transaction['date_transaction']) ? $transaction['date_transaction'] : null
            );
            ProcessLoggerHandler::closeLogger();

            $this->currentOrder = (int) Order::getIdByCartId((int) $id_cart);

            if ($this->currentOrder == false) {
                $msg = $this->l('Order validation error : ') . $e->getMessage() . '. ';
                if (isset($transaction['transaction_id']) && $id_order_state != Configuration::get('PS_OS_ERROR')) {
                    $msg .= $this->l('Attention, your payment is made. Please, contact customer support. Your transaction ID is  : ') . $transaction['transaction_id'];
                }
                Tools::redirect(Context::getContext()->link->getModuleLink('paypal', 'error', ['error_msg' => $msg, 'no_retry' => true]));
            }
        }

        $adminEmployee = new Employee(_PS_ADMIN_PROFILE_);
        $order = new Order($this->currentOrder);
        $orderState = new OrderState($order->current_state, $adminEmployee->id_lang);

        if (is_string($orderState->name)) {
            $message = $orderState->name;
        } elseif (is_array($orderState->name)) {
            $message = (isset($orderState->name[$order->id_lang]) ? $orderState->name[$order->id_lang] : current($orderState->name));
        } else {
            $message = $this->l('Order creation is successful');
        }

        ProcessLoggerHandler::openLogger();
        ProcessLoggerHandler::logInfo(
            $message,
            isset($transaction['transaction_id']) ? $transaction['transaction_id'] : null,
            $this->currentOrder,
            (int) $id_cart,
            $this->context->shop->id,
            isset($transaction['payment_tool']) && $transaction['payment_tool'] ? $transaction['payment_tool'] : 'PayPal',
            (int) Configuration::get('PAYPAL_SANDBOX'),
            isset($transaction['date_transaction']) ? $transaction['date_transaction'] : null
        );
        ProcessLoggerHandler::closeLogger();

        if (Tools::version_compare(_PS_VERSION_, '1.7.1.0', '>')) {
            $order = Order::getByCartId($id_cart);
        } else {
            $id_order = Order::getOrderByCartId($id_cart);
            $order = new Order($id_order);
        }

        if ($amount_paid_curr != 0 && $order->total_paid != $amount_paid_curr && $this->isOneOrder($order->reference)) {
            $order->total_paid = $amount_paid_curr;
            $order->total_paid_real = $amount_paid_curr;
            $order->total_paid_tax_incl = $amount_paid_curr;
            $order->update();

            $sql = 'UPDATE `' . _DB_PREFIX_ . 'order_payment`
		    SET `amount` = ' . (float) $amount_paid_curr . '
		    WHERE  `order_reference` = "' . pSQL($order->reference) . '"';
            Db::getInstance()->execute($sql);
        }

        //if there isn't a method, then we don't create PaypalOrder and PaypalCapture
        if (isset($transaction['method']) && $transaction['method']) {
            $paypal_order = new PaypalOrder();
            $paypal_order->id_order = $this->currentOrder;
            $paypal_order->id_cart = $id_cart;
            $paypal_order->id_transaction = $transaction['transaction_id'];
            $paypal_order->id_payment = $transaction['id_payment'];
            $paypal_order->payment_method = $transaction['payment_method'];
            $paypal_order->currency = $transaction['currency'];
            $paypal_order->total_paid = (float) $amount_paid;
            $paypal_order->payment_status = $transaction['payment_status'];
            $paypal_order->total_prestashop = (float) $total_ps;
            $paypal_order->method = $transaction['method'];
            $paypal_order->payment_tool = isset($transaction['payment_tool']) ? $transaction['payment_tool'] : 'PayPal';
            $paypal_order->sandbox = (int) Configuration::get('PAYPAL_SANDBOX');
            $paypal_order->intent = $transaction['intent'];
            $paypal_order->save();

            if ($transaction['capture']) {
                $paypal_capture = new PaypalCapture();
                $paypal_capture->id_paypal_order = $paypal_order->id;
                $paypal_capture->save();
            }

            if ($this->getWebhookOption()->isEnable() && $this->getWebhookOption()->isAvailable()) {
                if (PaypalPaymentMode::isSale()) {
                    $this
                        ->getWebhookService()
                        ->createForOrder(
                            $paypal_order,
                            $this->getStatusMapping()->getAcceptedStatus()
                        );
                }
            }
        }
    }

    public function hookDisplayAdminOrder($params)
    {
        // Since Ps 1.7.7 this hook is displayed at bottom of a page and we should use a hook DisplayAdminOrderTop
        if (version_compare(_PS_VERSION_, '1.7.7', '>=')) {
            return false;
        }

        $return = $this->getAdminOrderPageMessages($params);
        $return .= $this->getPartialRefund($params);

        return $return;
    }

    protected function getPartialRefund($params)
    {
        $paypal_order = PaypalOrder::loadByOrderId($params['id_order']);

        if (!Validate::isLoadedObject($paypal_order)) {
            return '';
        }

        $this->context->smarty->assign('chb_paypal_refund', $this->l('Refund on PayPal'));

        return $this->context->smarty->fetch(_PS_MODULE_DIR_ . $this->name . '/views/templates/hook/partialRefund.tpl');
    }

    public function hookDisplayAdminOrderTop($params)
    {
        $return = $this->getAdminOrderPageMessages($params);
        $return .= $this->getPartialRefund($params);

        return $return;
    }

    protected function getAdminOrderPageMessages($params)
    {
        /* @var $paypal_order PaypalOrder */
        $id_order = $params['id_order'];
        $order = new Order((int) $id_order);
        $paypal_msg = "<div class='module_warning'>";
        $paypal_order = PaypalOrder::loadByOrderId($id_order);
        $paypal_capture = PaypalCapture::loadByOrderPayPalId($paypal_order->id);

        if (!Validate::isLoadedObject($paypal_order)) {
            return false;
        }

        //Webhooks that wait more 1 hour
        $oldPendingWebhooks = $this->getWebhookService()->getPendingWebhooks($paypal_order, 1);
        $pendingWebhooks = $this->getWebhookService()->getPendingWebhooks($paypal_order);
        $totalRefunded = $this->getTotalRefunded($paypal_order);
        $totalPaid = $order->getTotalPaid();

        if ($totalRefunded > 0 && $totalRefunded < $totalPaid) {
            $paypal_msg .= $this->displayInformation(
                sprintf(
                    $this->l('A partial refund has been issued : %.2f refunded instead of %.2f'),
                    $totalRefunded,
                    $totalPaid
                )
            );
        }

        if (false == empty($oldPendingWebhooks)) {
            $paypal_msg .= $this->displayError(
                $this->l('Event notification has not been received yet. Please check if your website has a correct SSL certificate (https) or htaccess are not enabled.')
            );
        } elseif (false == empty($pendingWebhooks)) {
            foreach ($pendingWebhooks as $webhook) {
                $orderState = new OrderState($webhook->id_state, $this->context->language->id);
                $paypal_msg .= $this->displayInformation(
                    sprintf(
                        $this->l('A request has been sent to PayPal. The order status \'%s\' will be applied after confirmation from PayPal.'),
                        $orderState->name ? $orderState->name : ''
                    )
                );
            }
        }

        if ($order->current_state == $this->getStatusMapping()->getWaitValidationStatus() && $paypal_order->method == 'EC' && $paypal_order->intent == MethodEC::AUTHORIZE) {
            $paypal_msg .= $this->displayInformation(
                $this->l('This order has been created in Authorize mode, so you customer was not charged yet. Set \'Payment accepted status\' in order to confirm the order and to capture it or \'Cancelled\' if you want to cancel it.')
            );
        }

        if ($paypal_order->method == 'BT' && (Module::isInstalled('braintreeofficial') == false)) {
            $tmpMessage = "<p class='paypal-warning'>";
            $tmpMessage .= $this->l('This order has been paid via Braintree payment solution provided by PayPal module prior v5.0. ') . '</br>';
            $tmpMessage .= $this->l('Starting from v5.0.0 of PayPal module, Braintree payment solution won\'t be available via PayPal module anymore. You can continue using Braintree by installing the new Braintree module available via ') . "<a href='https://addons.prestashop.com/' target='_blank'>" . $this->l('addons.prestashop') . '</a>' . '</br>';
            $tmpMessage .= $this->l('All actions on this order will not be processed by Braintree until you install the new module (ex: you cannot refund this order automatically by changing order status).');
            $tmpMessage .= '</p>';
            $paypal_msg .= $this->displayWarning($tmpMessage);
        }
        if ($paypal_order->sandbox) {
            $tmpMessage = "<p class='paypal-warning'>";
            $tmpMessage .= $this->l('[SANDBOX] Please pay attention that payment for this order was made via PayPal Sandbox mode.');
            $tmpMessage .= '</p>';
            $paypal_msg .= $this->displayWarning($tmpMessage);
        }
        if (isset($_SESSION['need_refund']) && $_SESSION['need_refund']) {
            unset($_SESSION['need_refund']);
            $tmpMessage = "<p class='paypal-warning'>";
            $tmpMessage .= $this->l('The order should be refunded before the cancellation. Please select the status "Refunded".');
            $tmpMessage .= $this->l('You can cancel the order after. If you don\'t want to generate a refund automatically on PayPal when you change the status, you can disable it via the module settings: "Experience -> Advanced settings - Customize order status", select "no action".');
            $tmpMessage .= '</p>';
            $paypal_msg .= $this->displayWarning($tmpMessage);
        }
        if (isset($_SESSION['not_payed_capture']) && $_SESSION['not_payed_capture']) {
            unset($_SESSION['not_payed_capture']);
            $paypal_msg .= $this->displayWarning(
                '<p class="paypal-warning">' . $this->l('You can\'t refund order as it hasn\'t be paid yet.') . '</p>'
            );
        }
        if (Tools::getValue('error_refund')) {
            $paypal_msg .= $this->displayWarning(
                '<p class="paypal-warning">' . $this->l('We encountered an unexpected problem during refund operation. For more details please see the \'PayPal\' tab in the order details.') . '</p>'
            );
        }
        if (Tools::getValue('cancel_failed')) {
            $paypal_msg .= $this->displayWarning(
                '<p class="paypal-warning">' . $this->l('We encountered an unexpected problem during cancel operation. For more details please see the \'PayPal\' tab in the order details.') . '</p>'
            );
        }
        if ($order->current_state == Configuration::get('PS_OS_REFUND') && $paypal_order->payment_status == 'Refunded') {
            $msg = $this->l('Your order is fully refunded by PayPal.');
            $paypal_msg .= $this->displayWarning(
                '<p class="paypal-warning">' . $msg . '</p>'
            );
        }

        if ($order->getCurrentOrderState()->paid == 1 && Validate::isLoadedObject($paypal_capture) && $paypal_capture->id_capture) {
            $msg = $this->l('Your order is captured by PayPal.');
            $paypal_msg .= $this->displayWarning(
                '<p class="paypal-warning">' . $msg . '</p>'
            );
        }
        if (Tools::getValue('error_capture')) {
            $paypal_msg .= $this->displayWarning(
                '<p class="paypal-warning">' . $this->l('We encountered an unexpected problem during capture operation. See messages for more details.') . '</p>'
            );
        }

        if ($paypal_order->total_paid != $paypal_order->total_prestashop) {
            $preferences = $this->context->link->getAdminLink('AdminPreferences', true);
            $paypal_msg .= $this->displayWarning('<p class="paypal-warning">' . $this->l('Product pricing has been modified as your rounding settings aren\'t compliant with PayPal.') . ' ' .
                $this->l('To avoid automatic rounding to customer for PayPal payments, please update your rounding settings.') . ' ' .
                '<a target="_blank" href="' . $preferences . '">' . $this->l('Read more.') . '</a></p>');
        }

        if (isset($_SESSION['paypal_transaction_already_refunded']) && $_SESSION['paypal_transaction_already_refunded']) {
            unset($_SESSION['paypal_transaction_already_refunded']);
            $tmpMessage = '<p class="paypal-warning">';
            $tmpMessage .= $this->l('The order status was changed but this transaction has already been fully refunded.');
            $tmpMessage .= '</p>';
            $paypal_msg .= $this->displayWarning($tmpMessage);
        }

        if (isset($_SESSION['paypal_partial_refund_successful']) && $_SESSION['paypal_partial_refund_successful']) {
            unset($_SESSION['paypal_partial_refund_successful']);
            $tmpMessage = '<p class="paypal-warning">';
            $tmpMessage .= $this->l('A refund request has been sent to PayPal.');
            $tmpMessage .= '</p>';
            $paypal_msg .= $this->displayWarning($tmpMessage);
        }

        $paypal_msg .= '</div>';

        return $paypal_msg . $this->display(__FILE__, 'views/templates/hook/paypal_order.tpl');
    }

    public function hookActionBeforeCartUpdateQty($params)
    {
        $this->resetCookiePaymentInfo();
    }

    public function resetCookiePaymentInfo()
    {
        if (isset($this->context->cookie->paypal_ecs) || isset($this->context->cookie->paypal_ecs_payerid)) {
            //unset cookie of payment init if it's no more same cart
            Context::getContext()->cookie->__unset('paypal_ecs');
            Context::getContext()->cookie->__unset('paypal_ecs_payerid');
            Context::getContext()->cookie->__unset('paypal_ecs_email');
        }
        if (isset($this->context->cookie->paypal_pSc) || isset($this->context->cookie->paypal_pSc_payerid)) {
            //unset cookie of payment init if it's no more same cart
            Context::getContext()->cookie->__unset('paypal_pSc');
            Context::getContext()->cookie->__unset('paypal_pSc_payerid');
            Context::getContext()->cookie->__unset('paypal_pSc_email');
        }
    }

    public function hookActionOrderSlipAdd($params)
    {
        if (Tools::isSubmit('doPartialRefundPaypal')) {
            $paypalOrder = PaypalOrder::loadByOrderId($params['order']->id);

            if (!Validate::isLoadedObject($paypalOrder)) {
                return false;
            }

            $method = AbstractMethodPaypal::load($paypalOrder->method);
            $capture = PaypalCapture::loadByOrderPayPalId($paypalOrder->id);

            if (Validate::isLoadedObject($capture) && !$capture->id_capture) {
                ProcessLoggerHandler::openLogger();
                ProcessLoggerHandler::logError(
                    $this->l('You can\'t refund order as it hasn\'t be paid yet.'),
                    null,
                    $paypalOrder->id_order,
                    $paypalOrder->id_cart,
                    $this->context->shop->id,
                    $paypalOrder->payment_tool,
                    $paypalOrder->sandbox
                );
                ProcessLoggerHandler::closeLogger();

                return true;
            }

            $params = array_merge(Tools::getAllValues(), $params);
            /** @var \PaypalAddons\classes\API\Response\ResponseOrderRefund */
            $refundResponse = $method->partialRefund($params);

            if ($refundResponse->isSuccess()) {
                if ($this->getWebhookOption()->isEnable() && $this->getWebhookOption()->isAvailable()) {
                    if (session_status() == PHP_SESSION_NONE) {
                        session_start();
                    }

                    $_SESSION['paypal_partial_refund_successful'] = true;
                }

                ProcessLoggerHandler::openLogger();
                ProcessLoggerHandler::logInfo(
                    $this->getMessageFromRefundResponse($refundResponse),
                    $refundResponse->getIdTransaction(),
                    $paypalOrder->id_order,
                    $paypalOrder->id_cart,
                    $this->context->shop->id,
                    $paypalOrder->payment_tool,
                    $paypalOrder->sandbox,
                    $refundResponse->getDateTransaction()
                );
                ProcessLoggerHandler::closeLogger();
            } else {
                ProcessLoggerHandler::openLogger();
                ProcessLoggerHandler::logError(
                    $refundResponse->getError()->getMessage(),
                    null,
                    $paypalOrder->id_order,
                    $paypalOrder->id_cart,
                    $this->context->shop->id,
                    $paypalOrder->payment_tool,
                    $paypalOrder->sandbox
                );
                ProcessLoggerHandler::closeLogger();
            }
        }
    }

    public function getMessageFromRefundResponse(PaypalAddons\classes\API\Response\ResponseOrderRefund $response)
    {
        $message = '';

        if ($this->getWebhookOption()->isEnable() && $this->getWebhookOption()->isAvailable()) {
            $message .= $this->l('The refund request has been sent with success. Waiting for a webhook message.');
        }

        return $message .= $response->getMessage();
    }

    public function hookActionOrderStatusPostUpdate(&$params)
    {
        if ($params['newOrderStatus']->paid == 1) {
            $capture = PaypalCapture::getByOrderId($params['id_order']);
            $ps_order = new Order($params['id_order']);
            if (isset($capture['id_capture']) && $capture['id_capture']) {
                $this->setTransactionId($ps_order, $capture['id_capture']);
            }
        }
    }

    public function hookActionLocalizationPageSave($params)
    {
        $countryDefault = new Country((int) Configuration::get('PS_COUNTRY_DEFAULT'));
        $method = AbstractMethodPaypal::load();

        if (Validate::isLoadedObject($countryDefault) === false) {
            return;
        }

        $installmentTab = \Tab::getInstanceFromClassName('AdminPaypalInstallment');

        if (Validate::isLoadedObject($installmentTab) === false) {
            return;
        }

        if (in_array(Tools::strtolower($countryDefault->iso_code), InstallmentConfiguration::getAllowedCountries()) && $method->isConfigured()) {
            foreach (Language::getLanguages() as $language) {
                switch (Tools::strtolower($language['iso_code'])) {
                    case 'fr':
                        $installmentTab->name[$language['id_lang']] = '4X PayPal';
                        break;
                    case 'de':
                        $installmentTab->name[$language['id_lang']] = 'Später Bezahlen';
                        break;
                    case 'au':
                        $installmentTab->name[$language['id_lang']] = 'Pay in 4';
                        break;
                    case 'uk':
                        $installmentTab->name[$language['id_lang']] = 'Pay Later';
                        break;
                    case 'us':
                        $installmentTab->name[$language['id_lang']] = 'Pay Later';
                        break;
                    default:
                        $installmentTab->name[$language['id_lang']] = 'Pay Later';
                        break;
                }
            }

            $installmentTab->active = true;
            $installmentTab->save();
        } else {
            if ($installmentTab->active == true) {
                $installmentTab->active = false;
                $installmentTab->save();
            }
        }
    }

    /**
     * @return StatusMapping
     */
    protected function getStatusMapping()
    {
        return new StatusMapping();
    }

    public function hookActionOrderStatusUpdate(&$params)
    {
        if (PaypalContext::getContext()->get('skipHandleHookActionOrderStatusUpdate', false)) {
            return true;
        }
        /** @var $orderPayPal PaypalOrder */
        $orderPayPal = PaypalOrder::loadByOrderId($params['id_order']);
        $isRequestSent = false;

        if (!Validate::isLoadedObject($orderPayPal) || $orderPayPal->method == 'BT') {
            return false;
        }

        $method = AbstractMethodPaypal::load($orderPayPal->method);

        $osCanceled = $this->getStatusMapping()->getCanceledStatus($method);
        $osRefunded = $this->getStatusMapping()->getRefundStatus($method);
        $osPaymentAccepted = $this->getStatusMapping()->getAcceptedStatus();

        if ($params['newOrderStatus']->id == $osCanceled) {
            if ($this->context->controller instanceof PaypalIpnModuleFrontController) {
                return true;
            }

            if (in_array($orderPayPal->payment_status, ['refunded', 'voided'])) {
                return true;
            }

            if ($orderPayPal->method != 'EC') {
                return true;
            }

            $paypalCapture = PaypalCapture::loadByOrderPayPalId($orderPayPal->id);

            //If a payment is already captured, so need to refund firstly
            if (false == Validate::isLoadedObject($paypalCapture) || $paypalCapture->id_capture) {
                if (session_status() == PHP_SESSION_NONE) {
                    session_start();
                }

                $_SESSION['need_refund'] = true;
                Tools::redirect($_SERVER['HTTP_REFERER']);
            }

            /** @var $response \PaypalAddons\classes\API\Response\ResponseAuthorizationVoid */
            $response = $method->void($orderPayPal);

            if ($response->isSuccess()) {
                $isRequestSent = true;
                if (Validate::isLoadedObject($paypalCapture)) {
                    $paypalCapture->result = 'voided';
                    $paypalCapture->save();
                }

                $orderPayPal->payment_status = 'voided';
                $orderPayPal->save();
                $message = $response->getMessage();

                if ($this->getWebhookOption()->isEnable() && $this->getWebhookOption()->isAvailable()) {
                    $message .= '. ' . $this->l('Waiting for webhook message in order for change the order status.');
                }

                ProcessLoggerHandler::openLogger();
                ProcessLoggerHandler::logInfo(
                    $message,
                    $response->getIdTransaction(),
                    $orderPayPal->id_order,
                    $orderPayPal->id_cart,
                    $this->context->shop->id,
                    $orderPayPal->payment_tool,
                    $orderPayPal->sandbox,
                    $response->getDateTransaction()
                );
                ProcessLoggerHandler::closeLogger();
            } else {
                ProcessLoggerHandler::openLogger();
                ProcessLoggerHandler::logError(
                    $response->getError()->getMessage(),
                    null,
                    $orderPayPal->id_order,
                    $orderPayPal->id_cart,
                    $this->context->shop->id,
                    $orderPayPal->payment_tool,
                    $orderPayPal->sandbox
                );
                ProcessLoggerHandler::closeLogger();
                Tools::redirect($_SERVER['HTTP_REFERER'] . '&cancel_failed=1');
            }
        }

        if ($params['newOrderStatus']->id == $osRefunded) {
            if ($this->context->controller instanceof PaypalIpnModuleFrontController) {
                return true;
            }

            $capture = PaypalCapture::loadByOrderPayPalId($orderPayPal->id);
            if (Validate::isLoadedObject($capture) && !$capture->id_capture) {
                ProcessLoggerHandler::openLogger();
                ProcessLoggerHandler::logError(
                    $this->l('You can\'t refund order as it hasn\'t be paid yet.'),
                    null,
                    $orderPayPal->id_order,
                    $orderPayPal->id_cart,
                    $this->context->shop->id,
                    $orderPayPal->payment_tool,
                    $orderPayPal->sandbox
                );
                ProcessLoggerHandler::closeLogger();
                if (session_status() == PHP_SESSION_NONE) {
                    session_start();
                }

                $_SESSION['not_payed_capture'] = true;
                Tools::redirect($_SERVER['HTTP_REFERER']);
            }

            /** @var \PaypalAddons\classes\API\Response\ResponseOrderRefund */
            $refundResponse = $method->refund($orderPayPal);

            if ($refundResponse->isSuccess()) {
                $isRequestSent = true;
                if (Validate::isLoadedObject($capture)) {
                    $capture->result = 'refunded';
                    $capture->save();
                }

                $orderPayPal->payment_status = 'refunded';
                $orderPayPal->save();

                ProcessLoggerHandler::openLogger();
                ProcessLoggerHandler::logInfo(
                    $this->getMessageFromRefundResponse($refundResponse),
                    $refundResponse->getIdTransaction(),
                    $orderPayPal->id_order,
                    $orderPayPal->id_cart,
                    $this->context->shop->id,
                    $orderPayPal->payment_tool,
                    $orderPayPal->sandbox,
                    $refundResponse->getDateTransaction()
                );
                ProcessLoggerHandler::closeLogger();
            } else {
                if ($refundResponse->isAlreadyRefunded()) {
                    if (session_status() == PHP_SESSION_NONE) {
                        session_start();
                    }

                    $_SESSION['paypal_transaction_already_refunded'] = true;
                } else {
                    ProcessLoggerHandler::openLogger();
                    ProcessLoggerHandler::logError(
                        $refundResponse->getError()->getMessage(),
                        null,
                        $orderPayPal->id_order,
                        $orderPayPal->id_cart,
                        $this->context->shop->id,
                        $orderPayPal->payment_tool,
                        $orderPayPal->sandbox
                    );
                    ProcessLoggerHandler::closeLogger();
                    Tools::redirect($_SERVER['HTTP_REFERER'] . '&error_refund=1');
                }
            }
        }

        if ($params['newOrderStatus']->id == $osPaymentAccepted) {
            $capture = PaypalCapture::loadByOrderPayPalId($orderPayPal->id);

            if (!Validate::isLoadedObject($capture)) {
                return false;
            }

            $response = $method->confirmCapture($orderPayPal);

            if ($response->isSuccess()) {
                $isRequestSent = true;
                $orderPayPal->payment_status = $response->getStatus();
                $capture->id_capture = $response->getIdTransaction();
                $capture->result = $response->getStatus();
                $orderPayPal->save();
                $capture->save();
                $message = $response->getMessage();

                if ($this->getWebhookOption()->isEnable() && $this->getWebhookOption()->isAvailable()) {
                    $message .= '. ' . $this->l('Waiting for webhook message in order for change the order status.');
                }

                ProcessLoggerHandler::openLogger();
                ProcessLoggerHandler::logInfo(
                    $message,
                    $response->getIdTransaction(),
                    $orderPayPal->id_order,
                    $orderPayPal->id_cart,
                    $this->context->shop->id,
                    $orderPayPal->payment_tool,
                    $orderPayPal->sandbox,
                    $response->getDateTransaction()
                );
                ProcessLoggerHandler::closeLogger();
            } else {
                ProcessLoggerHandler::openLogger();
                ProcessLoggerHandler::logError(
                    $response->getError()->getMessage(),
                    null,
                    $orderPayPal->id_order,
                    $orderPayPal->id_cart,
                    $this->context->shop->id,
                    $orderPayPal->payment_tool,
                    $orderPayPal->sandbox
                );
                ProcessLoggerHandler::closeLogger();
                Tools::redirect($_SERVER['HTTP_REFERER'] . '&error_capture=1');
            }
        }

        if ($this->getWebhookOption()->isEnable() && $this->getWebhookOption()->isAvailable() && $isRequestSent) {
            try {
                $this->getWebhookService()->createForOrder($orderPayPal, $params['newOrderStatus']->id);
            } catch (Exception $e) {
            }

            if (class_exists('AdminOrdersController') && $this->context->controller instanceof AdminOrdersController) {
                Tools::redirect($_SERVER['HTTP_REFERER']);
            }

            /** @var PrestaShopBundle\Service\Routing\Router $router */
            $router = $this->get('prestashop.router');
            $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
            $match = $router->getMatcher()->match($request->getPathInfo());

            if (isset($match['_legacy_controller']) && $match['_legacy_controller'] == 'AdminOrders') {
                Tools::redirect($_SERVER['HTTP_REFERER']);
            }

            throw new \PaypalAddons\classes\PaypalException(0, $this->l('A request has been sent to PayPal. The order status will be updated after confirmation from PayPal'));
        }
    }

    /**
     * @return WebhookService
     */
    public function getWebhookService()
    {
        return new WebhookService();
    }

    /**
     * Get URL for EC onboarding
     *
     * @return string
     */
    public function getPartnerInfo()
    {
        $urlParams = [
            'active_method' => Tools::getValue('method'),
            'paypal_set_config' => 1,
            'with_card' => 0,
            'id_shop' => $this->context->shop->id,
        ];
        $return_url = $this->context->link->getAdminLink('AdminPayPalSetup', true, null, $urlParams);
        if ($this->context->country->iso_code == 'CN') {
            $country = 'C2';
        } else {
            $country = $this->context->country->iso_code;
        }

        $partner_info = [
            'email' => $this->context->employee->email,
            'language' => $this->context->language->iso_code . '_' . Tools::strtoupper($this->context->country->iso_code),
            'shop_url' => Tools::getShopDomainSsl(true),
            'address1' => Configuration::get('PS_SHOP_ADDR1', null, null, null, ''),
            'address2' => Configuration::get('PS_SHOP_ADDR2', null, null, null, ''),
            'city' => Configuration::get('PS_SHOP_CITY', null, null, null, ''),
            'country_code' => Tools::strtoupper($country),
            'postal_code' => Configuration::get('PS_SHOP_CODE', null, null, null, ''),
            'state' => Configuration::get('PS_SHOP_STATE_ID', null, null, null, ''),
            'return_url' => str_replace('http://', 'https://', $return_url),
            'first_name' => $this->context->employee->firstname,
            'last_name' => $this->context->employee->lastname,
            'shop_name' => Configuration::get('PS_SHOP_NAME', null, null, null, ''),
            'ref_merchant' => 'PrestaShop_' . (getenv('PLATEFORM') == 'PSREADY' ? 'Ready' : ''),
            'ps_version' => _PS_VERSION_,
            'pp_version' => $this->version,
            'sandbox' => Configuration::get('PAYPAL_SANDBOX') ? 'true' : '',
        ];

        $response = 'https://partners-subscribe.prestashop.com/paypal/request.php?' . http_build_query($partner_info, '', '&');

        return $response;
    }

    public static function getPrecision()
    {
        if (version_compare(_PS_VERSION_, '1.7.7', '<')) {
            return _PS_PRICE_DISPLAY_PRECISION_;
        } else {
            return Context::getContext()->getComputingPrecision();
        }
    }

    /**
     * Get decimal correspondent to payment currency
     *
     * @return int Number of decimal
     */
    public static function getDecimal($isoCurrency = null)
    {
        $paypal = Module::getInstanceByName('paypal');
        $currency_wt_decimal = ['HUF', 'JPY', 'TWD'];

        if ($isoCurrency === null || Currency::exists($isoCurrency) === false) {
            $isoCurrency = $paypal->getPaymentCurrencyIso();
        }

        $precision = self::getPrecision();

        if (in_array($isoCurrency, $currency_wt_decimal) || ($precision == 0)) {
            return (int) 0;
        } else {
            return (int) 2;
        }
    }

    /**
     * Get State ID
     *
     * @param $ship_addr_state string state code from PayPal
     * @param $ship_addr_country string delivery country iso code from PayPal
     *
     * @return int id state
     */
    public static function getIdStateByPaypalCode($ship_addr_state, $ship_addr_country)
    {
        $id_state = 0;
        $id_country = Country::getByIso($ship_addr_country);
        if (Country::containsStates($id_country)) {
            if (isset(PayPal::$state_iso_code_matrix[$ship_addr_country])) {
                $matrix = PayPal::$state_iso_code_matrix[$ship_addr_country];
                $ship_addr_state = array_search(Tools::strtolower($ship_addr_state), array_map('strtolower', $matrix));
            }
            if ($id_state = (int) State::getIdByIso(Tools::strtoupper($ship_addr_state), $id_country)) {
                $id_state = $id_state;
            } elseif ($id_state = State::getIdByName(pSQL(trim($ship_addr_state)))) {
                $state = new State((int) $id_state);
                if ($state->id_country == $id_country) {
                    $id_state = $state->id;
                }
            }
        }

        return $id_state;
    }

    /**
     * Get delivery state code in paypal format
     *
     * @param $address Address object
     *
     * @return string state code
     */
    public static function getPaypalStateCode($address)
    {
        $ship_addr_state = '';
        if ($address->id_state) {
            $country = new Country((int) $address->id_country);
            $state = new State((int) $address->id_state);
            if (isset(PayPal::$state_iso_code_matrix[$country->iso_code]) &&
                empty(PayPal::$state_iso_code_matrix[$country->iso_code]) == false
            ) {
                $matrix = PayPal::$state_iso_code_matrix[$country->iso_code];
                $ship_addr_state = $matrix[$state->iso_code] ? $matrix[$state->iso_code] : $matrix[$state->name];
            } else {
                $ship_addr_state = $state->iso_code;
            }
        }

        return $ship_addr_state;
    }

    public function hookDisplayAdminOrderTabOrder($params)
    {
        $params['class_logger'] = 'PaypalLog';
        if ($result = $this->handleExtensionsHook(__FUNCTION__, $params)) {
            if (!is_null($result)) {
                return $result;
            }
        }
    }

    public function hookDisplayAdminOrderTabLink($params)
    {
        $order = new Order((int) $params['id_order']);
        $params['order'] = $order;
        $return = $this->hookDisplayAdminOrderTabOrder($params);

        return $return;
    }

    public function hookDisplayAdminOrderTabContent($params)
    {
        $order = new Order((int) $params['id_order']);
        $params['order'] = $order;

        return $this->hookDisplayAdminOrderContentOrder($params);
    }

    public function hookDisplayAdminOrderContentOrder($params)
    {
        $params['class_logger'] = 'PaypalLog';
        if ($result = $this->handleExtensionsHook(__FUNCTION__, $params)) {
            if (!is_null($result)) {
                return $result;
            }
        }
    }

    public function hookDisplayAdminCartsView($params)
    {
        $params['class_logger'] = 'PaypalLog';
        if ($result = $this->handleExtensionsHook(__FUNCTION__, $params)) {
            if (!is_null($result)) {
                return $result;
            }
        }
    }

    public function isOneOrder($order_reference)
    {
        $query = new DbQuery();
        $query->select('COUNT(*)');
        $query->from('orders');
        $query->where('reference = "' . pSQL($order_reference) . '"');
        $countOrders = (int) Db::getInstance()->getValue($query);

        return $countOrders == 1;
    }

    public function showWarningForUserBraintree()
    {
        return (int) Configuration::get('PAYPAL_BRAINTREE_ENABLED') &&
            !Configuration::get('PAYPAL_USE_WITHOUT_BRAINTREE') &&
            Configuration::get('PAYPAL_METHOD') == 'BT';
    }

    public function displayInformation($message, $btnClose = true, $widthByContent = false, $class = false)
    {
        return $this->displayAlert($message, 'info', $btnClose, $widthByContent, $class);
    }

    public function displayError($message, $btnClose = true, $widthByContent = false, $class = false)
    {
        return $this->displayAlert($message, 'danger', $btnClose, $widthByContent, $class);
    }

    public function displayWarning($message, $btnClose = true, $widthByContent = false, $class = false)
    {
        return $this->displayAlert($message, 'warning', $btnClose, $widthByContent, $class);
    }

    public function displayAlert($message, $type, $btnClose = true, $widthByContent = false, $class = false)
    {
        $tplVars = [
            'message' => $message,
            'btnClose' => $btnClose,
            'widthByContent' => $widthByContent,
            'class' => $class,
            'type' => $type,
        ];
        $this->context->smarty->assign($tplVars);

        return $this->context->smarty->fetch(_PS_MODULE_DIR_ . $this->name . '/views/templates/admin/_partials/alert.tpl');
    }

    public function isSslActive()
    {
        return \Configuration::get('PS_SSL_ENABLED') && \Configuration::get('PS_SSL_ENABLED_EVERYWHERE');
    }

    public function renameTabParent()
    {
        $tab = Tab::getInstanceFromClassName('AdminParentPaypalConfiguration');

        if (Validate::isLoadedObject($tab) == false) {
            return;
        }

        $name = [];

        foreach (Language::getLanguages() as $lang) {
            if ($lang['iso_code'] == 'fr') {
                $name[$lang['id_lang']] = 'PayPal Officiel';
            } else {
                $name[$lang['id_lang']] = 'PayPal Official';
            }
        }
        $tab->name = $name;
        $tab->save();
    }

    public function handleExtensionsHook($hookName, $params)
    {
        if (!isset($this->extensions) || empty($this->extensions)) {
            return false;
        }
        $result = false;
        foreach ($this->extensions as $extension) {
            /** @var AbstractModuleExtension $extension */
            $extension = new $extension();
            $extension->setModule($this);
            if (is_callable([$extension, $hookName])) {
                $hookResult = $extension->{$hookName}($params);
                if ($result === false) {
                    $result = $hookResult;
                } elseif (is_array($hookResult) && $result !== false) {
                    $result = array_merge($result, $hookResult);
                } else {
                    $result .= $hookResult;
                }
            }
        }

        return $result;
    }

    /**
     * Handle module widget call
     *
     * @param $action
     * @param $method
     * @param $hookName
     * @param $configuration
     *
     * @return bool
     *
     * @throws \ReflectionException
     */
    public function handleWidget($action, $method, $hookName, $configuration)
    {
        if (!isset($this->extensions) || empty($this->extensions)) {
            return false;
        }

        foreach ($this->extensions as $extension) {
            /** @var AbstractModuleExtension $extension */
            $extension = new $extension();
            if (!($extension instanceof WidgetInterface)) {
                continue;
            }
            $extensionClass = (new ReflectionClass($extension))->getShortName();
            if ($extensionClass != $action) {
                continue;
            }
            $extension->setModule($this);
            if (is_callable([$extension, $method])) {
                return $extension->{$method}($hookName, $configuration);
            }
        }

        return false;
    }

    /**
     * TODO
     * Reset Module only if merchant choose to keep data on modal
     *
     * @return bool
     *
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function reset()
    {
        $installer = new ModuleInstaller($this);

        return $installer->reset($this);
    }

    /**
     * Add checkbox carrier restrictions for a module.
     *
     * @param array $shops
     *
     * @return bool
     */
    public function addCheckboxCarrierRestrictionsForModule(array $shops = [])
    {
        if (!$shops) {
            $shops = \Shop::getShops(true, null, true);
        }

        $carriers = \Carrier::getCarriers($this->context->language->id, false, false, false, null, \Carrier::ALL_CARRIERS);
        $carrier_ids = [];
        foreach ($carriers as $carrier) {
            $carrier_ids[] = $carrier['id_reference'];
        }

        foreach ($shops as $s) {
            foreach ($carrier_ids as $id_carrier) {
                if (!\Db::getInstance()->execute('INSERT INTO `' . _DB_PREFIX_ . 'module_carrier` (`id_module`, `id_shop`, `id_reference`)
				VALUES (' . (int) $this->id . ', "' . (int) $s . '", ' . (int) $id_carrier . ')')) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Add radio currency restrictions for a new module.
     *
     * @param array $shops
     *
     * @return bool
     */
    public function addRadioCurrencyRestrictionsForModule(array $shops = [])
    {
        if (!$shops) {
            $shops = Shop::getShops(true, null, true);
        }

        $query = 'INSERT INTO `' . _DB_PREFIX_ . 'module_currency` (`id_module`, `id_shop`, `id_currency`) VALUES (%d, %d, %d)';

        foreach ($shops as $s) {
            if (!Db::getInstance()->execute(sprintf($query, $this->id, $s, PAYPAL_PAYMENT_CUSTOMER_CURRENCY))) {
                return false;
            }
        }

        return true;
    }

    /**
     * Add checkbox country restrictions for a new module.
     *
     * @param array $shops
     *
     * @return bool
     */
    public function addCheckboxCountryRestrictionsForModule(array $shops = [])
    {
        return Country::addModuleRestrictions($shops, [], [['id_module' => (int) $this->id]]);
    }

    /**
     * @return array return the unregistered hooks
     */
    public function getHooksUnregistered()
    {
        $hooksUnregistered = [];

        foreach ($this->hooks as $hookName) {
            $alias = '';

            try {
                $alias = Hook::getNameById(Hook::getIdByName($hookName));
            } catch (Exception $e) {
            }

            $hookName = empty($alias) ? $hookName : $alias;

            if (Hook::isModuleRegisteredOnHook($this, $hookName, $this->context->shop->id)) {
                continue;
            }

            $hooksUnregistered[] = $hookName;
        }

        return $hooksUnregistered;
    }

    public function unregisterHook($hook_id, $shop_list = null)
    {
        try {
            return parent::unregisterHook($hook_id, $shop_list);
        } catch (Exception $e) {
            return false;
        }
    }

    public function unregisterExceptions($hook_id, $shop_list = null)
    {
        try {
            return parent::unregisterExceptions($hook_id, $shop_list);
        } catch (Exception $e) {
            return false;
        }
    }

    public function resetHooks()
    {
        //Unregister module hooks
        // Retrieve hooks used by the module
        $query = new DbQuery();
        $query
            ->from('hook_module')
            ->where('id_module = ' . (int) $this->id)
            ->select('id_hook');
        $result = Db::getInstance()->executeS($query);

        if (false === empty($result)) {
            foreach ($result as $row) {
                $this->unregisterHook((int) $row['id_hook']);
                $this->unregisterExceptions((int) $row['id_hook']);
            }
        }

        //Register hooks
        if (false === empty($this->hooks)) {
            foreach ($this->hooks as $hook) {
                $this->registerHook($hook);
            }
        }
    }

    public function getIpnPaypalListener($sandbox = null)
    {
        if ($sandbox === null) {
            $sandbox = (int) Configuration::get('PAYPAL_SANDBOX');
        }

        if ((int) $sandbox) {
            return 'https://ipnpb.sandbox.paypal.com/cgi-bin/webscr';
        } else {
            return 'https://ipnpb.paypal.com/cgi-bin/webscr';
        }
    }

    /**
     * @return bool
     */
    public function showWarningForPayPalPlusUsers()
    {
        $result = true;
        $methodPPP = AbstractMethodPaypal::load('PPP');
        $methodEC = AbstractMethodPaypal::load('EC');
        $result &= $this->paypal_method == 'PPP';
        $result &= $methodPPP->isConfigured() == false;
        $result &= $methodEC->isConfigured();

        return $result;
    }

    public function getOrderStatuses()
    {
        $orderStatuses = [
            [
                'id' => 0,
                'name' => $this->l('No action'),
            ],
        ];
        $prestashopOrderStatuses = OrderState::getOrderStates($this->context->language->id);

        foreach ($prestashopOrderStatuses as $prestashopOrderStatus) {
            $orderStatuses[] = [
                'id' => $prestashopOrderStatus['id_order_state'],
                'name' => $prestashopOrderStatus['name'],
            ];
        }

        return $orderStatuses;
    }

    public function showPsCheckoutMessage()
    {
        $countryDefault = new Country((int) \Configuration::get('PS_COUNTRY_DEFAULT'), $this->context->language->id);
        $notShowDetails = Configuration::get('PAYPAL_NOT_SHOW_PS_CHECKOUT');

        if (is_string($notShowDetails)) {
            try {
                $notShowDetailsArray = json_decode($notShowDetails, true);
                $notShowPsCheckout = isset($notShowDetailsArray[$this->version]) ? (bool) $notShowDetailsArray[$this->version] : false;
            } catch (Exception $e) {
                $notShowPsCheckout = false;
            }
        } else {
            $notShowPsCheckout = false;
        }

        return in_array($countryDefault->iso_code, $this->countriesApiCartUnavailable) && ($notShowPsCheckout == false);
    }

    public function setPsCheckoutMessageValue($value)
    {
        $notShowDetails = Configuration::get('PAYPAL_NOT_SHOW_PS_CHECKOUT');

        if (is_string($notShowDetails)) {
            try {
                $notShowDetailsArray = json_decode($notShowDetails, true);
                $notShowDetailsArray[$this->version] = $value;
            } catch (Exception $e) {
                $notShowDetailsArray = [$this->version => $value];
            }
        } else {
            $notShowDetailsArray = [$this->version => $value];
        }

        return Configuration::updateValue('PAYPAL_NOT_SHOW_PS_CHECKOUT', json_encode($notShowDetailsArray));
    }

    /**
     * @return bool
     */
    public function isShowShortcut()
    {
        if (is_null($this->context->controller)) {
            return false;
        }

        if (Module::isEnabled('braintreeofficial') && (int) Configuration::get('BRAINTREEOFFICIAL_ACTIVATE_PAYPAL')) {
            return false;
        }

        if ($this->paypal_method === 'MB' && (bool) Configuration::get('PAYPAL_MB_EC_ENABLED') === false) {
            return false;
        }

        if ($this->context->controller instanceof OrderController && Configuration::get(ShortcutConfiguration::SHOW_ON_SIGNUP_STEP)) {
            return true;
        }

        if ($this->context->controller instanceof ProductController && Configuration::get(ShortcutConfiguration::SHOW_ON_PRODUCT_PAGE)) {
            return true;
        }

        if ($this->context->controller instanceof CartController && Configuration::get(ShortcutConfiguration::SHOW_ON_CART_PAGE)) {
            return true;
        }

        return false;
    }

    public function renderWidget($hookName, array $configuration)
    {
        $widget = $this->getWidget($hookName, $configuration);

        return $widget->render();
    }

    public function getWidgetVariables($hookName, array $configuration)
    {
    }

    public function getIdProductAttributeByIdAttributes($idProduct, $idAttributes, $findBest = false)
    {
        if (version_compare(_PS_VERSION_, '1.7.3.4.0', '<')) {
            return Product::getIdProductAttributesByIdAttributes($idProduct, $idAttributes, $findBest);
        } else {
            return Product::getIdProductAttributeByIdAttributes($idProduct, $idAttributes, $findBest);
        }
    }

    /**
     * @param string $hookName
     * @param array $configurations
     *
     * @return \PaypalAddons\classes\Widget\AbstractWidget
     */
    protected function getWidget($hookName, array $configuration)
    {
        if (isset($configuration['action']) && $configuration['action'] === 'paymentshortcut') {
            return new ShortcutWidget($this, $configuration);
        }

        if (isset($configuration['action']) && $configuration['action'] === 'banner4x') {
            return new InstallmentWidget($this, $configuration);
        }

        return new DummyWidget($this, $configuration);
    }

    /**
     * @return BannerManager
     */
    public function getBannerManager()
    {
        return new BannerManager();
    }

    public function getWebhookOption()
    {
        return new WebhookOption();
    }

    protected function getPaypalOrderService()
    {
        return new ServicePaypalOrder();
    }

    public function registerHooks()
    {
        $result = true;
        $hooksUnregistered = $this->getHooksUnregistered();

        if (empty($hooksUnregistered)) {
            return $result;
        }

        foreach ($hooksUnregistered as $hookName) {
            $result &= $this->registerHook($hookName);
        }

        return $result;
    }

    /**
     * @param PaypalOrder $paypalOrder
     *
     * @return float
     */
    public function getTotalRefunded(PaypalOrder $paypalOrder)
    {
        $webhookOption = $this->getWebhookOption();
        $paymentRefundAmount = $this->getPaymentRefundAmount();

        if ($webhookOption->isEnable() && $webhookOption->isAvailable()) {
            return $paymentRefundAmount->calculateReceivedWebhookEvent($paypalOrder);
        }

        try {
            return $paymentRefundAmount->calculateTotalRefunded($paypalOrder);
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * @return PaymentRefundAmount
     */
    public function getPaymentRefundAmount()
    {
        return new PaymentRefundAmount();
    }

    /** @return bool*/
    public function installModels()
    {
        $installer = new ModuleInstaller($this);

        return $installer->installObjectModels();
    }

    /**
     * @return BnplAvailabilityManager
     */
    public function getBnplAvailabilityManager()
    {
        return new BnplAvailabilityManager();
    }

    /**
     * @return BNPLOption
     */
    public function getBnplOption()
    {
        return new BNPLOption();
    }

    /**
     * @return PaymentOption
     */
    protected function buildBnplPaymentOption($params)
    {
        $paymentOption = new PaymentOption();
        $action_text = $this->l('Pay Later');
        $paymentOption->setCallToActionText($action_text);
        $paymentOption->setAction(
            sprintf(
                'javascript:alert(\'%s\');',
                $this->l('Should use the button "Pay in X times"') // todo: specify message
            )
        );
        $paymentOption->setModuleName('paypal_bnpl');
        $paymentOption->setAdditionalInformation($this->renderBnpl(['sourcePage' => ConfigurationMap::PAGE_TYPE_PAYMENT_STEP]));

        return $paymentOption;
    }

    public function getShortcutPaymentStep()
    {
        return new ShortcutPaymentStep();
    }

    protected function initPuiFunctionality()
    {
        return new PuiFunctionality();
    }

    protected function buildOptionsMapAccordingToContext()
    {
        $map = [];

        if (empty($this->context->cart->id_address_delivery)) {
            return $map;
        }

        $addressDelivery = new Address($this->context->cart->id_address_delivery);
        $isoCountry = Tools::strtoupper((string) Country::getIsoById($addressDelivery->id_country));
        $isoCurrency = Tools::strtoupper((string) $this->context->currency->iso_code);

        if (empty($isoCountry) || empty($isoCurrency)) {
            return $map;
        }

        if ($isoCountry == 'BE' && $isoCurrency == 'EUR') {
            $map[] = [
                'method' => APM::BANCONTACT,
                'label' => $this->l('Bancontact'),
            ];

            $map[] = [
                'method' => APM::SOFORT,
                'label' => $this->l('Sofort'),
            ];
        }

        if ($isoCountry == 'BR' && $isoCurrency == 'BRL') {
            $map[] = [
                'method' => APM::BOLETOBANCARIO,
                'label' => $this->l('Boleto Bancario'),
            ];
        }

        if ($isoCountry == 'PL' && $isoCurrency == 'PLN') {
            $map[] = [
                'method' => APM::BLIK,
                'label' => $this->l('BLIK'),
            ];

            $map[] = [
                'method' => APM::P24,
                'label' => $this->l('Przelewy24'),
            ];
        }

        if ($isoCountry == 'AT' && $isoCurrency == 'EUR') {
            $map[] = [
                'method' => APM::EPS,
                'label' => $this->l('eps'),
            ];

            $map[] = [
                'method' => APM::SOFORT,
                'label' => $this->l('Sofort'),
            ];
        }

        if ($isoCountry == 'DE' && $isoCurrency == 'EUR') {
            $map[] = [
                'method' => APM::GIROPAY,
                'label' => $this->l('giropay'),
                'logo' => Media::getMediaPath(_PS_MODULE_DIR_ . $this->name . '/views/img/giropay.svg'),
            ];

            $map[] = [
                'method' => APM::SOFORT,
                'label' => $this->l('Sofort'),
                'logo' => Media::getMediaPath(_PS_MODULE_DIR_ . $this->name . '/views/img/sofort.svg'),
            ];
        }

        if ($isoCountry == 'NL' && $isoCurrency == 'EUR') {
            $map[] = [
                'method' => APM::IDEAL,
                'label' => $this->l('iDEAL'),
            ];

            $map[] = [
                'method' => APM::SOFORT,
                'label' => $this->l('Sofort'),
            ];

            $map[] = [
                'method' => APM::TRUSTLY,
                'label' => $this->l('Trustly'),
            ];
        }

        if ($isoCountry == 'PT' && $isoCurrency == 'EUR') {
            $map[] = [
                'method' => APM::MULTIBANCO,
                'label' => $this->l('Multibanco'),
            ];
        }

        if ($isoCountry == 'IT' && $isoCurrency == 'EUR') {
            $map[] = [
                'method' => APM::MYBANK,
                'label' => $this->l('MyBank'),
            ];

            $map[] = [
                'method' => APM::SOFORT,
                'label' => $this->l('Sofort'),
            ];
        }

        if ($isoCountry == 'MX' && $isoCurrency == 'MXN') {
            $map[] = [
                'method' => APM::OXXO,
                'label' => $this->l('OXXO'),
            ];
        }

        if ($isoCountry == 'GB' && $isoCurrency == 'GBP') {
            $map[] = [
                'method' => APM::SOFORT,
                'label' => $this->l('Sofort'),
            ];
        }

        if ($isoCountry == 'ES' && $isoCurrency == 'EUR') {
            $map[] = [
                'method' => APM::SOFORT,
                'label' => $this->l('Sofort'),
            ];
        }

        if ($isoCountry == 'US' && $isoCurrency == 'EUR') {
            $map[] = [
                'method' => APM::SOFORT,
                'label' => $this->l('Sofort'),
            ];
        }

        if (in_array($isoCountry, ['EE', 'FI', 'SE']) && $isoCurrency == 'EUR') {
            $map[] = [
                'method' => APM::TRUSTLY,
                'label' => $this->l('Trustly'),
            ];
        }

        if ($isoCountry == 'SE' && $isoCurrency == 'SEK') {
            $map[] = [
                'method' => APM::TRUSTLY,
                'label' => $this->l('Trustly'),
            ];
        }

        return $map;
    }

    public function hookActionAdminOrdersTrackingNumberUpdate($params)
    {
        if (empty($params['order']) || Validate::isLoadedObject($params['order']) == false) {
            return;
        }

        /** @var $paypalOrder PaypalOrder */
        $paypalOrder = PaypalOrder::loadByOrderId($params['order']->id);
        $method = AbstractMethodPaypal::load($this->paypal_method);
        $response = $method->addOrderTrackingInfo($paypalOrder);

        if ($response->isSuccess()) {
            ProcessLoggerHandler::openLogger();
            ProcessLoggerHandler::logInfo(
                'Added tracking info',
                $paypalOrder->id_transaction,
                $paypalOrder->id_order,
                $paypalOrder->id_cart,
                $this->context->shop->id,
                $paypalOrder->payment_tool,
                $paypalOrder->sandbox
            );
            ProcessLoggerHandler::closeLogger();
        } else {
            ProcessLoggerHandler::openLogger();
            ProcessLoggerHandler::logInfo(
                'Adding tracking info is failed. Error message: ' . $response->getError()->getMessage(),
                $paypalOrder->id_transaction,
                $paypalOrder->id_order,
                $paypalOrder->id_cart,
                $this->context->shop->id,
                $paypalOrder->payment_tool,
                $paypalOrder->sandbox
            );
            ProcessLoggerHandler::closeLogger();
        }
    }

    public function hookActionPaypalGetConflicts()
    {
        $conflicts = [];

        if (Module::isEnabled('pricerounding')) {
            $conflicts[] = $this->l('Using the module \'pricerounding\' can lead to an incorrect work.');
        }

        if (\Configuration::get('PS_ROUND_TYPE') != \Order::ROUND_ITEM
            || \Configuration::get('PS_PRICE_ROUND_MODE') != PS_ROUND_HALF_UP
            || \Configuration::get('PS_PRICE_DISPLAY_PRECISION') != 2) {
            $conflicts[] = $this->l('Your rounding settings are not fully compatible with PayPal requirements. In order to avoid some of the transactions to fail, please change the PrestaShop rounding mode.');
        }

        return $conflicts;
    }

    protected function isMobile()
    {
        return preg_match(
            "/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i",
            $_SERVER['HTTP_USER_AGENT']
        );
    }
}
