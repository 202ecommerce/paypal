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

/*
 * Smarty modifier to replace HTML tags in translations.
 * @usage {{l='test'}|paypalreplace}
 * @param.value string
 * @param.name string
 */

if (!function_exists('smarty_modifier_paypalreplace')) {
    function smarty_modifier_paypalreplace($string, $replaces = [])
    {
        $search = [
            '[b]',
            '[/b]',
            '[br]',
            '[em]',
            '[/em]',
            '[a @href1@]',
            '[a @href2@]',
            '[/a]',
            '[small]',
            '[/small]',
            '[strong]',
            '[/strong]',
            '[i]',
            '[/i]',
        ];
        $replace = [
            '<b>',
            '</b>',
            '<br>',
            '<em>',
            '</em>',
            '<a href="@href1@" @target@>',
            '<a href="@href2@" @target@>',
            '</a>',
            '<small>',
            '</small>',
            '<strong>',
            '</strong>',
            '<i>',
            '</i>',
        ];
        $string = str_replace($search, $replace, $string);
        foreach ($replaces as $k => $v) {
            $string = str_replace($k, $v, $string);
        }
        $string = str_replace(' @target@', '', $string);

        return $string;
    }
}
