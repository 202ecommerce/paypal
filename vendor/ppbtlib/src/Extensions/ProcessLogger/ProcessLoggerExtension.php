<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to a commercial license from SARL 202 ecommerce
 * Use, copy, modification or distribution of this source file without written
 * license agreement from the SARL 202 ecommerce is strictly forbidden.
 * In order to obtain a license, please contact us: tech@202-ecommerce.com
 * ...........................................................................
 * INFORMATION SUR LA LICENCE D'UTILISATION
 *
 * L'utilisation de ce fichier source est soumise a une licence commerciale
 * concedee par la societe 202 ecommerce
 * Toute utilisation, reproduction, modification ou distribution du present
 * fichier source sans contrat de licence ecrit de la part de la SARL 202 ecommerce est
 * expressement interdite.
 * Pour obtenir une licence, veuillez contacter 202-ecommerce <tech@202-ecommerce.com>
 * ...........................................................................
 *
 * @author    202-ecommerce <tech@202-ecommerce.com>
 * @copyright Copyright (c) 202-ecommerce
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @version   develop
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