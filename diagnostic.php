<?php
/*
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
 */

return [
    [
        'name' => 'paypal',
        'stubs' => [
            PaypalPPBTlib\Extensions\Diagnostic\Stubs\Concrete\FileIntegrityStub::class => [
                'repository' => '202ecommerce/paypal',
            ],
            PaypalPPBTlib\Extensions\Diagnostic\Stubs\Concrete\GithubVersionStub::class => [
                'repository' => '202ecommerce/paypal',
            ],
            PaypalPPBTlib\Extensions\Diagnostic\Stubs\Concrete\ConflictsStub::class => [],
            PaypalPPBTlib\Extensions\Diagnostic\Stubs\Concrete\DatabaseStub::class => [
                'optimize' => false,
                'integrity' => false,
            ],
            PaypalPPBTlib\Extensions\Diagnostic\Stubs\Concrete\ConfigurationStub::class => [],
            PaypalPPBTlib\Extensions\Diagnostic\Stubs\Concrete\OrderStateStub::class => [
                'statuses' => [
                    'PAYPAL_OS_REFUNDED' => [],
                    'PAYPAL_OS_CANCELED' => [],
                    'PAYPAL_OS_ACCEPTED' => [],
                    'PAYPAL_OS_CAPTURE_CANCELED' => [],
                    'PAYPAL_OS_WAITING_VALIDATION' => [],
                    'PAYPAL_OS_REFUNDED' => [],
                    'PAYPAL_OS_WAITING' => [
                        'color' => '#4169E1',
                        'invoice' => false,
                        'send_email' => false,
                    ],
                ],
            ],
            PaypalPPBTlib\Extensions\Diagnostic\Stubs\Concrete\HooksStub::class => [],
            PaypalPPBTlib\Extensions\Diagnostic\Stubs\Concrete\OverridesStub::class => [],
            PaypalPPBTlib\Extensions\Diagnostic\Stubs\Concrete\HostStub::class => [],
            PaypalPPBTlib\Extensions\Diagnostic\Stubs\Concrete\LogsStub::class => [],
        ],
    ],
];
