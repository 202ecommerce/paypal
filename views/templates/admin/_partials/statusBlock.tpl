{**
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
<div class="row pb-3 h-100">
  <div class="col-12 col-lg-9 col-xl-8 pb-4">
    <p>
      {l s='Merchant Country:' mod='paypal'} <b>{$vars.merchantCountry|escape:'htmlall':'UTF-8'}</b>
    </p>

    <p>
      {{l s='To  modify country: [a @href1@]International > Localization[/a]' mod='paypal'}|paypalreplace:['@href1@' =>
      {$vars.localizationUrl}, '@target@' => {'target="blank"'}]}
    </p>

    <ul class="list-unstyled mb-0">
      <li class="d-flex mb-1">
        {include
          file=$moduleFullDir|cat:"/views/templates/admin/_partials/icon-status.tpl"
          isSuccess=$vars.sslActivated|default:false
        }
        {if $vars.sslActivated|default:false}
          {l s='SSL enabled.' mod='paypal'}
        {else}
          {l s='SSL should be enabled on your website.' mod='paypal'}
        {/if}
      </li>

      {if $vars.tlsVersion|default:false}
        <li class="d-flex mb-1">
          {include
            file=$moduleFullDir|cat:"/views/templates/admin/_partials/icon-status.tpl"
            isSuccess=($vars.tlsVersion|default:false && $vars.tlsVersion['status'])
          }
          {if $vars.tlsVersion|default:false && $vars.tlsVersion['status']}
            {l s='The PHP cURL extension must be enabled on your server.' mod='paypal'}
          {elseif $vars.tlsVersion|default:false}
              <div>
                  {l s='TLS verification is failed. Probably, It doesn\'t impact on the module functionality.' mod='paypal'}
                  {{l s='The module can\'t access to [a @href1@]the page[/a].' mod='paypal'}|paypalreplace:['@href1@' =>{$vars.tlsVersion['ping_page']|default:'#'}, '@target@' => {'target="blank"'}]}
                  {l s='The possible causes are the following:' mod='paypal'}
                <ul>
                  <li>{l s='htpasswd enabled' mod='paypal'}</li>
                  <li>{l s='the https protocol is not supported' mod='paypal'}</li>
                  <li>{l s='wrong https certificate' mod='paypal'}</li>
                  <li>{l s='invalid server settings or insufficient access rights.' mod='paypal'}</li>
                </ul>
                  {if {$vars.tlsVersion['error_message']|default:''}}
                    <div>
                        {l s='Access error message:' mod='paypal'}
                        {$vars.tlsVersion['error_message']|escape:'htmlall':'UTF-8'}
                    </div>
                  {/if}
              </div>

          {/if}
        </li>
      {/if}

      {if $vars.showWebhookState|default:false}
        {if $vars.isWebhookEnabled}
          <li class="d-flex">
              {include
              file=$moduleFullDir|cat:"/views/templates/admin/_partials/icon-status.tpl"
              isSuccess=$vars.webhookState|default:false
              }
              {if isset($vars.webhookStateMsg)}{$vars.webhookStateMsg nofilter}{/if}
          </li>
        {else}
          <li class="d-flex">
              {include
              file=$moduleFullDir|cat:"/views/templates/admin/_partials/icon-status.tpl"
              isSuccess=false
              }
              {l s='Webhook is disabled' mod='paypal'}
          </li>
        {/if}
      {/if}

      {if isset($vars.numberRedundantFiles) && $vars.numberRedundantFiles}
        <li class="d-flex mb-1">
          {include
          file=$moduleFullDir|cat:"/views/templates/admin/_partials/icon-status.tpl"
          isSuccess=false
          }
          <div>{{l s='You have files in your PayPal module that are not included in the official content. See more details on [a @href1@]diagnostic page.[/a]' mod='paypal'}|paypalreplace:['@href1@' =>{$vars.diagnosticPage|default:'#'}, '@target@' => {'target="blank"'}]}</div>
        </li>
      {/if}
    </ul>
  </div>

  <div class="col-12 col-lg-3 col-xl-4 align-items-end d-flex justify-content-end">
    <button class="btn btn-secondary ml-auto" refresh-technical-checklist>{l s='Refresh' mod='paypal'}</button>
  </div>

</div>
