{if !empty($conflicts.data)}
  <div class="w-100 mb-3">
    <div class="row">
      <div class="col-sm-12">
        <div class="row justify-content-center">
          <div class="col-xl-12 pr-5 pl-5">
            <div class="card">
              <div class="card-header d-flex justify-content-between">
                <div>
                  <span class="material-icons">block</span>
                    {l s='Known conflicts (modules, configuration)' mod='paypal'}
                </div>
                {if empty($conflicts.data)}
                  <div class="badge-success px-2 mb-0">{l s='There is no conflicts' mod='paypal'}</div>
                {else}
                  <div class="badge-warning px-2 mb-0">{l s='Conflicts found' mod='paypal'}</div>
                {/if}
              </div>
              <div class="form-wrapper justify-content-center col-xl-12 mt-3 {if empty($conflicts.data)}d-none{/if}">
                <ul>
                  {foreach $conflicts.data as $conflict}
                      <li>{$conflict|escape:'html':'UTF-8'}</li>
                  {/foreach}
                </ul>
              </div>
             {if $conflicts.action|escape:'html':'UTF-8'}
               <div class="card-footer {if empty($conflicts.data)}d-none{/if}">
                 <div class="d-flex justify-content-end">
                   <a href="{$conflicts.action}" class="btn btn-lg btn-primary badge-info" type="submit">
                       {l s='Fix conflicts' mod='paypal'}
                   </a>
                 </div>
               </div>
             {/if}
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
{/if}
