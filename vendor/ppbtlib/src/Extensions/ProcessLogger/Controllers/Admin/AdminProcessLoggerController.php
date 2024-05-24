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

namespace PaypalPPBTlib\Extensions\ProcessLogger\Controllers\Admin;

use PaypalPPBTlib\Extensions\ProcessLogger\Classes\ProcessLoggerObjectModel;

use \Db;
use \Tools;
use \Configuration;

class AdminProcessLoggerController extends \ModuleAdminController
{
    /** @var bool $bootstrap Active bootstrap for Prestashop 1.6 */
    public $bootstrap = true;

    /** @var \Module Instance of your module automatically set by ModuleAdminController */
    public $module;

    /** @var string Associated object class name */
    public $className = 'PaypalPPBTlib\Extensions\ProcessLogger\Classes\ProcessLoggerObjectModel';

    /** @var string Associated table name */
    public $table = 'paypal_processlogger';

    /** @var string|false Object identifier inside the associated table */
    public $identifier = 'id_paypal_processlogger';

    /** @var string Default ORDER BY clause when $_orderBy is not defined */
    protected $_defaultOrderBy = 'id_paypal_processlogger';

    /** @var string Default ORDER WAY clause when $_orderWay is not defined */
    protected $_defaultOrderWay = 'DESC';

    /** @var bool List content lines are clickable if true */
    protected $list_no_link = true;

    public $multishop_context = 0;

    /**
     * @see AdminController::__construct()
     */
    public function __construct()
    {
        parent::__construct();

        $this->addRowAction('delete');

        $this->bulk_actions = array(
            'delete' => array(
                'text' => $this->module->l('Delete selected', 'AdminProcessLoggerController'),
                'confirm' => $this->module->l('Would you like to delete the selected items?', 'AdminProcessLoggerController'),
            )
        );

        $this->fields_list = array(
            'id_paypal_processlogger' => array(
                'title'  => $this->module->l('ID', 'AdminProcessLoggerController'),
                'align'  => 'center',
                'class'  => 'fixed-width-xs',
                'search' => true,
            ),
            'sandbox' => array(
                'title' => $this->module->l('Mode', 'AdminProcessLoggerController'),
                'type' => 'select',
                'list' => array(1 => 'Sandbox', 0 => 'Live'),
                'filter_key' => 'a!sandbox',
                'filter_type' => 'int',
                'callback' => 'getMode',
            ),
            'tools' => array(
                'title' => $this->module->l('Payment tools', 'AdminProcessLoggerController'),
            ),
            'id_cart' => array(
                'title'    => $this->module->l('ID cart', 'AdminProcessLoggerController'),
                'callback' => 'getCartLink',
            ),
            'id_order' => array(
                'title' => $this->module->l('ID order', 'AdminProcessLoggerController'),
                'callback' => 'getOrderLink',
            ),
            'id_transaction' => array(
                'title'    => $this->module->l('Transaction ID', 'AdminProcessLoggerController'),
                'callback' => 'getLinkToTransaction',
            ),
            'status' => array(
                'title' => $this->module->l('Level', 'AdminProcessLoggerController'),
                'callback' => 'getLevel'
            ),
            'log' => array(
                'title' => $this->module->l('Message (AIP PayPal/Braintree response)', 'AdminProcessLoggerController'),
            ),
            'id_shop' => array(
                'title' => $this->module->l('Shop ID', 'AdminProcessLoggerController'),
            ),
            'date_add' => array(
                'title' => $this->module->l('Date', 'AdminProcessLoggerController'),
            ),
            'date_transaction' => array(
                'title' => $this->module->l('Transaction date', 'AdminProcessLoggerController'),
                'callback' => 'getDateTransaction',
            ),
        );

        $this->fields_options = array(
            'processLogger' => array(
                'image'       => '../img/admin/cog.gif',
                'title'       => $this->module->l('Process Logger Settings', 'AdminProcessLoggerController'),
                'description' => $this->module->l('Here you can change the default configuration for this Process Logger', 'AdminProcessLoggerController'),
                'info' => $this->module->displayWarning(
                    $this->module->l('Logs with order ID will not be erased.', 'AdminProcessLoggerController')
                ),
                'fields'      => array(
                    'PAYPAL_EXTLOGS_ERASING_DISABLED' => array(
                        'title'        => $this->module->l('Disable auto erasing', 'AdminProcessLoggerController'),
                        'hint'         => $this->module->l('If disabled, logs will be automatically erased after the delay', 'AdminProcessLoggerController'),
                        'validation'   => 'isBool',
                        'cast'         => 'intval',
                        'type'         => 'bool',
                    ),
                    'PAYPAL_EXTLOGS_ERASING_DAYSMAX' => array(
                        'title'        => $this->module->l('Auto erasing delay (in days)', 'AdminProcessLoggerController'),
                        'hint'         => $this->module->l('Choose the number of days you want to keep logs in database', 'AdminProcessLoggerController'),
                        'validation'   => 'isInt',
                        'cast'         => 'intval',
                        'type'         => 'text',
                        'defaultValue' => 90,
                    ),
                ),
                'submit'      => array(
                    'title' => $this->module->l('Save', 'AdminProcessLoggerController'),
                    'name' => 'submitSaveConf'),
            ),
        );
    }

