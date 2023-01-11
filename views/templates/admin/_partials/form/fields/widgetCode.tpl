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

<div widget-code-container>
  <div class="input-group pp__mb-5">
    <input
            type="text"
            readonly
            {if isset($widgetCode)}value="{$widgetCode}"{/if}
            id="{if isset($confName)}{$confName}{/if}"
            name="{if isset($confName)}{$confName}{/if}"
    />

    <span
            class="input-group-addon"
            style="cursor: pointer"
            onclick="document.getElementById('{if isset($confName)}{$confName}{/if}').select(); document.execCommand('copy')"
    >
            <i class="icon-copy"></i>
    </span>
  </div>

  {if isset($isShowDescription) && isShowDescription}
    <div class="pp__mt-5">
      <div class="alert alert-info">
          {{l s='In order to display the PayPal button via [a @href1@]widget[/a] it will be necessary to add it to the template at the desired location.'}|paypalreplace:['@href1@' => 'https://devdocs.prestashop.com/1.7/modules/concepts/widgets/', '@target@' => {'target="blank"'}]}
      </div>
    </div>
  {/if}

</div>


