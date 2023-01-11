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

<div class="panel active-panel pp__flex pp__align-items-center">
	<div class="pp__pr-4">
		<img style="width: 135px" src="{$moduleDir|addslashes}paypal/views/img/paypal.png">
	</div>
	<div class="pp__pl-5">
		<p>
			{l s='Activate the PayPal module to start selling to +300M PayPal customers around the globe' mod='paypal'}.
		</p>
		{if isset($headerToolBar) && $headerToolBar}
        	{if isset($methodType) && $methodType == 'EC'}
				<p>{l s='Activate in three easy steps' mod='paypal'}: </p>
			{else}
				<p>{l s='Activate in two easy steps' mod='paypal'}: </p>
        	{/if}

			<p>
				<ul>
					<li>
						<a href="#pp_config_account" data-pp-link-settings> {l s='Connect below your existing PayPal account or create a new one' mod='paypal'}.</a>
					</li>

					{if isset($methodType) && $methodType == 'EC'}
						<li>
							<a href="#pp_config_payment" data-pp-link-settings> {l s='Adjust your Payment setting to either capture payments instantly (Sale), or after you confirm the order (Authorize)' mod='paypal'}.</a>
						</li>
					{/if}

					<li>
						<a href="#pp_config_environment" data-pp-link-settings> {l s='Make sure the module is set to Production mode' mod='paypal'}.</a>
					</li>
				</ul>
			</p>
			<p>{l s='Voilà! Your store is ready to accept payments!' mod='paypal'}</p>
		{/if}
	</div>
</div>

