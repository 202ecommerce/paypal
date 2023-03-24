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

require_once dirname(__FILE__) . '/../classes/PaypalIpn.php';

class ServicePaypalIpn
{
    /**
     * @param $idTransaction string
     * @param $status string
     *
     * @return bool
     */
    public function exists($idTransaction, $status)
    {
        $collection = new \PrestaShopCollection(\PaypalIpn::class);
        $collection->where('id_transaction', '=', pSQL($idTransaction));
        $collection->where('status', '=', pSQL($status));

        return (bool) $collection->count();
    }

    /**
     * @param $idTransaction string
     *
     * @return array of the Prestashop Order objects
     */
    public function getOrdersPsByTransaction($idTransaction)
    {
        $cart = $this->getCartByTransaction($idTransaction);

        if (\Validate::isLoadedObject($cart) == false) {
            return [];
        }

        $orderCollection = new \PrestaShopCollection(\Order::class);
        $orderCollection->where('id_cart', '=', (int) $cart->id);

        return $orderCollection->getResults();
    }

    /**
     * @param $idTransaction string
     *
     * @return \Cart
     */
    public function getCartByTransaction($idTransaction)
    {
        if ($idCart = $this->getIdCartByTransaction($idTransaction)) {
            $cart = new \Cart((int) $idCart);
            if (\Validate::isLoadedObject($cart)) {
                return $cart;
            }
        }

        return false;
    }

    /**
     * @param $idTransaction string
     *
     * @return int
     */
    public function getIdCartByTransaction($idTransaction)
    {
        $query = new \DbQuery();
        $query->from('paypal_order');
        $query->select('id_cart');
        $query->where('id_transaction = "' . pSQL($idTransaction) . '"');

        return (int) \Db::getInstance()->getValue($query);
    }
}
