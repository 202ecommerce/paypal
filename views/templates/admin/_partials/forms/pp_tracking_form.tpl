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
{extends file="./form.tpl"}

{block name='form_content'}
  {foreach from=$form.fields item=field key=key}
    {if $field.name|default:false && $field.type == 'select'}
      {include file="../form-fields.tpl" field=$field}
    {elseif $key == 'carrier_map'}
      <div class="form-group row pt-4">
        <label class="form-control-label form-control-label-check col-3" for="PAYPAL_CARRIER_MAP">{$field.label}</label>
        <div class="col-9 col-lg-8">
          <table class="table">
            <thead>
              <th>{l s='PrestaShop carrier' mod='paypal'}</th>
              <th>{l s='PayPal carrier' mod='paypal'}</th>
            </thead>
            <tbody>
              {foreach from=$field.set.carriers item=carrier}
                <tr>
                  <td>{$carrier.name|escape:'htmlall':'UTF-8'}</td>
                  <td>
                    {assign var="optionsPaypalCarrier" value=[]}
                    {assign var="selectedCarrier" value=$carrier['id_reference']|cat:','|cat:$field.set.mapService->getPaypalCarrierByPsCarrier($carrier['id_reference'])}
                    {foreach from=$field.set.mapService->getPaypalCarriersByCountry() item=paypalCarrier}
                      {$optionsPaypalCarrier[] = [
                        'value' => $carrier['id_reference']|cat:','|cat:$paypalCarrier.key,
                        'title' => $paypalCarrier.name
                      ]}
                    {/foreach}
                    {include
                      file="../form-fields.tpl"
                      field=[
                        'type' => 'select',
                        'name' => 'carrier_map[]',
                        'label' => false,
                        'options' => $optionsPaypalCarrier,
                        'variant' => 'primary',
                        'value' => $selectedCarrier
                      ]
                    }
                  </td>
                </tr>
              {/foreach}
            </tbody>
          </table>
        </div>
      </div>
    {/if}
  {/foreach}
{/block}
