{**
 * 2007-2024 PayPal
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
 *  @author 2007-2024 PayPal
 *  @author 202 ecommerce <tech@202-ecommerce.com>
 *  @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 *  @copyright PayPal
 *
 *}

{extends file='module:paypal/views/templates/acdc/payment-option.tpl'}

{block name='style'}
  <style>
    #cvv {
      min-width: 120px;
    }

    .pp-flex {
      display: flex;
    }

    .pp-space-between {
      justify-content: space-between;
    }

    .pp-center {
      justify-content: center;
    }

    .pp-field-wrapper {
      padding: 10px;
    }

    .pp-field-wrapper label {
      padding: 0 0 10px 0;
      font-weight: bold;
    }

    .pp-flex-direction-column {
      flex-direction: column;
    }

    [paypal-acdc-wrapper] {
      max-width: 300px;
    }

    .pp-padding-1 {
      padding: 10px
    }
  </style>
{/block}

