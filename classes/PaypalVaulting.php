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
 * Class PaypalOrder.
 */
class PaypalVaulting extends ObjectModel
{
    /** @var int id of the Prestashop Customer object */
    public $id_customer;

    /** @var string hash of the remembered card ids */
    public $rememberedCards;

    /** @var bool mode of payment (sandbox or live) */
    public $sandbox;

    /** @var string Object creation date */
    public $date_add;

    /** @var string Object last modification date */
    public $date_upd;

    /** @var string */
    public $profile_key;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table' => 'paypal_vaulting',
        'primary' => 'id_paypal_vaulting',
        'multilang' => false,
        'fields' => [
            'id_customer' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
            'rememberedCards' => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
            'profile_key' => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
            'sandbox' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'date_add' => ['type' => self::TYPE_DATE, 'validate' => 'isDateFormat'],
            'date_upd' => ['type' => self::TYPE_DATE, 'validate' => 'isDateFormat'],
        ],
        'collation' => 'utf8_general_ci',
    ];
}
