<?php

use PaypalAddons\classes\Form\AccountForm;
use PaypalAddons\classes\Form\CheckoutForm;
use PaypalAddons\classes\Form\FormInstallment;
use PaypalAddons\classes\Form\OrderStatusForm;
use PaypalAddons\classes\Form\ShortcutConfigurationForm;
use PaypalAddons\classes\Form\TrackingParametersForm;
use PaypalAddons\classes\Form\WhiteListForm;
use Symfony\Component\HttpFoundation\JsonResponse;

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
class AdminPaypalConfigurationController extends \ModuleAdminController
{
    public $bootstrap = false;

    protected $forms = [];

    public function __construct()
    {
        parent::__construct();

        $this->initForms();
    }

    protected function initForms()
    {
        $this->forms['checkoutForm'] = new CheckoutForm();
        $this->forms['trackingForm'] = new TrackingParametersForm();
        $this->forms['formInstallment'] = new FormInstallment();
        $this->forms['whiteListForm'] = new WhiteListForm();
        $this->forms['accountForm'] = new AccountForm();
        $this->forms['orderStatusForm'] = new OrderStatusForm();
        $this->forms['shortcutConfigurationForm'] = new ShortcutConfigurationForm();
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);

        $this->addJS(_PS_MODULE_DIR_ . 'paypal/views/js/admin.js');
        $this->addCSS(_PS_MODULE_DIR_ . 'paypal/views/css/paypal_bo.css');
    }

    public function initContent()
    {
        $this->content .= $this->renderConfiguration();
        parent::initContent();
    }

    protected function renderConfiguration()
    {
        $tpl = $this->context->smarty->createTemplate($this->getTemplatePath() . 'admin.tpl');
        /** @var \PaypalAddons\classes\Form\FormInterface $form*/
        foreach ($this->forms as $formName => $form) {
            $tpl->assign($formName, $form->getDescription());
        }

        $tpl->assign([
            'moduleDir' => _MODULE_DIR_ . $this->module->name,
        ]);

        return $tpl->fetch();
    }

    public function ajaxProcessSaveForm()
    {
        $data = [];
        $response = new JsonResponse();

        try {
            /** @var \PaypalAddons\classes\Form\FormInterface $form*/
            foreach ($this->forms as $form) {
                $form->save();
            }

            $data['success'] = true;
        } catch (Throwable $e) {
            $data['success'] = false;
        } catch (Exception $e) {
            $data['success'] = false;
        }

        $response->setData($data);
        $response->send();
        die;
    }
}
