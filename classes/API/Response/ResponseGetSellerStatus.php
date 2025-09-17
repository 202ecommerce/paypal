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

namespace PaypalAddons\classes\API\Response;

if (!defined('_PS_VERSION_')) {
    exit;
}

class ResponseGetSellerStatus extends Response
{
    protected $products = [];

    protected $capabilities = [];

    protected $productsFull = [];

    protected $capabilitiesFull = [];

    /**
     * @return array
     */
    public function getProducts()
    {
        return $this->products;
    }

    /**
     * @param array $products
     *
     * @return ResponseGetSellerStatus
     */
    public function setProducts($products)
    {
        if (false == is_array($products)) {
            return $this;
        }

        $this->products = $products;

        return $this;
    }

    /**
     * @return array
     */
    public function getCapabilities()
    {
        return $this->capabilities;
    }

    /**
     * @param array $capabilities
     *
     * @return ResponseGetSellerStatus
     */
    public function setCapabilities($capabilities)
    {
        if (false == is_array($capabilities)) {
            return $this;
        }

        $this->capabilities = $capabilities;

        return $this;
    }

    /**
     * @return array
     */
    public function getProductsFull()
    {
        return $this->productsFull;
    }

    /**
     * @param array $productsFull
     *
     * @return ResponseGetSellerStatus
     */
    public function setProductsFull($productsFull)
    {
        if (false == is_array($productsFull)) {
            return $this;
        }

        $this->productsFull = $productsFull;

        return $this;
    }

    /**
     * @return array
     */
    public function getCapabilitiesFull()
    {
        return $this->capabilitiesFull;
    }

    /**
     * @param array $capabilitiesFull
     *
     * @return ResponseGetSellerStatus
     */
    public function setCapabilitiesFull($capabilitiesFull)
    {
        if (false == is_array($capabilitiesFull)) {
            return $this;
        }

        $this->capabilitiesFull = $capabilitiesFull;

        return $this;
    }
}
