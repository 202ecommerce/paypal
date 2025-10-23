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

namespace PaypalAddons\classes\WhiteList;

use PaypalAddons\classes\Constants\WhiteList;
use Symfony\Component\HttpFoundation\Request;

if (!defined('_PS_VERSION_')) {
    exit;
}

class WhiteListService
{
    public function isEnabled()
    {
        return (bool) \Configuration::get(WhiteList::ENABLED);
    }

    public function setEnabled($isEnabled)
    {
        \Configuration::updateValue(WhiteList::ENABLED, (int) $isEnabled);

        return $this;
    }

    public function getListIP()
    {
        $list = \Configuration::get(WhiteList::LIST_IP);

        if (empty($list)) {
            return [];
        }

        $list = json_decode($list, true);

        if (empty($list)) {
            return [];
        }

        return array_filter($list, function ($ip) { return false === empty($ip); });
    }

    public function setListIP($list)
    {
        if (false == is_array($list)) {
            return $this;
        }

        \Configuration::updateValue(WhiteList::LIST_IP, json_encode($list));

        return $this;
    }

    public function isEligibleContext()
    {
        if (empty($this->getListIP())) {
            return true;
        }

        $request = Request::createFromGlobals();

        return in_array($request->getClientIp(), $this->getListIP()) || defined('_PS_ADMIN_DIR_');
    }
}
