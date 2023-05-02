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
<form id="{$form.id_form}" class="mt-4 {[
  'form-modal' => $isModal
]|classnames}">
  {block name='form_content'}
    {foreach from=$form.fields item=field}
      {if $field.name|default:false}
        {include file="module:paypal/views/templates/admin/_partials/form-fields.tpl" field=$field}
      {/if}
    {/foreach}
  {/block}

  {block name='form_footer'}
    <div class="form-group mb-0 d-flex justify-content-between pt-3 mt-auto">
      {block name='form_footer_buttons'}
        {if $isModal}
          <div class="d-flex justify-content-between flex-fill mr-3">
            <button data-btn-action="prev" class="btn btn-secondary d-none">{l s='Back' mod='paypal'}</button>
            <button data-btn-action="next" class="btn btn-outline-primary">{l s='Skip this step' mod='paypal'}</button>
          </div>
        {/if}
        <button data-btn-action="next" save-form class="btn btn-secondary ml-auto" name={$form.submit.name}>{$form.submit.title}</button>
      {/block}
    </div>
  {/block}
</form>
