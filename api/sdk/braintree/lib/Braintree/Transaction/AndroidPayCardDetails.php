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
 * Android Pay card details from a transaction
 *
 * @copyright  2015 Braintree, a division of PayPal, Inc.
 */

/**
 * creates an instance of AndroidPayCardDetails
 *
 * @copyright  2015 Braintree, a division of PayPal, Inc.
 *
 * @property string $bin
 * @property string $default
 * @property string $expirationMonth
 * @property string $expirationYear
 * @property string $googleTransactionId
 * @property string $imageUrl
 * @property string $sourceCardLast4
 * @property string $sourceCardType
 * @property string $sourceDescription
 * @property string $token
 * @property string $virtualCardLast4
 * @property string $virtualCardType
 */
class AndroidPayCardDetails extends Instance
{
    protected $_attributes = [];

    /**
     * @ignore
     */
    public function __construct($attributes)
    {
        parent::__construct($attributes);
        $this->_attributes['cardType'] = $this->virtualCardType;
        $this->_attributes['last4'] = $this->virtualCardLast4;
    }
}
class_alias('Braintree\Transaction\AndroidPayCardDetails', 'Braintree_Transaction_AndroidPayCardDetails');
