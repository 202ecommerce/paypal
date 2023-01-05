<div class="w-100 mb-3">
  <div class="row">
    <div class="col-sm-12">
      <div class="row justify-content-center">
        <div class="col-xl-12 pr-5 pl-5">
          <div class="card">
            <div class="card-header">
              <div>
                <span class="material-icons">file_download</span>
                  {l s='Export' mod='paypal'}
              </div>
            </div>
            <div class="form-wrapper justify-content-center col-xl-12 my-3">
              {l s='You can export all informations using the button `Export`' mod='paypal'}
            </div>
            <div class="card-footer">
              <div class="d-flex justify-content-end">
                <a href="{$exportStubLink|escape:'html':'UTF-8'}" class="btn btn-default">{l s='Export' mod='paypal'}</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>