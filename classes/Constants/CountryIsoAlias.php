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

namespace PaypalAddons\classes\Constants;

if (!defined('_PS_VERSION_')) {
    exit;
}

class CountryIsoAlias
{
    /**
     * PrestaShop and PayPal can use different ISO code for some countries
     * This method returns map <PrestaShop iso> => <PayPal iso>
     */
    public static function getAliasList()
    {
        return [
            'AE' => 'ARE',
            'AR' => 'ARG',
            'AT' => 'AUT',
            'AU' => 'AUS',
            'BA' => 'BIH',
            'BE' => 'BEL',
            'BG' => 'BGR',
            'BR' => 'BRA',
            'CA' => 'CAN',
            'CH' => 'CHE',
            'CL' => 'CHL',
            'CN' => 'CHN',
            'CO' => 'COL',
            'CY' => 'CYP',
            'CZ' => 'CZE',
            'DE' => 'DEU',
            'DK' => 'DNK',
            'EE' => 'EST',
            'ES' => 'ESP',
            'FI' => 'FIN',
            'FR' => 'FRA',
            'GB' => 'GBR',
            'GR' => 'GRC',
            'HK' => 'HKG',
            'HR' => 'HRV',
            'HU' => 'HUN',
            'ID' => 'IDN',
            'IE' => 'IRL',
            'IL' => 'ISR',
            'IN' => 'IND',
            'IT' => 'ITA',
            'JE' => 'JEY',
            'JP' => 'JPN',
            'KH' => 'KHM',
            'KR' => 'KOR',
            'LA' => 'LAO',
            'LT' => 'LTU',
            'LV' => 'LVA',
            'MX' => 'MEX',
            'MY' => 'MYS',
            'NG' => 'NGA',
            'NL' => 'NLD',
            'NO' => 'NOR',
            'NZ' => 'NZL',
            'OM' => 'OMN',
            'PA' => 'PAN',
            'PH' => 'PHL',
            'PK' => 'PAK',
            'PL' => 'POL',
            'PT' => 'PRT',
            'PY' => 'PRY',
            'RO' => 'ROU',
            'RS' => 'SRB',
            'RU' => 'RUS',
            'SA' => 'SAU',
            'SE' => 'SWE',
            'SG' => 'SGP',
            'SI' => 'SVN',
            'SK' => 'SVK',
            'TH' => 'THA',
            'TR' => 'TUR',
            'TW' => 'TWN',
            'UA' => 'UKR',
            'US' => 'USA',
            'UY' => 'URY',
            'VE' => 'VEN',
            'VN' => 'VNM',
            'ZA' => 'ZAF',
        ];
    }
}
