<?php
/*
 * Since 2007 PayPal
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
 *  @author Since 2007 PayPal
 *  @author 202 ecommerce <tech@202-ecommerce.com>
 *  @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 *  @copyright PayPal
 *
 */

namespace PaypalPPBTlib\Extensions\ProcessLogger\Classes;

use \ObjectModel;

class ProcessLoggerObjectModel extends ObjectModel
{
    /** @var string log */
    public $log;

    /* @var int id_order*/
    public $id_order;

    /* @var int id_cart*/
    public $id_cart;

    /* @var int id_shop*/
    public $id_shop;

    /* @var string id_transaction*/
    public $id_transaction;

    /* @var string status*/
    public $status;

    /* @var bool sandbox*/
    public $sandbox;

    /* @var string tools*/
    public $tools;

    /* @var string creation date*/
    public $date_add;

    /* @var string date of transaction*/
    public $date_transaction;

    /**
     * @see \ObjectModel::$definition
     */
    public static $definition = array(
        'table'        => 'paypal_processlogger',
        'primary'      => 'id_paypal_processlogger',
        'fields'       => array(
            'id_order'     => array(
                'type'     => ObjectModel::TYPE_INT,
                'validate' => 'isUnsignedId',
                'size'     => 11,
            ),
            'id_cart'     => array(
                'type'     => ObjectModel::TYPE_INT,
                'validate' => 'isUnsignedId',
                'size'     => 11,
            ),
            'id_shop'     => array(
                'type'     => ObjectModel::TYPE_INT,
                'validate' => 'isUnsignedId',
                'size'     => 11,
            ),
            'id_transaction'     => array(
                'type'     => ObjectModel::TYPE_STRING,
                'validate' => 'isGenericName',
                'size'     => 50,
            ),
            'log'     => array(
                'type'     => ObjectModel::TYPE_STRING,
                'validate' => 'isGenericName',
                'size'     => 1000,
            ),
            'status'     => array(
                'type'     => ObjectModel::TYPE_STRING,
                'validate' => 'isGenericName',
                'size'     => 20,
            ),
            'sandbox'     => array(
                'type'     => ObjectModel::TYPE_BOOL,
                'validate' => 'isBool'
            ),
            'tools'     => array(
                'type'     => ObjectModel::TYPE_STRING,
                'validate' => 'isGenericName',
                'size'     => 50,
            ),
            'date_add'     => array(
                'type'     => ObjectModel::TYPE_DATE,
                'validate' => 'isDate',
            ),
            'date_transaction'     => array(
                'type'     => ObjectModel::TYPE_DATE,
                'validate' => 'isDate',
            ),
        ),
    );

    public function getLinkToTransaction()
    {
        throw new \Exception('Need to define the method ' . __FUNCTION__);
    }

    public function getDateTransaction()
    {
        if ($this->date_transaction == '0000-00-00 00:00:00' || $this->date_transaction == false) {
            return '';
        }
        $datetime1 = new \DateTime($this->date_transaction);
        $datetime2 = new \DateTime($this->date_add);
        $diff = $datetime2->getOffset() / 3600;
        $interval = $datetime2->diff($datetime1);
        if ($interval->invert == 1) {
            $diff -= (int)$interval->format('%h');
        } else {
            $diff += (int)$interval->format('%h');
        }
        if ($diff == 0) {
            $diff = 'GMT';
        } elseif ($diff > 0) {
            $diff = "+" . $diff;
        }

        try {
            $dateTimeZone = new \DateTimeZone($diff);
            $date = new \DateTime($this->date_transaction, $dateTimeZone);
            return $date->format('Y-m-d H:i:s T');
        } catch (\Exception $e) {
            return '';
        }
    }
}
