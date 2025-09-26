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

namespace PaypalAddons\services;

use PaypalAddons\classes\AbstractMethodPaypal;
use PaypalAddons\classes\API\Model\VaultInfo;

require_once dirname(__FILE__) . '/../classes/PaypalVaulting.php';

if (!defined('_PS_VERSION_')) {
    exit;
}

class ServicePaypalVaulting
{
    protected $db;

    protected $method;

    public function __construct($method = null)
    {
        $this->db = \Db::getInstance();

        if ($method instanceof AbstractMethodPaypal) {
            $this->method = $method;
        } else {
            $this->method = AbstractMethodPaypal::load();
        }
    }

    /**
     * @param $idCustomer integer id of the Prestashop Customer object
     * @param $rememberedCards string hash of the remembered card ids
     * @param $mode bool mode of the payment (sandbox or live)
     *
     * @return bool
     */
    public function saveRememberedCards($idCustomer, $rememberedCards, $mode = null)
    {
        if ($mode === null) {
            $mode = (int) \Configuration::get('PAYPAL_SANDBOX');
        }

        $paypalVaultingObject = $this->getPaypalVaultingByIdCustomer($idCustomer, $mode);

        if (is_object($paypalVaultingObject) == false || \Validate::isLoadedObject($paypalVaultingObject) == false) {
            $paypalVaultingObject = new \PaypalVaulting();
            $paypalVaultingObject->id_customer = $idCustomer;
            $paypalVaultingObject->sandbox = (int) $mode;
            $paypalVaultingObject->profile_key = $this->getProfileKey((int) $mode);
        }

        $paypalVaultingObject->rememberedCards = $rememberedCards;
        try {
            return $paypalVaultingObject->save();
        } catch (\Throwable $e) {
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @return \PaypalVaulting|null
     */
    public function addVault($idCustomer, VaultInfo $vaultInfo, $mode = null)
    {
        if ($mode === null) {
            $mode = (int) \Configuration::get('PAYPAL_SANDBOX');
        }

        $idPaypalVaulting = (int) $this->db->getValue(
            (new \DbQuery())
                ->from(\PaypalVaulting::$definition['table'])
                ->where(sprintf('vault_id = "%s"', pSQL($vaultInfo->getVaultId())))
                ->where(sprintf('paypal_customer_id = "%s"', pSQL($vaultInfo->getCustomerId())))
        );

        $paypalVaultingObject = new \PaypalVaulting($idPaypalVaulting);
        $paypalVaultingObject->id_customer = (int) $idCustomer;
        $paypalVaultingObject->sandbox = (int) $mode;
        $paypalVaultingObject->profile_key = $this->getProfileKey((int) $mode);
        $paypalVaultingObject->vault_id = $vaultInfo->getVaultId();
        $paypalVaultingObject->paypal_customer_id = $vaultInfo->getCustomerId();
        $paypalVaultingObject->payment_source = $vaultInfo->getPaymentSource();

        try {
            $paypalVaultingObject->save();
        } catch (\Throwable $e) {
            return null;
        } catch (\Exception $e) {
            return null;
        }

        return $paypalVaultingObject;
    }

    /**
     * @param $idCustomer integer id of the Prestashop Customer object
     * @param $mode bool mode of the payment (sandbox or live)
     *
     * @return string
     */
    public function getRememberedCardsByIdCustomer($idCustomer, $mode = null)
    {
        if ($mode === null) {
            $mode = (int) \Configuration::get('PAYPAL_SANDBOX');
        }

        $paypalVaultingObject = $this->getPaypalVaultingByIdCustomer($idCustomer, $mode);

        if (is_object($paypalVaultingObject) == false || \Validate::isLoadedObject($paypalVaultingObject) == false) {
            return '';
        }

        return $paypalVaultingObject->rememberedCards;
    }

    /**
     * @param $idCustomer integer id of the Prestashop Customer object
     * @param $mode bool mode of the payment (sandbox or live)
     *
     * @return \PaypalVaulting object or false
     */
    public function getPaypalVaultingByIdCustomer($idCustomer, $mode = null)
    {
        if ($mode === null) {
            $mode = (int) \Configuration::get('PAYPAL_SANDBOX');
        }

        $collection = new \PrestaShopCollection(\PaypalVaulting::class);
        $collection->where('id_customer', '=', (int) $idCustomer);
        $collection->where('sandbox', '=', (int) $mode);
        $collection->where('profile_key', '=', $this->getProfileKey((int) $mode));

        return $collection->getFirst();
    }

    public function getVaultListByCustomer($idCustomer, $mode = null)
    {
        if ($mode === null) {
            $mode = (int) \Configuration::get('PAYPAL_SANDBOX');
        }

        $query = new \DbQuery();
        $query->from(\PaypalVaulting::$definition['table']);
        $query->where('id_customer = ' . (int) $idCustomer);
        $query->where('sandbox = ' . (int) $mode);
        $query->where(sprintf('profile_key = "%s"', pSQL($this->getProfileKey((int) $mode))));
        $query->where('vault_id <> ""');
        $query->where('vault_id IS NOT NULL');

        $result = $this->db->executeS($query);
        $vaultList = [];

        if (empty($result)) {
            return $vaultList;
        }

        foreach ($result as $row) {
            $obj = new \PaypalVaulting();
            $obj->hydrate($row);
            $vaultList[] = $obj;
        }

        return $vaultList;
    }

    protected function getProfileKey($mode)
    {
        return md5($this->method->getClientId((int) $mode));
    }
}
