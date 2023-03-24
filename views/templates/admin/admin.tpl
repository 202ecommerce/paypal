{**
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
 *
 *}

{if isset($isModeSandbox) && $isModeSandbox}
  <style>
    .page-head {
      background-color: #585757 !important;
    }

    .bootstrap .page-head .toolbarBox .btn-toolbar .toolbar_btn i {
      color: #fff !important;
    }

    .bootstrap .page-head ul.breadcrumb {
      color: #fff !important;
    }

    .bootstrap .page-head ul.page-breadcrumb li:before {
      color: #fff !important;
    }

    .bootstrap .page-head ul.page-breadcrumb li a {
      color: #fff !important;
    }

    .page-head .wrapper h1 {
      color: #fff !important;
    }

    .bootstrap .page-head .toolbarBox .btn-toolbar .toolbar_btn {
      color: #fff !important;
    }

    {if version_compare($psVersion, '1.7.4', '<')}

    .page-head .page-title {
      color: #fff !important;
    }

    .page-head .page-head-tabs a{
      color: #fff !important;
    }

    .page-head .page-head-tabs a.current{
      border-bottom: 3px solid #fff !important;
    }

    {/if}


  </style>
{/if}

{block name='content'}
    {if isset($content)}
        {$content nofilter}
    {/if}
{/block}

