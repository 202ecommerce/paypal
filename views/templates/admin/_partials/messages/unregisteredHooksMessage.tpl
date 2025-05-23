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

{if isset($hooks) && is_array($hooks) && empty($hooks) == false}
    <div>
        <p>
            {l s='The module is not registered with the following hooks :' mod='paypal'}
        </p>

        <ul>

            {foreach from=$hooks item=hookName}
                <li class="pp__mb-0">
                    {$hookName|escape:'htmlall':'UTF-8'}
                </li>
            {/foreach}

        </ul>

        <div class="pp__mt-5">
            <a href="{$link->getAdminLink('AdminPayPalHelp', true, null, ['registerHooks' => 1])}"
               class="btn btn-default">
                {l s='Install the required hooks automatically' mod='paypal'}
            </a>
        </div>
    </div>
{/if}

