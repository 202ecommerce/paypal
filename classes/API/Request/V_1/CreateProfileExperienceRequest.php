<?php
/**
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
 */

namespace PaypalAddons\classes\API\Request\V_1;

use Configuration;
use Exception;
use Module;
use PaypalAddons\classes\API\Response\Error;
use PaypalAddons\classes\API\Response\ResponseCreateProfileExperience;
use Throwable;
use Tools;

class CreateProfileExperienceRequest extends RequestAbstractMB
{
    /**
     * @return ResponseCreateProfileExperience
     */
    public function execute()
    {
        $response = new ResponseCreateProfileExperience();

        // Parameters for input fields customization.
        $inputFields = new \PayPal\Api\InputFields();
        // Enables the buyer to enter a note to the merchant on the PayPal page during checkout.
        $inputFields->setAllowNote(false)
            // Determines whether or not PayPal displays shipping address fields on the experience pages. Allowed values: 0, 1, or 2. When set to 0, PayPal displays the shipping address on the PayPal pages. When set to 1, PayPal does not display shipping address fields whatsoever. When set to 2, if you do not pass the shipping address, PayPal obtains it from the buyer’s account profile. For digital goods, this field is required, and you must set it to 1.
            ->setNoShipping(1)
            // Determines whether or not the PayPal pages should display the shipping address and not the shipping address on file with PayPal for this buyer. Displaying the PayPal street address on file does not allow the buyer to edit that address. Allowed values: 0 or 1. When set to 0, the PayPal pages should not display the shipping address. When set to 1, the PayPal pages should display the shipping address.
            ->setAddressOverride(1);
        // #### Payment Web experience profile resource
        $webProfile = new \PayPal\Api\WebProfile();
        // Name of the web experience profile. Required. Must be unique
        $webProfile->setName(Tools::substr(Configuration::get('PS_SHOP_NAME'), 0, 30) . uniqid())
            // Parameters for input field customization.
            ->setInputFields($inputFields)
            // Indicates whether the profile persists for three hours or permanently. Set to `false` to persist the profile permanently. Set to `true` to persist the profile for three hours.
            ->setTemporary(false);

        // For Sample Purposes Only.
        try {
            // Use this call to create a profile.
            $createProfileResponse = $webProfile->create($this->getApiContext());
        } catch (Throwable $ex) {
            $module = Module::getInstanceByName($this->method->name);
            $error = new Error();
            $error->setMessage($module->l('An error occurred while creating your web experience. Check your credentials.', get_class($this)));

            return $response->setError($error)->setSuccess(false);
        } catch (Exception $ex) {
            $module = Module::getInstanceByName($this->method->name);
            $error = new Error();
            $error->setMessage($module->l('An error occurred while creating your web experience. Check your credentials.', get_class($this)));

            return $response->setError($error)->setSuccess(false);
        }

        return $response
            ->setIdProfileExperience($createProfileResponse->getId())
            ->setSuccess(true)
            ->setData($createProfileResponse);
    }
}
