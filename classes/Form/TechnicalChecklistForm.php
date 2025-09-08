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

namespace PaypalAddons\classes\Form;

use PaypalAddons\classes\AbstractMethodPaypal;
use PaypalAddons\classes\Webhook\WebhookOption;
use PaypalAddons\services\Checker;

if (!defined('_PS_VERSION_')) {
    exit;
}

class TechnicalChecklistForm implements FormInterface
{
    protected $module;

    protected $method;

    protected $webhookOption;

    protected $checker;

    protected $context;

    public function __construct()
    {
        $this->module = \Module::getInstanceByName('paypal');
        $this->method = AbstractMethodPaypal::load();
        $this->webhookOption = new WebhookOption();
        $this->checker = new Checker();
        $this->context = \Context::getContext();
    }

    /**
     * @return array
     */
    public function getDescription()
    {
        $countryDefault = new \Country((int) \Configuration::get('PS_COUNTRY_DEFAULT'), $this->context->language->id);

        $tpl_vars = [
            'merchantCountry' => $countryDefault->name,
            'tlsVersion' => $this->checker->checkTLSVersion(),
            'accountConfigured' => $this->method == null ? false : $this->method->isConfigured(),
            'sslActivated' => $this->module->isSslActive(),
            'localizationUrl' => $this->context->link->getAdminLink('AdminLocalization', true),
            'numberRedundantFiles' => count($this->module->getRedundantFiles()),
            'diagnosticPage' => $this->context->link->getAdminLink('AdminPaypalDiagnostic', true),
        ];

        if ($this->webhookOption->isEligibleContext()) {
            $tpl_vars['showWebhookState'] = true;

            if ($this->webhookOption->isEnable()) {
                $webhookCheck = $this->checker->checkWebhook();
                $tpl_vars['isWebhookEnabled'] = true;
                $tpl_vars['webhookState'] = $webhookCheck['state'];
                $tpl_vars['webhookStateMsg'] = $webhookCheck['message'];
            } else {
                $tpl_vars['isWebhookEnabled'] = false;
            }
        }

        return [
            'legend' => [
                'title' => '',
            ],
            'fields' => [
                'technicalChecklist' => [
                    'type' => 'varialble-set',
                    'set' => $tpl_vars,
                ],
            ],
            'submit' => [
                'title' => '',
                'name' => '',
            ],
            'id_form' => 'pp_technical_checklist_form',
        ];
    }

    /**
     * @return bool
     */
    public function save($data = null)
    {
        return true;
    }
}
