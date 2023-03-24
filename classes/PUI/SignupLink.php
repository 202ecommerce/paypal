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

namespace PaypalAddons\classes\PUI;

use PayPal;
use PaypalAddons\classes\AbstractMethodPaypal;
use PaypalAddons\services\Core\PaypalMerchantId;

class SignupLink
{
    protected $method;

    public function __construct(AbstractMethodPaypal $method)
    {
        $this->method = $method;
    }

    public function get()
    {
        if ($this->method->isSandbox()) {
            $urlLink = 'https://www.sandbox.paypal.com/bizsignup/partner/entry?';
        } else {
            $urlLink = 'https://www.paypal.com/bizsignup/partner/entry?';
        }

        $params = [
            'partnerClientId' => $this->getPartnerClientId(),
            'partnerId' => $this->getPartnerId(),
            'integrationType' => 'FO',
            'features' => 'PAYMENT,REFUND,ACCESS_MERCHANT_INFORMATION',
            'returnToPartnerUrl' => \Context::getContext()->link->getAdminLink('AdminPaypalGetCredentials'),
            'displayMode' => 'minibrowser',
            'product' => 'ppcp',
            'secondaryProducts' => 'payment_methods',
            'capabilities' => 'PAY_UPON_INVOICE',
            'country.x' => 'DE',
            'locale.x' => 'de-DE',
            'sellerNonce' => $this->getSellerNonce(),
        ];

        return $urlLink . http_build_query($params);
    }

    protected function initMerchantId()
    {
        return new PaypalMerchantId();
    }

    protected function getPartnerClientId()
    {
        if ($this->method->isSandbox()) {
            return PayPal::PAYPAL_PARTNER_CLIENT_ID_SANDBOX;
        } else {
            return PayPal::PAYPAL_PARTNER_CLIENT_ID_LIVE;
        }
    }

    protected function getPartnerId()
    {
        if ($this->method->isSandbox()) {
            return PayPal::PAYPAL_PARTNER_ID_SANDBOX;
        } else {
            return PayPal::PAYPAL_PARTNER_ID_LIVE;
        }
    }

    protected function getSellerNonce()
    {
        return $this->method->getSellerNonce();
    }
}
