{if empty($fileContent) && empty($dbContent)}
    {l s='Log unavailable' mod='paypal'}
{elseif !empty($fileContent)}
  <pre>{$fileContent|escape:'html':'UTF-8'}</pre>
{elseif !empty($dbContent)}
  <table class="table border">
    <thead>
    {foreach $dbContent.headers as $header}
      <th>{$header|escape:'html':'UTF-8'}</th>
    {/foreach}
    </thead>
    <tbody>
    {foreach $dbContent.content as $line}
        <tr>
          {foreach $line as $name => $value}
            <td>{$value|escape:'html':'UTF-8'}</td>
          {/foreach}
        </tr>
    {/foreach}
    </tbody>
  </table>
{/if}