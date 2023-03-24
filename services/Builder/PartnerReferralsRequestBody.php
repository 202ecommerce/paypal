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

namespace PaypalAddons\services\Builder;

use Context;
use Country;
use PaypalAddons\classes\AbstractMethodPaypal;
use PaypalAddons\classes\PUI\PsMerchantId;
use State;
use Store;
use Tools;
use Validate;

class PartnerReferralsRequestBody implements BuilderInterface
{
    /** @var AbstractMethodPaypal */
    protected $method;

    protected $context;

    public function __construct(AbstractMethodPaypal $method)
    {
        $this->method = $method;
        $this->context = Context::getContext();
    }

    public function build()
    {
        $body = [
            'business_entity' => [
                'business_type' => [
                    'type' => $this->getBusinessType(),
                ],
            ],
            'partner_config_override' => [
                'return_url' => $this->context->link->getAdminLink('AdminPayPalPUIListener'),
            ],
            'operations' => [
                [
                    'operation' => 'API_INTEGRATION',
                    'api_integration_preference' => [
                        'rest_api_integration' => [
                            'integration_method' => 'PAYPAL',
                            'integration_type' => 'THIRD_PARTY',
                            'third_party_details' => [
                                'features' => [
                                    'PAYMENT',
                                    'REFUND',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'tracking_id' => $this->initPsMerchantId()->get(),
            'products' => [
                'PAYMENT_METHODS',
            ],
            'capabilities' => [
                'PAY_UPON_INVOICE',
            ],
            'legal_consents' => [
                [
                    'type' => 'SHARE_DATA_CONSENT',
                    'granted' => true,
                ],
            ],
        ];

        $businessAddress = $this->getBusinessAddresses();

        if (false == empty($businessAddress)) {
            $body['business_entity']['addresses'] = $businessAddress;
        }

        return $body;
    }

    protected function initPsMerchantId()
    {
        return new PsMerchantId($this->method);
    }

    protected function getBusinessAddresses()
    {
        $addresses = [];
        $stores = Store::getStores($this->context->language->id);

        if (empty($stores)) {
            return $addresses;
        }

        foreach ($stores as $store) {
            $country = new Country($store['id_country']);
            $address = [
                'address_line_1' => $store['address1'],
                'admin_area_2' => $store['city'],
                'postal_code' => $store['postcode'],
                'country_code' => Tools::strtoupper($country->iso_code),
                'type' => 'WORK',
            ];

            if ($country->contains_states) {
                $state = new State($store['id_state']);

                if (Validate::isLoadedObject($state)) {
                    $address['admin_area_1'] = Tools::strtoupper($state->iso_code);
                }
            }

            $addresses[] = $address;
        }

        return $addresses;
    }

    protected function getBusinessType()
    {
        return 'PRIVATE_CORPORATION';
    }
}