    /**
     * @param $echo string Value of field
     * @param $tr array All data of the row
     * @return string
     */
    public function getObjectId($echo, $tr)
    {
        unset($tr);
        return empty($echo) ? '' : $echo;
    }

    public function getDateTransaction($date_transaction, $tr)
    {
        if ((int)$tr['id_paypal_processlogger'] == false) {
            return '';
        }
        $collectionLogs = new \PrestaShopCollection($this->className);
        $collectionLogs->where('id_paypal_processlogger', '=', (int)$tr['id_paypal_processlogger']);
        $log = $collectionLogs->getFirst();
        if (\Validate::isLoadedObject($log) == false) {
            return '';
        }
        return $log->getDateTransaction();
    }

    public function getLinkToTransaction($id_transaction, $tr)
    {
        if ($id_transaction == false || (int)$tr['id_paypal_processlogger'] == false) {
            return '';
        }
        $collectionLogs = new \PrestaShopCollection($this->className);
        $collectionLogs->where('id_paypal_processlogger', '=', (int)$tr['id_paypal_processlogger']);
        $log = $collectionLogs->getFirst();
        if (\Validate::isLoadedObject($log) == false) {
            return '';
        }
        $link = $log->getLinkToTransaction();
        return '<a href="' . $link . '" target="_blank">' . $log->id_transaction . '</a>';
    }

    public function getCartLink($id_cart)
    {
        if ((int)$id_cart) {
            $link = \Context::getContext()->link->getAdminLink('AdminCarts', true, array(), array('id_cart' => (int)$id_cart, 'viewcart' => 1));
            return '<a href="' . $link . '" target="_blank">' . $id_cart . '</a>';
        }
    }

    public function getOrderLink($id_order)
    {
        if ((int)$id_order) {
            $link = \Context::getContext()->link->getAdminLink('AdminOrders', true, array(), array('id_order' => (int)$id_order, 'vieworder' => 1));
            return '<a href="' . $link . '" target="_blank">' . $id_order . '</a>';
        }
    }

    /**
     * @param $sandbox int Value of field
     * @param $tr array All data of the row
     * @return string
     */
    public function getMode($sandbox, $tr)
    {
        unset($tr);
        if ((int)$sandbox) {
            return 'Sandbox';
        } else {
            return 'Live';
        }
    }

    /**
     * @param $echo string Value of field
     * @param $tr array All data of the row
     * @return string
     */
    public function getLevel($echo, $tr)
    {
        unset($tr);
        switch (strtolower($echo)) {
            case 'info':
                $echo = '<span class="badge badge-info">'.$echo.'</span>';
                break;
            case 'success':
                $echo = '<span class="badge badge-success">'.$echo.'</span>';
                break;
            case 'error':
                $echo = '<span class="badge badge-danger">'.$echo.'</span>';
                break;
        }
        return $echo;
    }

    /**
     * @see AdminController::initPageHeaderToolbar()
     */
    public function initPageHeaderToolbar()
    {
        parent::initPageHeaderToolbar();
        // Remove the help icon of the toolbar which no useful for us
        $this->context->smarty->clearAssign('help_link');
    }

    /**
     * @see AdminController::initToolbar()
     */
    public function initToolbar()
    {
        parent::initToolbar();
        // Remove the add new item button
        unset($this->toolbar_btn['new']);
        $this->toolbar_btn['delete'] = array(
            'short' => 'Erase',
            'desc' => $this->module->l('Erase all'),
            'js' => 'if (confirm(\''.
                $this->module->l('Are you sure?', 'AdminProcessLoggerController').
                '\')) document.location = \''.self::$currentIndex.'&amp;token='.$this->token.'&amp;action=erase\';'
        );
    }

    /**
     * Delete all logs
     *
     * @return bool
     */
    public function processErase()
    {
        $result = Db::getInstance()->delete($this->table);

        if ($result) {
            $this->confirmations[] = $this->module->l('All logs have been erased', 'AdminProcessLoggerController');
        }

        return $result;
    }

    public function postProcess()
    {
        if (Tools::isSubmit('submitSaveConf')) {
            return $this->saveConfiguration();
        }

        return parent::postProcess();
    }

    public function saveConfiguration()
    {
        $shops = \Shop::getShops();
        foreach ($shops as $shop) {
            $extlogs_erasing_daysmax = \Tools::getValue('PAYPAL_EXTLOGS_ERASING_DAYSMAX');
            $extlogs_erasing_disabled = \Tools::getValue('PAYPAL_EXTLOGS_ERASING_DISABLED');

            Configuration::updateValue(
                'PAYPAL_EXTLOGS_ERASING_DISABLED',
                ($extlogs_erasing_disabled ? true : false),
                false,
                null,
                $shop['id_shop']
            );

            if (!is_numeric($extlogs_erasing_daysmax)) {
                $this->errors[] = $this->module->l(
                    'You must specify a valid \"Auto erasing delay (in days)\" number.', 'AdminProcessLoggerController');
            } else {
                \Configuration::updateValue(
                    'PAYPAL_EXTLOGS_ERASING_DAYSMAX',
                    $extlogs_erasing_daysmax,
                    false,
                    null,
                    $shop['id_shop']
                );
                $this->confirmations[] = $this->module->l('Successful update.', 'AdminProcessLoggerController');
            }
        }

        return true;
    }
}
