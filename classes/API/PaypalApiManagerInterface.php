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

namespace PaypalAddons\classes\API;

use PaypalAddons\classes\API\Request\RequestInteface;

interface PaypalApiManagerInterface
{
    /**
     * @return RequestInteface
     */
    public function getAccessTokenRequest();

    /**
     * @return RequestInteface
     */
    public function getOrderRequest();

    /**
     * @return RequestInteface
     */
    public function getOrderCaptureRequest($idPayment);

    /**
     * @return RequestInteface
     */
    public function getOrderAuthorizeRequest($idPayment);

    /**
     * @return RequestInteface
     */
    public function getOrderRefundRequest(\PaypalOrder $paypalOrder);

    /**
     * @return RequestInteface
     */
    public function getOrderPartialRefundRequest(\PaypalOrder $paypalOrder, $amount);

    /**
     * @return RequestInteface
     */
    public function getAuthorizationVoidRequest(\PaypalOrder $orderPayPal);

    /**
     * @return RequestInteface
     */
    public function getCaptureAuthorizeRequest(\PaypalOrder $paypalOrder);

    /**
     * @return RequestInteface
     */
    public function getOrderGetRequest($idPayment);

    /**
     * @return RequestInteface
     */
    public function getOrderPatchRequest($idPayment);
}
