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

/**
 * Class PaypalIpn.
 */
class PaypalWebhook extends ObjectModel
{
    const DATE_FORMAT = 'Y-m-d H:i:s';

    /** @var int */
    public $id_paypal_order;

    /* @var string */
    public $id_webhook;

    /* @var string */
    public $event_type;

    /* @var string */
    public $data;

    /** @var int */
    public $id_state;

    /* @var string creation date*/
    public $date_add;

    /* @var string date*/
    public $date_completed;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table' => 'paypal_webhook',
        'primary' => 'id_paypal_webhook',
        'multilang' => false,
        'fields' => [
            'id_paypal_order' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
            'id_webhook' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName'],
            'event_type' => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
            'data' => ['type' => self::TYPE_HTML, 'validate' => 'isString'],
            'id_state' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
            'date_add' => ['type' => self::TYPE_DATE, 'validate' => 'isDateFormat'],
            'date_completed' => ['type' => self::TYPE_DATE, 'validate' => 'isDateFormat'],
        ],
    ];
}
