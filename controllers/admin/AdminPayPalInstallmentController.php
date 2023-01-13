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
require_once _PS_MODULE_DIR_ . 'paypal/vendor/autoload.php';

use PaypalAddons\classes\AdminPayPalController;
use PaypalAddons\classes\Form\Controller\AdminPayPalInstallment\FormInstallment;
use PaypalAddons\classes\Form\FormInterface;
use PaypalAddons\classes\InstallmentBanner\Banner;
use Symfony\Component\HttpFoundation\JsonResponse;

class AdminPayPalInstallmentController extends AdminPayPalController
{
    protected $headerToolBar = true;

    /** @var array<string, FormInterface> */
    protected $forms;

    public function __construct()
    {
        parent::__construct();

        $this->forms['formInstallment'] = new FormInstallment();
    }

    public function initContent()
    {
        parent::initContent();

        $this->initFormInstallment();
        $this->context->smarty->assign('formInstallment', $this->renderForm());
        $content = $this->context->smarty->fetch($this->getTemplatePath() . 'installment.tpl');
        $this->context->smarty->assign('content', $content);
        $this->addJS(_PS_MODULE_DIR_ . $this->module->name . '/views/js/adminInstallment.js');
    }

    protected function initFormInstallment()
    {
        $this->fields_form['form']['form'] = $this->forms['formInstallment']->getFields();
        $this->tpl_form_vars = array_merge(
            $this->tpl_form_vars,
            $this->forms['formInstallment']->getValues()
        );
    }

    public function saveForm()
    {
        return $this->forms['formInstallment']->save();
    }

    public function displayAjaxGetBanner()
    {
        $jsonResponse = new JsonResponse();
        $banner = new Banner();
        $jsonResponse->setData([
            'success' => true,
            'content' => $banner->render(),
        ]);

        return $jsonResponse->send();
    }
}
