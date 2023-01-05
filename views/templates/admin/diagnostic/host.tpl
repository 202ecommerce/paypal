<div class="w-100 mb-3">
  <div class="row">
    <div class="col-sm-12">
      <div class="row justify-content-center">
        <div class="col-xl-12 pr-5 pl-5">
          <div class="card">
            <div class="card-header d-flex justify-content-between">
              <div>
                <span class="material-icons">dns</span>
                  {l s='Hosting conditions' mod='paypal'}
              </div>
                {if !$checks.failOptional && !$checks.failRequired}
                  <div class="badge-success px-2 mb-0">{l s='No hosting errors' mod='paypal'}</div>
                {else}
                  <div class="badge-warning px-2 mb-0">{l s='Found some hosting errors' mod='paypal'}</div>
                {/if}
            </div>
            <div class="form-wrapper justify-content-center col-xl-12 mt-3 {if !$checks.failOptional && !$checks.failRequired}d-none{/if}">
              <div class="mt-2 alert alert-info">{l s='Information about your hosting can help support to understand specific behaviour.' mod='paypal'}</div>
              <div class="row">
                <div class="col-6">
                  <div class="row">
                    <div class="col-12">
                      <h3>{l s='Server Information' mod='paypal'}</h3>
                      <table class="table border">
                        <thead></thead>
                        <tbody>
                        {if !empty($hostingInfo.uname)}
                          <tr>
                            <td class="font-weight-bold">{l s='Server information:' mod='paypal'}</td>
                            <td>{$hostingInfo.uname|escape:'html':'UTF-8'}</td>
                          </tr>
                        {/if}
                        <tr>
                          <td class="font-weight-bold">{l s='Server software version:' mod='paypal'}</td>
                          <td>{$hostingInfo.version.server|escape:'html':'UTF-8'}</td>
                        </tr>
                        <tr>
                          <td class="font-weight-bold">{l s='PHP version:' mod='paypal'}</td>
                          <td>{$hostingInfo.version.php|escape:'html':'UTF-8'}</td>
                        </tr>
                        <tr>
                          <td class="font-weight-bold">{l s='Memory limit:' mod='paypal'}</td>
                          <td>{$hostingInfo.version.memory_limit|escape:'html':'UTF-8'}</td>
                        </tr>
                        <tr>
                          <td class="font-weight-bold">{l s='Max execution time:' mod='paypal'}</td>
                          <td>{$hostingInfo.version.max_execution_time|escape:'html':'UTF-8'}</td>
                        </tr>
                        {if !empty($hostingInfo.apache_instaweb)}
                          <tr>
                            <td class="font-weight-bold">{l s='PageSpeed module for Apache installed (mod_instaweb)' mod='paypal'}</td>
                            <td>true</td>
                          </tr>
                        {/if}
                        </tbody>
                      </table>
                      <h3>{l s='Database information' mod='paypal'}</h3>
                      <table class="table border">
                        <thead></thead>
                        <tbody>
                        <tr>
                          <td class="font-weight-bold">{l s='MySQL version:' mod='paypal'}</td>
                          <td>{$hostingInfo.database.version|escape:'html':'UTF-8'}</td>
                        </tr>
                        <tr>
                          <td class="font-weight-bold">{l s='MySQL server:' mod='paypal'}</td>
                          <td>{$hostingInfo.database.server|escape:'html':'UTF-8'}</td>
                        </tr>
                        <tr>
                          <td class="font-weight-bold">{l s='MySQL name:' mod='paypal'}</td>
                          <td>{$hostingInfo.database.name|escape:'html':'UTF-8'}</td>
                        </tr>
                        <tr>
                          <td class="font-weight-bold">{l s='MySQL user:' mod='paypal'}</td>
                          <td>{$hostingInfo.database.user|escape:'html':'UTF-8'}</td>
                        </tr>
                        <tr>
                          <td class="font-weight-bold">{l s='Tables prefix:' mod='paypal'}</td>
                          <td>{$hostingInfo.database.prefix|escape:'html':'UTF-8'}</td>
                        </tr>
                        <tr>
                          <td class="font-weight-bold">{l s='MySQL engine:' mod='paypal'}</td>
                          <td>{$hostingInfo.database.engine|escape:'html':'UTF-8'}</td>
                        </tr>
                        <tr>
                          <td class="font-weight-bold">{l s='MySQL driver:' mod='paypal'}</td>
                          <td>{$hostingInfo.database.driver|escape:'html':'UTF-8'}</td>
                        </tr>

                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
                <div class="col-6">
                  <div class="row">
                    <div class="col-12">
                      <h3>{l s='Store information' mod='paypal'}</h3>
                      <table class="table border">
                        <thead></thead>
                        <tbody>
                        <tr>
                          <td class="font-weight-bold">{l s='PrestaShop version:' mod='paypal'}</td>
                          <td>{$shopInfo.shop.ps|escape:'html':'UTF-8'}</td>
                        </tr>
                        <tr>
                          <td class="font-weight-bold">{l s='Shop URL:' mod='paypal'}</td>
                          <td>{$shopInfo.shop.url|escape:'html':'UTF-8'}</td>
                        </tr>
                        <tr>
                          <td class="font-weight-bold">{l s='Current theme in use:' mod='paypal'}</td>
                          <td>{$shopInfo.shop.theme|escape:'html':'UTF-8'}</td>
                        </tr>
                        </tbody>
                      </table>

                      <h3>{l s='Mail configuration' mod='paypal'}</h3>
                      <table class="table border">
                        <thead></thead>
                        <tbody>
                        <tr>
                          <td class="font-weight-bold">{l s='Mail method:' mod='paypal'}</td>
                          <td>
                              {if $shopInfo.mail}
                                  {l s='You are using the PHP mail() function.' mod='paypal'}
                              {else}
                                  {l s='You are using your own SMTP parameters.' mod='paypal'}
                              {/if}
                          </td>
                        </tr>
                        {if !$shopInfo.mail}
                          <tr>
                            <td class="font-weight-bold">{l s='SMTP server' mod='paypal'}</td>
                            <td>{$shopInfo.smtp.server|escape:'html':'UTF-8'}</td>
                          </tr>
                          <tr>
                            <td class="font-weight-bold">{l s='SMTP username' mod='paypal'}</td>
                            <td>{if $shopInfo.smtp.user neq ''}
                                    {l s='Defined' mod='paypal'}
                                {else}
                                <span style="color:red;">{l s='Not defined' mod='paypal'}</span>
                                {/if}
                            </td>
                          <tr>
                            <td class="font-weight-bold">{l s='SMTP password' mod='paypal'}</td>
                            <td>
                                {if $shopInfo.smtp.password neq ''}
                                    {l s='Defined' mod='paypal'}
                                {else}
                                  <span style="color:red;">{l s='Not defined' mod='paypal'}</span>
                                {/if}
                            </td>
                          </tr>
                          <tr>
                            <td class="font-weight-bold">{l s='Encryption:' mod='paypal'}</td>
                            <td>{$shopInfo.smtp.encryption|escape:'html':'UTF-8'}</td>
                          </tr>
                          <tr>
                            <td class="font-weight-bold">{l s='SMTP port:' mod='paypal'}</td>
                            <td>{$shopInfo.smtp.port|escape:'html':'UTF-8'}</td>
                          </tr>
                        {/if}
                        </tbody>
                      </table>

                      <h3>{l s='Your information' mod='paypal'}</h3>
                      <table class="table border">
                        <thead></thead>
                        <tbody>
                        <tr>
                          <td class="font-weight-bold">{l s='Your web browser:' mod='paypal'}</td>
                          <td>{$shopInfo.user_agent|escape:'html':'UTF-8'}</td>
                        </tr>
                        </tbody>
                      </table>

                      <h3>{l s='Check your configuration' mod='paypal'}</h3>
                      <p>
                        <strong>{l s='Required parameters:' mod='paypal'}</strong>
                          {if !$checks.failRequired}
                        <span class="text-success">{l s='OK' mod='paypal'}</span>
                      </p>
                        {else}
                      <span class="text-danger">{l s='Please fix the following error(s)' mod='paypal'}</span>
                      </p>
                      <ul>
                          {foreach from=$checks.testsRequired item='value' key='key'}
                              {if $value eq 'fail' && isset($checks.testsErrors[$key])}
                                <li>{$checks.testsErrors[$key]|escape:'html':'UTF-8'}</li>
                              {/if}
                          {/foreach}
                      </ul>
                        {/if}
                        {if isset($checks.failOptional)}
                          <p>
                          <strong>{l s='Optional parameters:' mod='paypal'}</strong>
                            {if !$checks.failOptional}
                              <span class="text-success">{l s='OK' mod='paypal'}</span>
                              </p>
                            {else}
                              <span class="text-danger">{l s='Please fix the following error(s)' mod='paypal'}</span>
                              </p>
                              <ul>
                                  {foreach from=$checks.testsOptional item='value' key='key'}
                                      {if $value eq 'fail' && isset($checks.testsErrors[$key])}
                                        <li>{$checks.testsErrors[$key]|escape:'html':'UTF-8'}</li>
                                      {/if}
                                  {/foreach}
                              </ul>
                            {/if}
                        {/if}
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>