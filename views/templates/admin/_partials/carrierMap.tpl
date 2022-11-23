
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
          <td>{$carrier['name']|escape:'htmlall':'utf-8'}</td>
          <td>
            <select name="carrier_map[]" id="">
              <option value="0">{l s='Select carrier' mod='paypal'}</option>
              {foreach from=$mapService->getPaypalCarriersByCountry() item=paypalCarrier}
                <option
                        value="{$carrier['id_reference']|escape:'htmlall':'utf-8'},{$paypalCarrier['key']|escape:'htmlall':'utf-8'}"
                        {if $selectedCarrier == $paypalCarrier['key']}selected{/if}>

                    {$paypalCarrier['name']|escape:'htmlall':'utf-8'}

                </option>
              {/foreach}
            </select>
          </td>
        </tr>
    {/foreach}
  {/if}

  </tbody>
</table>
