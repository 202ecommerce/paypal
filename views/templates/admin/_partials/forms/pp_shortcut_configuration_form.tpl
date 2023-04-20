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
<form id="{$shortcutConfigurationForm.id_form}" class="mt-4">
  {foreach from=$shortcutConfigurationForm.fields item=field}
    {if $field.name|default:false}
      <div class="form-group">
        <label class="form-control-label col-lg-3 {[
          'form-control-label-check' => $field.type == 'switch'
        ]|classnames}" for="{$field.name}">{$field.label}</label>
        <div class="col-lg-7">
          {include file="module:paypal/views/templates/admin/_partials/form-fields.tpl" field=$field}
        </div>
      </div>
    {/if}
  {/foreach}

  <div class="form-group pt-5 mb-0">
    <button class="btn btn-secondary ml-auto" name={$shortcutConfigurationForm.submit.name}>{$shortcutConfigurationForm.submit.title}</button>
  </div>
</form>
