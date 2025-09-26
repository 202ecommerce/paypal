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

class Response
{
    /** @var bool */
    protected $success;

    /** @var Error|null */
    protected $error;

    protected $data;

    /**
     * @return bool
     */
    public function isSuccess()
    {
        return $this->success;
    }

    public function getError()
    {
        if ($this->error instanceof Error) {
            return $this->error;
        }

        return new Error();
    }

    public function setSuccess($success)
    {
        $this->success = (bool) $success;

        return $this;
    }

    public function getData()
    {
        return $this->data;
    }

    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    public function setError(Error $error)
    {
        $this->error = $error;

        return $this;
    }

    public function getLink($type)
    {
        if (empty($this->data->result->links)) {
            return null;
        }
        foreach ($this->data->result->links as $link) {
            if ($link->rel === $type) {
                return $link->href;
            }
        }

        return null;
    }
}
