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

<table class="table">
  <thead>
    <tr>
      <th><b>{l s='PrestaShop carrier' mod='paypal'}</b></th>
      <th><b>{l s='PayPal carrier' mod='paypal'}</b></th>
    </tr>
  </thead>
  <tbody>
  {if isset($carriers) && isset($mapService)}
    {foreach from=$carriers item=carrier}
        {assign var='selectedCarrier' value=$mapService->getPaypalCarrierByPsCarrier($carrier['id_reference'])}
        <tr>
          <td>{$carrier['name']|escape:'htmlall':'UTF-8'}</td>
          <td>
            <select name="carrier_map[]" id="">
              <option value="0">{l s='Select carrier' mod='paypal'}</option>
              {foreach from=$mapService->getPaypalCarriersByCountry() item=paypalCarrier}
                <option
                        value="{$carrier['id_reference']|escape:'htmlall':'UTF-8'},{$paypalCarrier['key']|escape:'htmlall':'UTF-8'}"
                        {if $selectedCarrier == $paypalCarrier['key']}selected{/if}>

                    {$paypalCarrier['name']|escape:'htmlall':'UTF-8'}

                </option>
              {/foreach}
            </select>
          </td>
        </tr>
    {/foreach}
  {/if}

  </tbody>
</table>
