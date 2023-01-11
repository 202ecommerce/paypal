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

<div class="pp-select-preview-container pp__mb-5">
  <div class="pp-select-container">
    <select name="{if isset($confName)}{$confName}{/if}">
        {if isset($hooks) && false === empty($hooks)}
            {foreach from=$hooks key=hookName item=hookData}
              <option
                      value="{$hookName}"
                      {if isset($selectedHook) && $selectedHook === $hookName}selected{/if}
                      {if isset($hookData['preview'])}data-preview-image="{$hookData['preview']|addslashes}"{/if}>
                  {if isset($hookData['desc'])}{$hookData['desc']|escape:'htmlall':'UTF-8'}{/if}
              </option>
            {/foreach}
        {/if}
    </select>

    <div class="pp__mt-5">
      <div class="alert alert-info">
          {{l s='If some elements added via other modules are displayed on the same hook, you can manage the position of the PayPal Official module via [a @href1@]« Design - Positions »[/a].' mod='paypal'}|paypalreplace:['@href1@' => {$link->getAdminLink('AdminModulesPositions', true)}, '@target@' => {'target="blank"'}]}
      </div>
    </div>

  </div>

  <div class="pp-preview-container">
    <div class="pp-preview">

    </div>
  </div>
</div>

