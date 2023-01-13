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

use PaypalAddons\classes\AbstractMethodPaypal;

/**
 * Init payment for EC shortcut
 */
class PaypalScInitModuleFrontController extends PaypalAbstarctModuleFrontController
{
    /* @var $method AbstractMethodPaypal*/
    protected $method;

    public function init()
    {
        parent::init();
        $this->values['source_page'] = Tools::getvalue('source_page');
        $this->values['checkAvailability'] = Tools::getvalue('checkAvailability');
        $this->values['id_product'] = Tools::getvalue('id_product');
        $this->values['product_attribute'] = Tools::getvalue('product_attribute');
        $this->values['id_product_attribute'] = Tools::getvalue('id_product_attribute');
        $this->values['quantity'] = Tools::getvalue('quantity');
        $this->values['combination'] = Tools::getvalue('combination');
        $this->values['getToken'] = Tools::getvalue('getToken');
        $this->values['credit_card'] = 0;
        $this->values['short_cut'] = 1;
        if ($this->module->paypal_method == 'MB') {
            $methodType = 'EC';
        } else {
            $methodType = $this->module->paypal_method;
        }
        $this->setMethod(AbstractMethodPaypal::load($methodType));
    }

    public function displayAjaxCheckAvailability()
    {
        $request = $this->getRequest();

        switch ($request->page) {
            case 'cart':
                if ($this->context->cart->checkQuantities() && $this->context->cart->nbProducts()) {
                    $this->jsonValues = ['success' => true];
                } else {
                    $this->jsonValues = ['success' => false];
                }
                break;
            case 'product':
                $product = new Product((int) $request->idProduct);
                $group = $this->parseCombination($request->combination);

                if (false == empty($group)) {
                    $product->id_product_attribute = $this->module->getIdProductAttributeByIdAttributes($request->idProduct, $group);
                }

                if ($product->checkQty($request->quantity)) {
                    $this->jsonValues = ['success' => true];
                } else {
                    $this->jsonValues = ['success' => false];
                }
                break;
            default:
        }
    }

    protected function parseCombination($combination)
    {
        $temp_group = explode('|', $combination);
        $group = [];

        foreach ($temp_group as $item) {
            $temp = explode(':', $item);

            if (count($temp) == 1) {
                continue;
            }

            $temp = array_map(
                function ($value) {
                    return trim($value);
                },
                $temp
            );
            $group[$temp[0]] = $temp[1];
        }

        return $group;
    }

    public function prepareProduct()
    {
        if (empty($this->context->cart->id)) {
            $this->context->cart->add();
            $this->context->cookie->id_cart = $this->context->cart->id;
            $this->context->cookie->write();
        } else {
            // delete all product in cart
            $products = $this->context->cart->getProducts();
            foreach ($products as $product) {
                $this->context->cart->deleteProduct($product['id_product'], $product['id_product_attribute'], $product['id_customization'], $product['id_address_delivery']);
            }
        }

        if ($this->values['combination']) {
            // build group for search product attribute
            $group = $this->parseCombination($this->values['combination']);
            $this->context->cart->updateQty($this->values['quantity'], $this->values['id_product'], $this->module->getIdProductAttributeByIdAttributes($this->values['id_product'], $group));
        } else {
            $this->context->cart->updateQty($this->values['quantity'], $this->values['id_product']);
        }
    }

    public function setMethod($method)
    {
        $this->method = $method;
    }

    public function displayAjaxCreateOrder()
    {
        $request = $this->getRequest();

        if ($request->page == 'product') {
            $this->values['quantity'] = $request->quantity;
            $this->values['id_product'] = $request->idProduct;
            $this->values['combination'] = $request->combination;

            $this->prepareProduct();
        }

        if (empty($request->addAddress)) {
            $this->method->setShortCut(true);
        }

        if (empty($request->apmMethod)) {
            $this->method->init();
        } else {
            $this->method->initApm($request->apmMethod);
        }

        $this->jsonValues = ['success' => true, 'idOrder' => $this->method->getPaymentId()];
    }

    public function getRequest()
    {
        return json_decode(Tools::file_get_contents('php://input'));
    }
}
