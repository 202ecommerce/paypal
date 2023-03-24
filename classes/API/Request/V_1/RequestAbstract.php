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

namespace PaypalAddons\classes\API\Request\V_1;

use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;
use PaypalAddons\classes\AbstractMethodPaypal;
use PaypalAddons\classes\API\Request\RequestInteface;
use PaypalAddons\services\FormatterPaypal;

abstract class RequestAbstract implements RequestInteface
{
    /** @var AbstractMethodPaypal */
    protected $method;

    /** @var FormatterPaypal */
    protected $formatter;

    public function __construct(AbstractMethodPaypal $method)
    {
        $this->method = $method;
        $this->formatter = new FormatterPaypal();
    }

    /**
     * @return ApiContext
     */
    public function getApiContext($mode_order = null)
    {
        if ($mode_order === null) {
            $mode_order = $this->method->isSandbox();
        }

        $apiContext = new ApiContext(
            new OAuthTokenCredential(
                $this->method->getClientId($mode_order),
                $this->method->getSecret($mode_order)
            )
        );

        $apiContext->setConfig(
            [
                'mode' => $mode_order ? 'sandbox' : 'live',
                'log.LogEnabled' => false,
                'cache.enabled' => true,
            ]
        );

        return $apiContext;
    }
}
