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

namespace PaypalAddons\classes\API\Injector;

use PaypalAddons\classes\API\InjectorInterface;
use PaypalAddons\classes\API\Request\HttpRequestInterface;

if (!defined('_PS_VERSION_')) {
    exit;
}

class UserAgentInjector implements InjectorInterface
{
    /**
     * @var \PayPal
     */
    protected $module;

    public function __construct()
    {
        /* @phpstan-ignore-next-line */
        $this->module = \Module::getInstanceByName('paypal');
    }

    public function inject(&$object)
    {
        if (false === $object instanceof HttpRequestInterface) {
            return;
        }

        $headers = $object->getHeaders();

        if (isset($headers['User-Agent'])) {
            return;
        }

        $headers['User-Agent'] = $this->getUserAgent();
        $object->setHeaders($headers);
    }

    protected function getUserAgent()
    {
        return sprintf(
            'PrestaShop/%s ModuleVersion/%s PHP/%s OS/%s Machine/%s',
            _PS_VERSION_,
            $this->module->version,
            PHP_VERSION,
            function_exists('php_uname') ? str_replace(' ', '_', php_uname('s') . ' ' . php_uname('r')) : 'Undefined',
            function_exists('php_uname') ? php_uname('m') : 'Undefined'
        );
    }
}
