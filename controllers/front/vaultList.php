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

use PaypalAddons\classes\AbstractMethodPaypal;
use PaypalAddons\services\ServicePaypalVaulting;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Prepare EC payment
 */
class PaypalVaultListModuleFrontController extends ModuleFrontController
{
    public $auth = true;

    protected $paypalVaultingService;

    protected $method;

    protected $request;

    public function __construct()
    {
        parent::__construct();

        $this->paypalVaultingService = new ServicePaypalVaulting();
        $this->method = AbstractMethodPaypal::load();
        $this->request = Request::createFromGlobals();
    }

    public function checkAccess()
    {
        if (empty($this->request->get('token')) || $this->request->get('token') != $this->context->customer->secure_key) {
            Tools::redirect(
                $this->context->link->getPageLink('my-account')
            );
        }

        return parent::checkAccess();
    }

    public function setMedia()
    {
        $this->registerJavascript(
            'paypal-vault-list-page',
            '/modules/paypal/views/js/vaultListPage.js',
            ['position' => 'bottom', 'priority' => 0]
        );

        return parent::setMedia();
    }

    public function initContent()
    {
        $this->context->smarty->assign('vaultList', $this->prepareVaultList());
        $this->setTemplate('module:paypal/views/templates/front/vaulting-list.tpl');
        parent::initContent();
    }

    protected function prepareVaultList()
    {
        $paypalVaultingList = $this->paypalVaultingService->getVaultListByCustomer((int) $this->context->customer->id);
        $preparedVaultList = [];
        /** @var PaypalVaulting $paypalVaulting */
        foreach ($paypalVaultingList as $paypalVaulting) {
            $response = $this->method->getVaultPaymentToken($paypalVaulting->vault_id);

            if (!$response) {
                continue;
            }

            $preparedVaultList[] = [
                'id' => $paypalVaulting->id,
                'paymentSource' => $response->getPaymentSourceInfo(),
            ];
        }

        return $preparedVaultList;
    }

    public function displayAjaxRemovePaypalVaulting()
    {
        $response = new JsonResponse();
        $paypalVaulting = new PaypalVaulting((int) $this->request->get('id'));

        if (false === Validate::isLoadedObject($paypalVaulting)) {
            return $response->setData([
                'success' => false,
                'message' => 'PayPal vaulting is not found',
            ])->send();
        }

        if (!$paypalVaulting->id_customer || $paypalVaulting->id_customer != $this->context->customer->id) {
            return $response->setData([
                'success' => false,
                'message' => 'PayPal vaulting is not found',
            ])->send();
        }

        if (false === $this->method->deleteVaultPaymentToken($paypalVaulting->vault_id)) {
            return $response->setData([
                'success' => false,
                'message' => 'Deleting the payment token is failed',
            ])->send();
        }

        $paypalVaulting->delete();

        return $response->setData([
            'success' => true,
        ])->send();
    }
}
