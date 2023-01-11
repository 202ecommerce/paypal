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
      {assign var="isUpToDate" value=true}
      {foreach $githubVersions as $index => $githubVersion}
        {if version_compare($githubVersion.name, $moduleVersion) > 0 and empty($githubVersion.prerelease) === true}
          {assign var="isUpToDate" value=false}
        {/if}
      {/foreach}
      <div class="row justify-content-center">
        <div class="col-xl-12 pr-5 pl-5">
          <div class="card">
            <div class="card-header d-flex justify-content-between">
              <div>
                <span class="material-icons">upgrade</span>
                {l s='Module version (github)' mod='paypal'}
              </div>
              {if $isUpToDate}
                <div class="badge-success px-2 mb-0">
                  {l s='Your module is up to date' mod='paypal'}
                </div>
              {else}
                <div class="badge-danger px-2 mb-0">
                  {l s='New version of your module found' mod='paypal'}
                </div>
              {/if}
            </div>
            <div class="form-wrapper justify-content-center col-xl-12 {if $isUpToDate}d-none{/if}">
              <div class="mt-2 alert alert-info">{l s='Keeping this module up to date allows better maintenance of your PrestaShop.' mod='paypal'}</div>

              {assign var="uptodate" value=true}
              {foreach name=githubVersions from=$githubVersions key=index item=githubVersion}
                {if version_compare($githubVersion.name, $moduleVersion) > 0 and $smarty.foreach.githubVersions.first}
                  {assign var="uptodate" value=false}
                  <div class="{if $index != (count($githubVersions)-1)}border-bottom{/if} mt-3 pb-3">
                    <div class="d-flex flex-column">
                      <div class="d-flex align-items-center">
                        <span>{{l s='Your version is: %a but lastest stable version is: %b.' mod='paypal'}|replace:['%a','%b']:[$moduleVersion, $githubVersion.name]}</span>
                      </div>
                    </div>
                    <div class="text-center">
                      <div class="font-weight-bold">
                        {l s='You can download the lastest release on GitHub' mod='paypal'}
                      </div>
                      <div>
                        {foreach $githubVersion.assets as $asset}
                          {if preg_match('/.zip$/', $asset.browser_download_url)}
                            <a href="{$asset.browser_download_url|escape:'html':'UTF-8'}"
                               class="btn btn-warning text-center inline">{$asset.name|escape:'html':'UTF-8'}</a>
                          {/if}
                        {/foreach}
                      </div>
                    </div>
                    <div>
                      <div class="font-weight-bold">
                        {l s='Lastest release note:' mod='paypal'}
                      </div>
                    </div>
                    <div>
                        {$githubVersion.body|escape:'html':'UTF-8'|nl2br}
                    </div>
                  </div>
                {elseif version_compare($githubVersion.name, $moduleVersion) == 0 && $uptodate == true}
                  <div class="d-flex align-items-center mt-3 pb-3">
                    <span class="material-icons text-success">close</span>
                    {l s='Nice, your addons looks up to date.' mod='paypal'}
                  </div>
                {/if}
              {/foreach}
              <div class="mt-3 mb-3" role="alert">
              {l s='You want to participate to the developers community or just be notified of new releases ?' mod='paypal'}
              {l s='Need more information, please visit our ' mod='paypal'}
              <a href="https://github.com/{$githubInfos|escape:'html':'UTF-8'}">{l s='GitHub public repository' mod='paypal'}</a>.
              </div>

              {if empty($githubVersions)}
                <div class="alert alert-danger mt-3 mb-3" role="alert">
                  <p class="alert-text">{l s='Github repository not found or forbidden' mod='paypal'}</p>
                </div>
              {/if}
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>