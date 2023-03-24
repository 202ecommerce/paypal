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

namespace PaypalAddons\classes;

use Exception;

/**
 * Class PaypalException
 * Custom exception with additional long message parameter
 */
class PaypalException extends Exception
{
    /** @var string Long detailed error message */
    private $message_long;

    /**
     * PaypalException constructor.
     * Redefine the exception construct so add long message
     *
     * @param int $code
     * @param string $message not required
     * @param string $message_long not required
     */
    public function __construct($code = 0, $message = '', $message_long = '')
    {
        $this->message_long = $message_long;
        // make sure everything is assigned properly
        parent::__construct($message, $code);
    }

    /**
     * @return string
     */
    public function getMessageLong()
    {
        return $this->message_long;
    }

    /*
     * custom string representation of object
     */
    /*public function __toString() {
        $paypal = Module::getInstanceByName('paypal');
        $error_msg = $this->code ? '['.$this->code.'] ' : '';
        $error_msg .= $this->message ? $this->message : '';
        $error_msg .= '<br>';
        $error_msg .= $this->message_long ? $this->message_long : '';
        return $error_msg."\n";
    }*/
}
