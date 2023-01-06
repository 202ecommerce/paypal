<div class="w-100 mb-3">
  <div class="row">
    <div class="col-sm-12">
      <div class="row justify-content-center">
        <div class="col-xl-12 pr-5 pl-5">
          <div class="card">
            <div class="card-header d-flex justify-content-between">
              <div>
                <span class="material-icons">receipt_long</span>
                  {l s='Logs' mod='paypal'}
              </div>
                {if empty($logs.files) && empty($logs.db)}
                  <div class="badge-success px-2 mb-0">{l s='There are no logs' mod='paypal'}</div>
                {else}
                  <div class="badge-warning px-2 mb-0">{l s='Logs found' mod='paypal'}</div>
                {/if}
            </div>
            <div class="form-wrapper justify-content-center col-xl-12 mt-3 d-none">
              <section class="accordion-section clearfix">
                <div class="container">
                  {if !empty($logs.files)}
                    {foreach $logs.files as $file}
                      <div class="panel-group pt-2">
                        <div class="panel panel-default mb-3">
                          <div class="panel-heading paypal-collapse"
                                data-type="files"
                                data-value="{$file.path|escape:'html':'UTF-8'}">
                            <h4 class="panel-title mb-0">
                              <a role="button" href="#">{$file.title|escape:'html':'UTF-8'}</a>
                            </h4>
                            <small>{l s='will be exported only if less than 2Mo' mod='paypal'}</small>
                          </div>
                          <div class="d-none" data-log-zone>
                            <div class="panel-body mt-2">
                              <div data-zone-content></div>
                              <div class="d-flex justify-content-end">
                                {if $file.downloadYes}
                                  <a class="btn btn-outline-primary"
                                    href="{$actionsLink|cat:'&event=downloadLog'|cat:'&value='|cat:$file.path|cat:'&type=files'|escape:'html':'UTF-8'}">
                                    {l s='Download' mod='paypal'}
                                  </a>
                                {/if}
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    {/foreach}
                  {/if}
                  {if !empty($logs.db)}
                    {foreach $logs.db as $db => $dbValues}
                      <div class="panel-group pt-2">
                        <div class="panel panel-default mb-3">
                          <div class="panel-heading paypal-collapse"
                               data-type="db"
                               data-value="{$db|escape:'html':'UTF-8'}">
                            <h4 class="panel-title mb-0">
                              <a role="button" href="#">
                                {l s='Log - ' mod='paypal'} {$db|escape:'html':'UTF-8'} - {$dbValues.countLines|escape:'html':'UTF-8'}
                                {l s='rows /' mod='paypal'} {$dbValues.xLastDays|escape:'html':'UTF-8'}
                                {l s=' last days' mod='paypal'}
                              </a>
                            </h4>
                          </div>
                          <div class="d-none" data-log-zone>
                            <div class="panel-body mt-2">
                              <div data-zone-content></div>
                              <div class="d-flex justify-content-end">
                                <a class="btn btn-outline-primary"
                                   href="{$actionsLink|cat:'&event=downloadLog'|cat:'&value='|cat:$db|cat:'&type=db'|escape:'html':'UTF-8'}">
                                  {l s='Download' mod='paypal'}
                                </a>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    {/foreach}
                  {/if}
                </div>
              </section>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
