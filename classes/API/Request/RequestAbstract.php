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

namespace PaypalAddons\classes\API\Request;

use PaypalAddons\classes\AbstractMethodPaypal;
use PaypalAddons\services\FormatterPaypal;
use PaypalAddons\services\PaypalContext;
use PayPalCheckoutSdk\Core\PayPalHttpClient;

abstract class RequestAbstract implements RequestInteface
{
    /** PayPalHttpClient*/
    protected $client;

    /** @var \Context */
    protected $context;

    /** @var AbstractMethodPaypal */
    protected $method;

    /** @var \Module */
    protected $module;

    /** @var FormatterPaypal */
    protected $formatter;

    /** @var PaypalContext */
    protected $paypalContext;

    public function __construct(PayPalHttpClient $client, AbstractMethodPaypal $method)
    {
        $this->client = $client;
        $this->method = $method;
        $this->context = \Context::getContext();
        $this->module = \Module::getInstanceByName($method->name);
        $this->formatter = new FormatterPaypal();
        $this->paypalContext = PaypalContext::getContext();
    }

    /**
     * @return array
     */
    protected function getHeaders()
    {
        $headers = [
            'PayPal-Partner-Attribution-Id' => $this->method->getPaypalPartnerId(),
        ];

        return $headers;
    }

    abstract public function execute();
}
