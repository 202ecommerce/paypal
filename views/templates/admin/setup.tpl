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
    {if $showPsCheckoutInfo}
        {include './_partials/messages/prestashopCheckoutInfo.tpl'}
    {/if}

    {if $showRestApiIntegrationMessage}
        {include './_partials/messages/restApiIntegrationMessage.tpl'}
    {/if}

    {if isset($need_rounding) && $need_rounding}
        {include './_partials/messages/roundingSettingsMessage.tpl'}
    {/if}

    {if isset($showInstallmentPopup) && $showInstallmentPopup}
        {include './_partials/paypalBanner/installmentPopup.tpl'}
    {/if}

    {include './_partials/headerLogo.tpl'}

  <div class="pp__flex setup-blocks">
      {if isset($formAccountSettings)}
        <div class="pp__flex-item-1 pp__mr-1 stretchHeightForm">
            {if isset($country_iso) && in_array($country_iso, ['MX', 'BR', 'IN', 'JP'])}
              <div>
                  {$formAccountSettings nofilter}{* the variable contains html code *}
              </div>

              <div>
                  {if isset($formPaymentSettings)}
                    <div>
                        {$formPaymentSettings nofilter}{* the variable contains html code *}
                    </div>
                  {/if}
              </div>

            {else}
                {$formAccountSettings nofilter}{* the variable contains html code *}
            {/if}

        </div>
      {/if}

      {if isset($formEnvironmentSettings)}
        <div class="pp__flex-item-1 pp__mr-1 stretchHeightForm">
            {$formEnvironmentSettings nofilter}{* the variable contains html code *}
        </div>
      {/if}

      {if isset($country_iso) === false || false === in_array($country_iso, ['MX', 'BR', 'IN', 'JP'])}
          {if isset($formPaymentSettings)}
            <div class="pp__flex-item-1 pp__mr-1 stretchHeightForm">
                {$formPaymentSettings nofilter}{* the variable contains html code *}
            </div>
          {/if}
      {/if}

      {if isset($formStatus)}
        <div class="pp__flex-item-1 {if false === isset($country_iso) || false === in_array($country_iso, ['MX', 'BR', 'IN', 'JP'])}stretchHeightForm{/if}">
            {$formStatus nofilter}{* the variable contains html code *}
        </div>
      {/if}
  </div>
{/block}

