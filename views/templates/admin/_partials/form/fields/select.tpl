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

<div field-row class="{if isset($css) && $css}{$css}{/if}">

    {if isset($label) && $label}
        <div label>
          <div class="pp__my-2">{$label|escape:'htmlall':'UTF-8'}</div>
        </div>
    {/if}

  <div field>
    <div select-wrap class="pp__flex">
      <select
              name="{if isset($name)}{$name}{/if}"
              {if isset($configType)}data-type="{$configType}"{/if}>

          {if isset($options) && false === empty($options)}
              {foreach from=$options item=option}
                  {$option->render() nofilter}
              {/foreach}
          {/if}

      </select>

      {if isset($hintSwitch) && $hintSwitch}
          <div>
            <span class="btn btn-default pp__ml-2" onclick="toggleHint(event)">?</span>
          </div>
      {/if}
    </div>

    {if isset($afterSelectContent)}
        <div after-select-content>
          {$afterSelectContent nofilter}
        </div>
    {/if}
  </div>
</div>

<script>
  window.toggleHint = function (e) {
      try {
          var btn = e.target;
          var hint = btn.closest('[field]').querySelector('[hint-wrap]');
          hint.style.display = hint.style.display == 'none' ? 'block' : 'none';
          btn.textContent = btn.textContent == '?' ? 'X' : '?';
      } catch (exception) {
          console.error(exception);
      }
  }
</script>


