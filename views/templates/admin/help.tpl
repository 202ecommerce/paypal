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

{extends file='./admin.tpl'}

{block name='content'}
    {if $showRestApiIntegrationMessage}
        {include './_partials/messages/restApiIntegrationMessage.tpl'}
    {/if}

    {if isset($need_rounding) && $need_rounding}
        {include './_partials/messages/roundingSettingsMessage.tpl' variant='hidden'}
    {/if}

    {include './_partials/headerLogo.tpl'}

  <div class="panel">
    <div>
        {l s='If you have just created your PayPal account, check the email sent by PayPal to confirm your email address.' mod='paypal'}
    </div>
    <div>
        {l s='You must have a PayPal Business Account. Otherwise, your personal account should be converted to Business account.' mod='paypal'}
    </div>
  </div>

    {if isset($need_rounding) && $need_rounding}
        {include file="./_partials/block_info.tpl" variant="help"}
    {/if}

    {include './_partials/messages/logoMessage.tpl'}

  <div class="panel help">
    <ul class="tick">
      <li>
        <p class="h4">
            {l s='Discover module documentation before configuration' mod='paypal'}
        </p>
        <p>
          <a target="_blank"
             href="https://help.202-ecommerce.com/wp-content/uploads/2019/12/User-guide-PayPal-PrestaShop-module-51.pdf"
             class="btn btn-default">
              {l s='Access user documentation for module configuration.' mod='paypal'}
          </a>
        </p>
      </li>


      <li>
        <p class="h4">
            {l s='Check requirements before installation' mod='paypal'}
        </p>
        <p>
            {l s='Are you using the required TLS version? Did you select a default country? Click on the button below and check if all requirements are completed!' mod='paypal'}
        </p>
        <p>
          <button  name="submit-ckeck_requirements"  class="btn btn-default" id="ckeck_requirements">
              {l s='Check requirements' mod='paypal'}
          </button>

        <p class="action_response"></p>
        </p>
      </li>

      <li>
        <p class="h4">
            {l s='Check your transactions history log and potential errors' mod='paypal'}
        </p>
        <a href="{$link->getAdminLink('AdminPayPalLogs', true)|addslashes}" class="btn btn-default">
            {l s='Transaction log' mod='paypal'}
        </a>
      </li>

      <li>
        <p class="h4">
            {l s='Do you still have any questions?' mod='paypal'}
        </p>
        <p>
            {l s='Contact us! We will be happy to help!' mod='paypal'}
        </p>
        <p>
          <a target="_blank"
             href="https://www.paypal.com/fr/webapps/mpp/contact-us"
             class="btn btn-default">
              {l s='Contact our product team for any functional questions' mod='paypal'}
          </a>
        </p>
        <p>
          <a target="_blank" href="https://addons.prestashop.com/fr/contactez-nous?id_product=1748" class="btn btn-default">
              {l s='Contact our technical support' mod='paypal'}
          </a>
        </p>
      </li>

        {if $showPsCheckout}
          <li>
            <p class="h4">
                {l s='Do you want to accept more types of payments?' mod='paypal'}
            </p>

            <p>
            <div>
                {l s='This module allows your customers to pay with their PayPal account.' mod='paypal'}
            </div>

            <div>
                {l s='If you wish to accept credit cards and other payment methods in addition to PayPal, we recommend the PrestaShop Checkout module.' mod='paypal'}
            </div>
            </p>

            <p>
          <span class="btn btn-default install-ps-checkout" data-action="install">
              {$psCheckoutBtnText|escape:'htmlall':'UTF-8'}
          </span>
            </p>

            <p>
              <image src="{$moduleDir|addslashes}paypal/views/img/Logos.png"></image>
            </p>
          </li>
        {/if}
    </ul>
  </div>
{/block}


