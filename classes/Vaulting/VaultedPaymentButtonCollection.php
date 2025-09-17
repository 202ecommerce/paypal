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

namespace PaypalAddons\classes\Vaulting;

use PaypalAddons\classes\AbstractMethodPaypal;
use PaypalAddons\services\ServicePaypalVaulting;

if (!defined('_PS_VERSION_')) {
    exit;
}

class VaultedPaymentButtonCollection
{
    /** @var \Context */
    protected $context;

    /** @var AbstractMethodPaypal */
    protected $method;

    protected $vaultingFunctionality;

    protected $paypalVaultingService;

    protected $paymentSourceType;

    protected $id_customer;

    public function __construct($id_customer, $paymentSourceType)
    {
        $this->context = \Context::getContext();
        $this->method = AbstractMethodPaypal::load();
        $this->vaultingFunctionality = new VaultingFunctionality();
        $this->paypalVaultingService = new ServicePaypalVaulting();
        $this->paymentSourceType = (string) $paymentSourceType;
        $this->id_customer = (int) $id_customer;
    }

    public function render()
    {
        $collection = '';
        /** @var \PaypalVaulting $paypalVaulting */
        foreach ($this->paypalVaultingService->getVaultListByCustomer($this->id_customer) as $paypalVaulting) {
            if ($paypalVaulting->payment_source === $this->paymentSourceType) {
                $userIdToken = $this->method->generateVaultUserIdToken((string) $paypalVaulting->paypal_customer_id);

                if (empty($userIdToken)) {
                    continue;
                }

                $collection .= (new VaultedPaymentButton($userIdToken))->render();
            }
        }

        return $collection;
    }
}
