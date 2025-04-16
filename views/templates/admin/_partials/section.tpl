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
{assign var="sectionRowClasses" value=$sectionRowClasses|default:' mt-4'}
{assign var="sectionColFormClasses" value=$sectionColFormClasses|default:' col-xl-6'}
{assign var="sectionColInfoClasses" value=$sectionColInfoClasses|default:' col-xl-6'}
{assign var="isModal" value=$isModal|default:false}

<section class="row{$sectionRowClasses} {[
  'd-none' => !$isModal
]|classnames}" data-section-configuration>
<div class="col col-12 {if $form.help && $form.help != ''}col-md-8{else}col-md-12{/if}{$sectionColFormClasses}">
   <div class="card">
   {if !$isModal}
      <div class="card-header">
        {$form.legend.title|escape:'htmlall':'UTF-8'}
      </div>
    {/if}
    <div class="card-body {[
      'pr-0' => $isModal
    ]|classnames}">
      {if $isModal}
        <div class="h1">{$form.legend.title|escape:'htmlall':'UTF-8'}</div>
      {/if}
        <div form-container="{$form.id_form|escape:'htmlall':'UTF-8'}">
            {if $form.id_form != 'pp_white_list_form'}
                {include file=$moduleFullDir|cat:"/views/templates/admin/_partials/forms/"|cat:$form.id_form|cat:".tpl" form=$form}
            {else}
                {include file=$moduleFullDir|cat:"/views/templates/admin/_partials/forms/form.tpl" form=$form}
            {/if}
        </div>
     </div>
   </div>
 </div>

 {if $form.help && $form.help != ''}
  <div class="col col-12 col-md-4{$sectionColInfoClasses|escape:'htmlall':'UTF-8'}">
    <div class="card-body">
      {$form.help|default:'' nofilter}
    </div>
  </div>
 {/if}
</section>
