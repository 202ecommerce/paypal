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
{include file='module:paypal/views/templates/_partials/javascript.tpl'}

<div paypal-messaging-banner class="{if in_array($paypalmessenging.placement, ['category', 'home'])}container{/if}"
 data-pp-message
  {if !empty($paypalmessenging.buyercountry)}
    data-pp-buyercountry="{$paypalmessenging.buyercountry|escape:'htmlall':'UTF-8'}"
  {/if}
 {if $paypalmessenging.placement != 'home' && $paypalmessenging.placement != 'category'}
    data-pp-style-layout="{$paypalmessenging.layout|escape:'htmlall':'UTF-8'}"
    data-pp-style-logo-type="{$paypalmessenging.logo_type|escape:'htmlall':'UTF-8'}"
   {if isset($paypalmessenging.logo_position)}
     data-pp-style-logo-position="{$paypalmessenging.logo_position|escape:'htmlall':'UTF-8'}"
   {/if}
    data-pp-style-text-color="{$paypalmessenging.text_color|escape:'htmlall':'UTF-8'}"
    data-pp-style-text-size="{$paypalmessenging.text_size|escape:'htmlall':'UTF-8'}"
 {else}
    data-pp-style-color="{$paypalmessenging.color|escape:'htmlall':'UTF-8'}"
    data-pp-style-layout="{$paypalmessenging.layout|escape:'htmlall':'UTF-8'}"
    data-pp-style-ratio="{$paypalmessenging.ratio|escape:'htmlall':'UTF-8'}"
 {/if}
 data-pp-amount="{$paypalmessenging.amount|escape:'htmlall':'UTF-8'}"
 data-pp-placement="{$paypalmessenging.placement|escape:'htmlall':'UTF-8'}"
 data-pp-locale="{$paypalmessenging.locale|escape:'htmlall':'UTF-8'}">
</div>
