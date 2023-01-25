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

namespace PaypalAddons\classes\PUI;

use Address;
use Context;
use Exception;
use PaypalAddons\classes\AbstractMethodPaypal;
use Throwable;

class FraudNetForm
{
    protected $context;

    protected $method;

    public function __construct()
    {
        $this->context = Context::getContext();
        $this->method = AbstractMethodPaypal::load('PPP');
    }

    /**
     * @return string
     */
    public function render()
    {
        $this->context->smarty->assign('sessionId', $this->getFraudSessionId()->buildSessionId());
        $this->context->smarty->assign('sourceId', $this->getSourceId());
        $this->context->smarty->assign('isSandbox', $this->method->isSandbox());
        $this->context->smarty->assign('userData', $this->getUserData());
        $this->context->smarty->assign('JSscripts', $this->getScripts());
        $this->context->smarty->assign('psPaypalDir', _PS_MODULE_DIR_ . 'paypal');

        try {
            return Context::getContext()->smarty->fetch('module:paypal/views/templates/pui/fraudNetForm.tpl');
        } catch (Throwable $e) {
            return '';
        } catch (Exception $e) {
            return '';
        }
    }

    protected function getFraudSessionId()
    {
        return new FraudSessionId();
    }

    protected function getSourceId()
    {
        return (new PsMerchantId($this->method))->get() . '-checkout-page';
    }

    protected function getUserData()
    {
        $billingAddress = new Address($this->context->cart->id_address_invoice);
        $userData = new DataUserForm();

        $userData->setFirstName($this->context->customer->firstname);
        $userData->setLastName($this->context->customer->lastname);
        $userData->setEmail($this->context->customer->email);
        $userData->setPhone($billingAddress->phone);

        return $userData;
    }

    protected function getScripts()
    {
        return [
            'paypal-tools' => [
                'src' => __PS_BASE_URI__ . 'modules/paypal/views/js/tools.js',
            ],
        ];
    }
}
