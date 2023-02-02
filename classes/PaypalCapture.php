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
 * Class PaypalCapture.
 */
class PaypalCapture extends ObjectModel
{
    /** @var string Capture ID */
    public $id_capture;

    /** @var int PaypalOrder ID */
    public $id_paypal_order;

    /** @var float Captured amount */
    public $capture_amount;

    /** @var string Transaction status */
    public $result;

    /** @var string Object creation date */
    public $date_add;

    /** @var string Object last modification date */
    public $date_upd;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table' => 'paypal_capture',
        'primary' => 'id_paypal_capture',
        'multilang' => false,
        'fields' => [
            'id_capture' => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
            'id_paypal_order' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
            'capture_amount' => ['type' => self::TYPE_FLOAT, 'size' => 10, 'scale' => 2],
            'result' => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
            'date_add' => ['type' => self::TYPE_DATE, 'validate' => 'isDateFormat'],
            'date_upd' => ['type' => self::TYPE_DATE, 'validate' => 'isDateFormat'],
        ],
        'collation' => 'utf8_general_ci',
    ];

    /**
     * Load Capture by PaypalOrder
     *
     * @param int $orderPayPalId PaypalOrder ID
     *
     * @return object PaypalCapture
     */
    public static function loadByOrderPayPalId($orderPayPalId)
    {
        $sql = new DbQuery();
        $sql->select('id_paypal_capture');
        $sql->from('paypal_capture');
        $sql->where('id_paypal_order = ' . (int) $orderPayPalId);
        $id_paypal_capture = Db::getInstance()->getValue($sql);

        return new self($id_paypal_capture);
    }

    /**
     * Get all datas from PaypalOrder and PaypalCapture
     *
     * @param int $id_order PrestaShop order ID
     *
     * @return array PaypalCapture
     */
    public static function getByOrderId($id_order)
    {
        $sql = new DbQuery();
        $sql->select('*');
        $sql->from('paypal_order', 'po');
        $sql->innerJoin('paypal_capture', 'pc', 'po.`id_paypal_order` = pc.`id_paypal_order`');
        $sql->where('po.id_order = ' . (int) $id_order);
        $row = Db::getInstance()->getRow($sql);

        if (is_array($row)) {
            return $row;
        } else {
            return [];
        }
    }

    /**
     * Update PaypalCapture
     *
     * @param string $transaction_id New transaction ID that correspond to capture
     * @param float $amount Captured amount
     * @param string $status new payment status
     * @param int $id_paypal_order PaypalOrder ID
     */
    public static function updateCapture($transaction_id, $amount, $status, $id_paypal_order)
    {
        Db::getInstance()->update(
            'paypal_capture',
            [
                'id_capture' => pSQL($transaction_id),
                'capture_amount' => (float) $amount,
                'result' => pSQL($status),
            ],
            'id_paypal_order = ' . (int) $id_paypal_order
        );
    }
}
