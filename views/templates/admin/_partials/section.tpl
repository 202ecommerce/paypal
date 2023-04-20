{*
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
{assign var="sectionRowClasses" value=$sectionRowClasses|default:' mt-4'}
{assign var="sectionColFormClasses" value=$sectionColFormClasses|default:' col-md-6'}
{assign var="sectionColInfoClasses" value=$sectionColInfoClasses|default:' col-md-6'}

<div class="row{$sectionRowClasses}">
 <div class="col{$sectionColFormClasses}">
   <div class="card">
     <div class="card-header">
      {$form.legend.title}
     </div>
     <div class="card-body">
      {include file="module:paypal/views/templates/admin/_partials/forms/"|cat:$form.id_form|cat:".tpl" form=$form}
     </div>
   </div>
 </div>

 <div class="col{$sectionColInfoClasses}">
   <div class="card-body">
    {include file="module:paypal/views/templates/admin/_partials/block-info.tpl"}
   </div>
 </div>
</div>
