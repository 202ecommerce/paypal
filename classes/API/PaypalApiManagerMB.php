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

use PaypalAddons\classes\AbstractMethodPaypal;
use PaypalAddons\classes\API\Request\RequestDummy;
use PaypalAddons\classes\API\Request\V_1\CreateProfileExperienceRequest;
use PaypalAddons\classes\API\Request\V_1\PaypalOrderCaptureRequest;
use PaypalAddons\classes\API\Request\V_1\PaypalOrderCreateRequest;
use PaypalAddons\classes\API\Request\V_1\PaypalOrderPartialRefundRequest;
use PaypalAddons\classes\API\Request\V_1\PaypalOrderRefundRequest;

class PaypalApiManagerMB implements PaypalApiManagerInterface
{
    /** @var AbstractMethodPaypal */
    protected $method;

    public function __construct(AbstractMethodPaypal $method)
    {
        $this->method = $method;
    }

    /**
     * @return CreateProfileExperienceRequest
     */
    public function getAccessTokenRequest()
    {
        return new CreateProfileExperienceRequest($this->method);
    }

    /**
     * @return PaypalOrderCreateRequest
     */
    public function getOrderRequest()
    {
        return new PaypalOrderCreateRequest($this->method);
    }

    /**
     * @return PaypalOrderCaptureRequest
     */
    public function getOrderCaptureRequest($idPayment)
    {
        return new PaypalOrderCaptureRequest($this->method);
    }

    /**
     * @return RequestDummy
     */
    public function getOrderAuthorizeRequest($idPayment)
    {
        return new RequestDummy();
    }

    /**
     * @return PaypalOrderRefundRequest
     */
    public function getOrderRefundRequest(\PaypalOrder $paypalOrder)
    {
        return new PaypalOrderRefundRequest($this->method, $paypalOrder);
    }

    /**
     * @return PaypalOrderPartialRefundRequest
     */
    public function getOrderPartialRefundRequest(\PaypalOrder $paypalOrder, $amount)
    {
        return new PaypalOrderPartialRefundRequest($this->method, $paypalOrder, $amount);
    }

    /**
     * @return RequestDummy
     */
    public function getAuthorizationVoidRequest(\PaypalOrder $orderPayPal)
    {
        return new RequestDummy();
    }

    /**
     * @return RequestDummy
     */
    public function getCaptureAuthorizeRequest(\PaypalOrder $paypalOrder)
    {
        return new RequestDummy();
    }

    /**
     * @return RequestDummy
     */
    public function getOrderGetRequest($idPayment)
    {
        return new RequestDummy();
    }

    /**
     * @return RequestDummy
     */
    public function getOrderPatchRequest($idPayment)
    {
        return new RequestDummy();
    }
}
