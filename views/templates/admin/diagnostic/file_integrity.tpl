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
<div class="w-100 mb-3">
  <div class="row">
    <div class="col-sm-12">
      <div class="row justify-content-center">
        <div class="col-xl-12 pr-5 pl-5">
          <div class="card">
            <div class="card-header d-flex justify-content-between">
              <div>
                <span class="material-icons">description</span>
                  {l s='File integrity' mod='paypal'}
              </div>
                {if isset($missing) == false}
                  <div class="badge-secondary px-2 mb-0">
                    {l s='Not available' mod='paypal'}
                  </div>
                {elseif empty($created) && empty($missing) && empty($updated)}
                  <div class="badge-success px-2 mb-0">
                    {l s='There are no missing or updated files' mod='paypal'}
                  </div>
                {else}
                  <div class="badge-danger color-light px-2 mb-0">
                    {l s='Missing or updated files found' mod='paypal'}
                  </div>
                {/if}
            </div>
            <div class="form-wrapper justify-content-center col-xl-12 mt-3 d-none">
                <div class="mt-2 alert alert-info">{l s='Compare your %s module files with the official package of the same release.' sprintf=[$module_name|escape:'html':'UTF-8'] mod='paypal'}</div>
                {if !empty($missing)}
                  <p>{l s='These files are not found in your server, you should add it:' mod='paypal'}</p>
                  <ul class="list-unstyled">
                      {foreach $missing as $missingFile}
                        <li>
                          <span class="material-icons">remove</span>
                          <abbr title="{l s='File deleted relative to the original package' mod='paypal'}">
                            {$missingFile|escape:'html':'UTF-8'}</abbr>
                        </li>
                      {/foreach}
                  </ul>
                {/if}
                {if !empty($created)}
                  <p>{l s='These files are found in your server, but not in the original package:' mod='paypal'}</p>
                  <ul class="list-unstyled">
                      {foreach $created as $createdFile}
                        <li>
                          <span class="material-icons">add</span>
                          <abbr title="{l s='File added relative to the original package' mod='paypal'}">
                            {$createdFile|escape:'html':'UTF-8'}</abbr>
                        </li>
                      {/foreach}
                  </ul>
                {/if}
                {if !empty($updated)}
                  <p>{l s='These files were modified in module relative to the original package:' mod='paypal'}</p>
                  <div>
                      {foreach $updated as $updatedFile}
                        <details class="mb-2">
                          <summary>{$updatedFile.path|escape:'html':'UTF-8'}</summary>
                          <code>
                            <pre class="mt-2">{$updatedFile.diff|escape:'html':'UTF-8'}}</pre>
                          </code>
                        </details>
                      {/foreach}
                  </div>
                {/if}
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
