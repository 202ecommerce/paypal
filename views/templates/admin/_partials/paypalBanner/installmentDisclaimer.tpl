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

{if isset($isoCountryDefault)}
    {if $isoCountryDefault == 'fr'}
      <div installment-disclaimer class="pp__flex pp__align-items-center">
        <div class="pp__pr-4">
          <img style="width: 135px" src="{$moduleDir|addslashes}paypal/views/img/paypal.png">
        </div>
        <div class="pp__pl-5">
          <div class="h4">
              {l s='Display the 4X PayPal Payment on your site' mod='paypal'}
          </div>
          <div>
              {l s='Payment in 4X PayPal allows French consumers to pay in 4 equal installments. You can promote 4X PayPal Payment only if you are a merchant based in France, with a French website and standard PayPal integration.' mod='paypal'}
              {l s='Merchants with the Vaulting tool (digital safe) or recurring payments / subscription integration, as well as those with certain activities (sale of digital goods / non-physical goods) are not eligible to promote 4X PayPal Payment . We will post messages on your site promoting 4X PayPal Payment. You cannot promote 4X PayPal Payment with any other content.' mod='paypal'}
          </div>
          <div>
            <a href="https://www.paypal.com/fr/business/buy-now-pay-later" target="_blank">
                {l s='See more' mod='paypal'}
            </a>
          </div>
        </div>
      </div>

      <hr>
    {/if}

    {if $isoCountryDefault == 'gb'}
      <div installment-disclaimer class="pp__flex pp__align-items-center">
        <div class="pp__pr-4">
          <img style="width: 135px" src="{$moduleDir|addslashes}paypal/views/img/paypal.png">
        </div>
        <div class="pp__pl-5">
          <div class="h4">
              {l s='Display the 3X PayPal Payment on your site' mod='paypal'}
          </div>
          <div>
              {l s='Display pay later messaging on your site for offers like Pay in 3, which lets customers pay with 3 interest-free monthly payments.' mod='paypal'}
              {l s='We’ll show messages on your site to promote this feature for you. You may not promote pay later offers with any other content, marketing, or materials.' mod='paypal'}
          </div>
          <div>
            <a href="https://www.paypal.com/fr/business/buy-now-pay-later" target="_blank">
                {l s='See more' mod='paypal'}
            </a>
          </div>
        </div>
      </div>

      <hr>
    {/if}
{/if}


