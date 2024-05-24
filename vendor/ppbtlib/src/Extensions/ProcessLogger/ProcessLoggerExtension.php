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
namespace PaypalPPBTlib\Extensions\ProcessLogger;

use PaypalPPBTlib\Extensions\AbstractModuleExtension;
use PaypalPPBTlib\Extensions\ProcessLogger\Controllers\Admin\AdminProcessLoggerController;
use PaypalPPBTlib\Extensions\ProcessLogger\Classes\ProcessLoggerObjectModel;

/**
 * @import 'paypal/views/templates/hook/tableLogs.tpl'
 */
class ProcessLoggerExtension extends AbstractModuleExtension
{
    public $name = 'process_logger';

    public $extensionAdminControllers = array(
        array(
            'name' => array(
                'en' => 'Logger',
                'fr' => 'Logger',
            ),
            'class_name' => 'AdminPaypalProcessLogger',
            'parent_class_name' => 'AdminParentPaypalConfiguration',
            'visible' => true,
        ),
    );

    public $objectModels = array(
        ProcessLoggerObjectModel::class
    );

    public function hookDisplayAdminOrderContentOrder($params)
    {
        /** @var $order \Order*/
        $order = $params['order'];
        if ($order->module != 'paypal') {
            return;
        }
        if (isset($params['class_logger']) && is_subclass_of($params['class_logger'], ProcessLoggerObjectModel::class)) {
            $class_logger = $params['class_logger'];
        } else {
            $class_logger = ProcessLoggerObjectModel::class;
        }
        $collectionLogs = new \PrestaShopCollection($class_logger);
        $collectionLogs->where('id_cart', '=', $params['order']->id_cart);
        \Context::getContext()->smarty->assign('logs', $collectionLogs->getResults());
        return \Context::getContext()->smarty->fetch(_PS_MODULE_DIR_ . 'paypal/views/templates/hook/displayAdminOrderContentOrder.tpl');
    }

    public function hookDisplayAdminOrderTabOrder($params)
    {
        /** @var $order \Order*/
        $order = $params['order'];
        if ($order->module != 'paypal') {
            return;
        }
        if (isset($params['class_logger']) && is_subclass_of($params['class_logger'], ProcessLoggerObjectModel::class)) {
            $class_logger = $params['class_logger'];
        } else {
            $class_logger = ProcessLoggerObjectModel::class;
        }
        $collectionLogs = new \PrestaShopCollection($class_logger);
        $collectionLogs->where('id_cart', '=', $params['order']->id_cart);
        \Context::getContext()->smarty->assign('logs', $collectionLogs->getResults());
        \Context::getContext()->smarty->assign('psVersion', _PS_VERSION_);
        return \Context::getContext()->smarty->fetch(_PS_MODULE_DIR_ . 'paypal/views/templates/hook/displayAdminOrderTabOrder.tpl');
    }

    public function hookDisplayAdminCartsView($params)
    {

        /** @var $cart Cart */
        $cart = $params['cart'];
        $order = new \Order((int)\Order::getIdByCartId($cart->id));
        if (\Validate::isLoadedObject($order) && $order->module != 'paypal') {
            return;
        }
        if (isset($params['class_logger']) && is_subclass_of($params['class_logger'], ProcessLoggerObjectModel::class)) {
            $class_logger = $params['class_logger'];
        } else {
            $class_logger = ProcessLoggerObjectModel::class;
        }
        $collectionLogs = new \PrestaShopCollection($class_logger);
        $collectionLogs->where('id_cart', '=', $params['cart']->id);

        if ($collectionLogs->count() == 0) {
            return;
        }

        \Context::getContext()->smarty->assign('logs', $collectionLogs->getResults());
        return \Context::getContext()->smarty->fetch(_PS_MODULE_DIR_ . 'paypal/views/templates/hook/displayAdminCartsView.tpl');
    }

    public function hookDisplayOrderPreview($params)
    {
        $order = new \Order((int)$params['order_id']);

        if ($order->module != 'paypal') {
            return;
        }

        if (isset($params['class_logger']) && is_subclass_of($params['class_logger'], ProcessLoggerObjectModel::class)) {
            $class_logger = $params['class_logger'];
        } else {
            $class_logger = ProcessLoggerObjectModel::class;
        }

        $collectionLogs = new \PrestaShopCollection($class_logger);
        $collectionLogs->where('id_order', '=', $order->id);
        $collectionLogs
            ->orderBy('date_add', 'desc');

        if ($collectionLogs->count() == 0) {
            return;
        }

        $log = $collectionLogs->getResults();
        \Context::getContext()->smarty->assign('log', $collectionLogs->getFirst());

        return \Context::getContext()->smarty->fetch(_PS_MODULE_DIR_ . 'paypal/views/templates/hook/displayOrderPreview.tpl');
    }
}
