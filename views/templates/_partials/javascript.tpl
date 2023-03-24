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

<script>
 {if isset($JSvars)}    
   {foreach from=$JSvars key=varName item=varValue}
       {assign var="isNotJSVarsvalue" value=($varName == '0' && $varValue == '1')}
       {if $isNotJSVarsvalue === false}        
           var {$varName} = {$varValue|json_encode nofilter};
       {/if}
   {/foreach}
 {/if}
</script>

{if isset($JSscripts) && is_array($JSscripts) && false === empty($JSscripts)}
  <script>
      function init() {
          if (document.readyState == 'complete') {
              addScripts();
          } else {
              document.addEventListener('readystatechange', function () {
                  console.log(document.readyState);
                  if (document.readyState == 'complete') {
                      addScripts();
                  }
              })
          }

          function addScripts() {
              var scripts = Array();
              {foreach from=$JSscripts key=keyScript item=JSscriptAttributes}
              var script = document.querySelector('script[data-key="{$keyScript}"]');

              if (null == script) {
                  var newScript = document.createElement('script');
                  {foreach from=$JSscriptAttributes key=attrName item=attrVal}
                  newScript.setAttribute('{$attrName}', '{$attrVal nofilter}');
                  {/foreach}

                  if (false === ('{$keyScript}'.search('jq-lib') === 0 && typeof jQuery === 'function')) {
                      newScript.setAttribute('data-key', '{$keyScript}');
                      scripts.push(newScript);
                  }
              }
              {/foreach}

              scripts.forEach(function (scriptElement) {
                  document.body.appendChild(scriptElement);
              })
          };
      };
      init();

  </script>

{/if}

