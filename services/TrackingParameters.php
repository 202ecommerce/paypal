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

use Configuration;
use Country;
use Exception;
use PaypalAddons\classes\Constants\TrackingParameters as Map;
use PrestaShopLogger;
use Throwable;

class TrackingParameters
{
    /** @var array */
    protected $carrierMap;
    /** @var array */
    protected $paypalCarriers = [];
    /** @var Country */
    protected $defaultCountry;

    public function __construct()
    {
        $this->initDefaultCountry();
        $this->initCarrierMap();
        $this->initPaypalCarrierList();
    }

    public function getPaypalCarriers()
    {
        return $this->paypalCarriers;
    }

    public function getPaypalCarriersByCountry($isoCountry = null)
    {
        $carriers = [
            [
                'key' => Map::CARRIER_OTHER,
                 'name' => Map::CARRIER_OTHER,
            ],
        ];

        if ($isoCountry === null) {
            $isoCountry = $this->defaultCountry->iso_code;
        }

        if (empty($this->paypalCarriers[strtoupper($isoCountry)])) {
            return $carriers;
        }

        return array_merge($carriers, $this->paypalCarriers[strtoupper($isoCountry)]);
    }

    public function getPaypalCarrierByPsCarrier($carrierRef)
    {
        if (empty($this->carrierMap[$carrierRef])) {
            return false;
        }

        return $this->carrierMap[$carrierRef];
    }

    public function setCarrierMap(array $map)
    {
        $this->carrierMap = [
            $this->defaultCountry->iso_code => $map,
        ];
        $this->updateCarrierMap();

        return $this;
    }

    protected function updateCarrierMap()
    {
        try {
            return Configuration::updateValue(Map::CARRIER_MAP, json_encode($this->carrierMap));
        } catch (Throwable $e) {
            PrestaShopLogger::addLog('[paypal][TrackingParameters::updateCarrierMap()] Error: ' . $e->getMessage());

            return false;
        } catch (Exception $e) {
            PrestaShopLogger::addLog('[paypal][TrackingParameters::updateCarrierMap()] Error: ' . $e->getMessage());

            return false;
        }
    }

    public function getStatus()
    {
        $status = Configuration::get(Map::STATUS);

        if ($status) {
            return $status;
        }

        return Map::STATUS_SHIPPED;
    }

    public function setStatus($status)
    {
        if ($this->isStatusValid($status)) {
            return Configuration::updateValue(Map::STATUS, $status);
        }

        return false;
    }

    public function getStatusList()
    {
        return Map::getPaypalStatusList();
    }

    public function isStatusValid($status)
    {
        if (false == is_string($status)) {
            return false;
        }

        foreach ($this->getStatusList() as $paypalStatus) {
            if ($paypalStatus['key'] == $status) {
                return true;
            }
        }

        return false;
    }

    protected function initDefaultCountry()
    {
        try {
            $this->defaultCountry = new Country(Configuration::get('PS_COUNTRY_DEFAULT'));
        } catch (Throwable $e) {
            $this->defaultCountry = new Country();

            return false;
        } catch (Exception $e) {
            $this->defaultCountry = new Country();

            return false;
        }

        return true;
    }

    protected function initPaypalCarrierList()
    {
        try {
            $this->paypalCarriers = json_decode(\Tools::file_get_contents(_PS_MODULE_DIR_ . 'paypal/paypal-carriers.json'), true);
        } catch (Throwable $e) {
            return false;
        } catch (Exception $e) {
            return false;
        }

        return true;
    }

    protected function initCarrierMap()
    {
        try {
            $carrierMap = json_decode(Configuration::get(Map::CARRIER_MAP), true);
        } catch (Throwable $e) {
            $this->carrierMap = [];

            return false;
        } catch (Exception $e) {
            $this->carrierMap = [];

            return false;
        }

        if (empty($carrierMap[$this->defaultCountry->iso_code])) {
            $carrierMap = [];
        } else {
            $carrierMap = $carrierMap[$this->defaultCountry->iso_code];
        }

        $this->carrierMap = $carrierMap;

        return true;
    }
}
