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

namespace PaypalAddons\classes\API\Token;

if (!defined('_PS_VERSION_')) {
    exit;
}

use PaypalAddons\classes\AbstractMethodPaypal;
use PaypalAddons\classes\API\TokenInterface;

class BasicToken implements TokenInterface
{
    /** @var AbstractMethodPaypal */
    protected $method;

    public function __construct(AbstractMethodPaypal $method)
    {
        $this->method = $method;
    }

    /** @return bool*/
    public function isEligible()
    {
        if (empty($this->getClientId()) || empty($this->getClientSecret())) {
            return false;
        }

        return true;
    }

    /** @return string*/
    public function getToken()
    {
        return base64_encode(implode(':', [$this->getClientId(), $this->getClientSecret()]));
    }

    /**
     * @param mixed $data
     *
     * @return bool
     */
    public function update($data)
    {
        return true;
    }

    /** @return string*/
    protected function getClientId()
    {
        return $this->method->getClientId();
    }

    /** @return string*/
    protected function getClientSecret()
    {
        return $this->method->getSecret();
    }
}
