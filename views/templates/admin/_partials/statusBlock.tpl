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

<div>
  <div>
      {l s='Merchant Country:' mod='paypal'} <b>{$vars.merchantCountry|escape:'htmlall':'UTF-8'}</b>
  </div>

  <div>
      {{l s='To  modify country: [a @href1@]International > Localization[/a]' mod='paypal'}|paypalreplace:['@href1@' => {$vars.localizationUrl}, '@target@' => {'target="blank"'}]}
  </div>

  <div>
      {if $vars.sslActivated|default:false}
        <i class="icon-check text-success"></i>
          {l s='SSL enabled.' mod='paypal'}
      {else}
        <i class="icon-remove text-danger"></i>
          {l s='SSL should be enabled on your website.' mod='paypal'}
      {/if}
  </div>

  <div>
      {if isset($vars.tlsVersion) && $vars.tlsVersion['status']}
        <i class="icon-check text-success"></i>
          {l s='The PHP cURL extension must be enabled on your server.' mod='paypal'}
      {elseif isset($vars.tlsVersion)}
        <i class="icon-remove text-danger"></i>
          {l s='The PHP cURL extension must be enabled on your server. Please contact your hosting provider for more information.' mod='paypal'} {$tlsVersion['error_message']}
      {/if}
  </div>

    {if $vars.showWebhookState|default:false}
      <div>
          {if $vars.webhookState|default:false}
            <i class="icon-check text-success"></i>
          {else}
            <i class="icon-remove text-danger"></i>
          {/if}
          {if isset($vars.webhookStateMsg)}{$vars.webhookStateMsg nofilter}{/if}
      </div>
    {/if}

</div>




