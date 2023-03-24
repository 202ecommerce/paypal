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

<div bnpl-page-displaying-setting-container>
  <div>
    <div class="form-group">
      <input
              type="checkbox"
              id="PAYPAL_BNPL_CART_PAGE"
              name="PAYPAL_BNPL_CART_PAGE"
              value="1"
              {if isset($PAYPAL_BNPL_CART_PAGE) && $PAYPAL_BNPL_CART_PAGE}checked{/if}
      >
      <label for="PAYPAL_BNPL_CART_PAGE" class="control-label">
          {l s='Cart Page' mod='paypal'}
      </label>
    </div>

    <div class="form-group">
      <input
              type="checkbox"
              id="PAYPAL_BNPL_PAYMENT_STEP_PAGE"
              name="PAYPAL_BNPL_PAYMENT_STEP_PAGE"
              value="1"
              {if isset($PAYPAL_BNPL_PAYMENT_STEP_PAGE) && $PAYPAL_BNPL_PAYMENT_STEP_PAGE}checked{/if}
      >
      <label for="PAYPAL_BNPL_PAYMENT_STEP_PAGE" class="control-label">
          {l s='Step payment in checkout' mod='paypal'}
      </label>
    </div>

    <div class="form-group">
      <input
              type="checkbox"
              id="PAYPAL_BNPL_PRODUCT_PAGE"
              name="PAYPAL_BNPL_PRODUCT_PAGE"
              value="1"
              {if isset($PAYPAL_BNPL_PRODUCT_PAGE) && $PAYPAL_BNPL_PRODUCT_PAGE}checked{/if}
      >
      <label for="PAYPAL_BNPL_PRODUCT_PAGE" class="control-label">
          {l s='Product Pages' mod='paypal'}
      </label>
    </div>

    <div class="form-group">
      <input
              type="checkbox"
              id="PAYPAL_BNPL_CHECKOUT_PAGE"
              name="PAYPAL_BNPL_CHECKOUT_PAGE"
              value="1"
              {if isset($PAYPAL_BNPL_CHECKOUT_PAGE) && $PAYPAL_BNPL_CHECKOUT_PAGE}checked{/if}
      >
      <label for="PAYPAL_BNPL_CHECKOUT_PAGE" class="control-label">
          {l s='Sign up step in checkout' mod='paypal'}
      </label>
    </div>

  </div>
</div>
