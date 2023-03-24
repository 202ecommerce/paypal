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

    {if isset($method) &&
        $method == 'PPP' ||
        ($method == 'EC' && (isset($country_iso) && in_array($country_iso, ['IN', 'JP']) == false))}
        <p class="h3">
            {if isset($accountConfigured) && $accountConfigured}<i class="icon-check text-success"></i>{/if}
            {l s='PayPal Account' mod='paypal'}
            {if isset($accountConfigured) && $accountConfigured}{l s='connected' mod='paypal'}{/if}
        </p>
        {if isset($accountConfigured) == false || $accountConfigured == false}
          <p>
              {l s='In order to activate the module, you must connect your existing PayPal account or create a new one.' mod='paypal'}
          </p>
        {/if}

    {/if}

    {if isset($accountConfigured) && $accountConfigured}

        {if isset($method) && $method == 'MB'}
            {include './mbCredentialsForm.tpl'}
        {/if}

        {if isset($country_iso) && in_array($country_iso, ['IN', 'JP'])}
          <div class="modal-body">
            <h4>{l s='API Credentials' mod='paypal'}</h4>
            <p>{l s='In order to accept PayPal payments, please fill in your API REST credentials.' mod='paypal'}</p>
            <ul>
              <li>{l s='Access' mod='paypal'} <a target="_blank" href="https://developer.paypal.com/developer/applications/">{l s='https://developer.paypal.com/developer/applications/' mod='paypal'}</a></li>
              <li>{l s='Log in or Create a business account' mod='paypal'}</li>
              <li>{l s='Create a « REST API apps »' mod='paypal'}</li>
              <li>{l s='Click « Show » below « Secret: »' mod='paypal'}</li>
              <li>{l s='Copy/paste your « Client ID » and « Secret » below for each environment' mod='paypal'}</li>
            </ul>
            <hr/>

            <input type="hidden" name="id_shop" value="{if isset($idShop)}{$idShop}{/if}"/>
            <h4>{l s='API Credentials for' mod='paypal'} {$mode}</h4>
              {include './ecCredentialFields.tpl'}

          </div>
        {/if}

        {if isset($method) &&
        $method == 'PPP' ||
        ($method == 'EC' && (isset($country_iso) && in_array($country_iso, ['IN', 'JP']) == false))}
            <span class="btn btn-default pp__mt-5" id="logoutAccount">
              <i class="icon-signout"></i>
				      {l s='Logout' mod='paypal'}
            </span>
        {/if}
    {else}
        {if isset($method) && $method == 'MB'}
            {include './mbCredentialsForm.tpl'}
        {elseif isset($country_iso) && in_array($country_iso, ['IN', 'JP'])}
          <div class="modal-body">
            <h4>{l s='API Credentials' mod='paypal'}</h4>
            <p>{l s='In order to accept PayPal payments, please fill in your API REST credentials.' mod='paypal'}</p>
            <ul>
              <li>{l s='Access' mod='paypal'} <a target="_blank" href="https://developer.paypal.com/developer/applications/">{l s='https://developer.paypal.com/developer/applications/' mod='paypal'}</a></li>
              <li>{l s='Log in or Create a business account' mod='paypal'}</li>
              <li>{l s='Create a « REST API apps »' mod='paypal'}</li>
              <li>{l s='Click « Show » below « Secret: »' mod='paypal'}</li>
              <li>{l s='Copy/paste your « Client ID » and « Secret » below for each environment' mod='paypal'}</li>
            </ul>
            <hr/>

            <input type="hidden" name="id_shop" value="{if isset($idShop)}{$idShop}{/if}"/>
            <h4>{l s='API Credentials for' mod='paypal'} {$mode}</h4>
              {include './ecCredentialFields.tpl'}

          </div>
        {elseif isset($method) && in_array($method, ['EC'])}
          <a href="{$urlOnboarding|addslashes}"
            target="_blank"
            data-paypal-button
            data-paypal-onboard-complete="onboardCallback"
            class="btn btn-default spinner-button"
          >
            <i class="icon-signin"></i>
            <div class="spinner pp__mr-1"></div>
            {l s='Connect or create PayPal account' mod='paypal'}
          </a>

          <script src="{$paypalOnboardingLib|addslashes}"></script>
        {elseif isset($SignUpLinkButton)}
            {$SignUpLinkButton->render() nofilter} {* html. cannot be escaped*}
        {/if}
    {/if}
</div>

{if isset($isPuiAvailable) && $isPuiAvailable == false}
  <div class="alert alert-warning pp__mt-3">
      {{l s='If you don\'t see Pay Upon Invoice payment method at your checkout page, please request it to PayPal by using this [a @href1@]link[/a]' mod='paypal'}|paypalreplace:['@href1@' => 'https://www.paypal.com/bizsignup/entry?country.x=DE&product=payment_methods&capabilities=PAY_UPON_INVOICE', '@target@' => {'target="blank"'}]}
  </div>
{/if}
