{*
 * since 2007 PayPal
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
 *  @author since 2007 PayPal
 *  @author 202 ecommerce <tech@202-ecommerce.com>
 *  @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 *  @copyright PayPal
 *
 *}
{* Dashboard *}
{include
  file=$moduleFullDir|cat:"/views/templates/admin/_partials/dashboard.tpl"
  form=$trackingForm
}

{if $isShowModalConfiguration|default:false}
  {include
    file=$moduleFullDir|cat:"/views/templates/admin/_partials/modal-configuration.tpl"
  }
{else}

  {* Account section *}
    {if isset($accountForm)}
        {include
          file=$moduleFullDir|cat:"/views/templates/admin/_partials/section.tpl"
          form=$accountForm
        }
    {/if}

  {* Tracking section *}
  {if isset($trackingForm)}
      {include
      file=$moduleFullDir|cat:"/views/templates/admin/_partials/section.tpl"
      form=$trackingForm
      }
  {/if}

  {* Checkout section *}
  {if isset($checkoutForm)}
      {include
        file=$moduleFullDir|cat:"/views/templates/admin/_partials/section.tpl"
        form=$checkoutForm
      }
  {/if}


  {* BNPL Button section *}
    {if isset($formInstallment)}
        {include
        file=$moduleFullDir|cat:"/views/templates/admin/_partials/section.tpl"
        form=$formInstallment
        sectionColFormClasses=' '
        sectionColInfoClasses=' '
        }
    {/if}

  {* BNPL Installment section *}
    {if isset($formInstallmentMessaging)}
        {include
        file=$moduleFullDir|cat:"/views/templates/admin/_partials/section.tpl"
        form=$formInstallmentMessaging
        sectionColFormClasses=' '
        sectionColInfoClasses=' '
        }
    {/if}

    {* Shortcut section *}
  {if isset($shortcutConfigurationForm)}
      {include
      file=$moduleFullDir|cat:"/views/templates/admin/_partials/section.tpl"
      form=$shortcutConfigurationForm
      }
  {/if}


  {* Order status section *}
  {if isset($orderStatusForm)}
      {include
      file=$moduleFullDir|cat:"/views/templates/admin/_partials/section.tpl"
      form=$orderStatusForm
      }
  {/if}

  {* White list section *}
  {if isset($whiteListForm)}
      {include
      file=$moduleFullDir|cat:"/views/templates/admin/_partials/section.tpl"
      form=$whiteListForm
      }
  {/if}

  {* PrestaShop CloudSync section *}
  {if isset($cloudSyncForm)}
    {include
    file=$moduleFullDir|cat:"/views/templates/admin/_partials/section.tpl"
    form=$cloudSyncForm
    }
  {/if}


{/if}
