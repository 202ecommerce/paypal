<?php
/**
 *  2007-2022 PayPal
 *
 *  NOTICE OF LICENSE
 *
 *  This source file is subject to the Academic Free License (AFL 3.0)
 *  that is bundled with this package in the file LICENSE.txt.
 *  It is also available through the world-wide-web at this URL:
 *  http://opensource.org/licenses/afl-3.0.php
 *  If you did not receive a copy of the license and are unable to
 *  obtain it through the world-wide-web, please send an email
 *  to license@prestashop.com so we can send you a copy immediately.
 *
 *  DISCLAIMER
 *
 *  Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 *  versions in the future. If you wish to customize PrestaShop for your
 *  needs please refer to http://www.prestashop.com for more information.
 *
 *  @author 2007-2022 PayPal
 *  @author 202 ecommerce <tech@202-ecommerce.com>
 *  @copyright PayPal
 *  @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace Braintree\Transaction;

use Braintree\Instance;

/**
 * Europe bank account details from a transaction
 * Creates an instance of europe bank account details as returned from a transaction
 *
 * @copyright  2015 Braintree, a division of PayPal, Inc.
 *
 * @property string $accountHolderName
 * @property string $bic
 * @property string $imageUrl
 * @property string $mandateAcceptedAt
 * @property string $mandateReferenceNumber
 * @property string $maskedIban
 * @property string $token
 */
class EuropeBankAccountDetails extends Instance
{
}
class_alias('Braintree\Transaction\EuropeBankAccountDetails', 'Braintree_Transaction_EuropeBankAccountDetails');
