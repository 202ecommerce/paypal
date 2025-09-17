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

class OAuthToken implements TokenInterface
{
    /** @var array */
    protected $token;
    /** @var AbstractMethodPaypal */
    protected $method;

    public function __construct(AbstractMethodPaypal $method)
    {
        $token = json_decode((string) \Configuration::get(\PayPal::ACCESS_TOKEN), true);
        $this->method = $method;

        if (!empty($token['client-id']) && $token['client-id'] === $method->getClientId()) {
            $this->token = $token;
        }
    }

    /** @return bool*/
    public function isEligible()
    {
        if (empty($this->token['access_token'])) {
            return false;
        }

        if (empty($this->token['expires_in'])) {
            return false;
        }

        if (empty($this->token['date_add'])) {
            return false;
        }

        $expiredTime = $this->token['date_add'] + $this->token['expires_in'] - 50;

        return $expiredTime > time();
    }

    /** @return string*/
    public function getToken()
    {
        if (empty($this->token['access_token'])) {
            return '';
        }

        return $this->token['access_token'];
    }

    /**
     * @param mixed $data
     *
     * @return bool
     */
    public function update($data)
    {
        if (false === is_array($data)) {
            return false;
        }

        if (empty($data['access_token']) || empty($data['expires_in'])) {
            return false;
        }

        $data['date_add'] = time();
        $data['client-id'] = $this->method->getClientId();
        $this->token = $data;

        return \Configuration::updateValue(\PayPal::ACCESS_TOKEN, json_encode($data));
    }
}
