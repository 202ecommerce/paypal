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

namespace PaypalAddons\services;

use Exception;
use Throwable;

require_once dirname(__FILE__) . '/../classes/PaypalVaulting.php';

class ServicePaypalVaulting
{
    /**
     * @param $idCustomer integer id of the Prestashop Customer object
     * @param $rememberedCards string hash of the remembered card ids
     * @param $mode bool mode of the payment (sandbox or live)
     *
     * @return bool
     */
    public function createOrUpdatePaypalVaulting($idCustomer, $rememberedCards, $mode = null)
    {
        if ($mode === null) {
            $mode = (int) \Configuration::get('PAYPAL_SANDBOX');
        }

        $paypalVaultingObject = $this->getPaypalVaultingByIdCustomer($idCustomer, $mode);

        if (is_object($paypalVaultingObject) == false || \Validate::isLoadedObject($paypalVaultingObject) == false) {
            $paypalVaultingObject = new \PaypalVaulting();
            $paypalVaultingObject->id_customer = $idCustomer;
            $paypalVaultingObject->sandbox = (int) $mode;

            if ((int) $mode) {
                $profileKey = md5(\Configuration::get('PAYPAL_MB_SANDBOX_CLIENTID'));
            } else {
                $profileKey = md5(\Configuration::get('PAYPAL_MB_LIVE_CLIENTID'));
            }

            $paypalVaultingObject->profile_key = $profileKey;
        }

        $paypalVaultingObject->rememberedCards = $rememberedCards;
        try {
            return $paypalVaultingObject->save();
        } catch (Throwable $e) {
            return false;
        } catch (Exception $e) {
            return false;
        }
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

        if ((int) $mode) {
            $profileKey = md5(\Configuration::get('PAYPAL_MB_SANDBOX_CLIENTID'));
        } else {
            $profileKey = md5(\Configuration::get('PAYPAL_MB_LIVE_CLIENTID'));
        }

        $collection = new \PrestaShopCollection(\PaypalVaulting::class);
        $collection->where('id_customer', '=', (int) $idCustomer);
        $collection->where('sandbox', '=', (int) $mode);
        $collection->where('profile_key', '=', $profileKey);

        return $collection->getFirst();
    }
}
