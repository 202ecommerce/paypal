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

namespace PaypalAddons\classes\Shortcut;

use Context;
use Exception;
use Hook;
use Module;
use PaypalAddons\classes\AbstractMethodPaypal;
use PaypalAddons\services\PaypalMedia;
use Throwable;

abstract class ShortcutAbstract
{
    /** @var Context */
    protected $context;

    /** @var Module */
    protected $module;

    /** @var AbstractMethodPaypal */
    protected $method;

    /** @var string */
    protected $id;

    public function __construct()
    {
        $this->context = Context::getContext();
        $this->module = Module::getInstanceByName('paypal');
        $this->method = AbstractMethodPaypal::load($this->getMethodType());
        $this->setId(uniqid());
    }

    /**
     * @return string html
     */
    public function render()
    {
        $this->context->smarty->assign($this->getTplVars());
        $this->context->smarty->assign('JSvars', $this->getJSvars());
        $this->context->smarty->assign('JSscripts', $this->getJS());
        $this->context->smarty->assign('psPaypalDir', _PS_MODULE_DIR_ . 'paypal');

        return $this->context->smarty->fetch($this->getTemplatePath());
    }

    /**
     * @return []
     */
    protected function getJSvars()
    {
        $JSvars = [];
        $JSvars['sc_init_url'] = $this->context->link->getModuleLink($this->module->name, 'ScInit', [], true);
        $JSvars['scOrderUrl'] = $this->context->link->getModuleLink($this->module->name, 'scOrder', [], true);
        $JSvars['styleSetting'] = $this->getStyleSetting();

        return $JSvars;
    }

    /**
     * @return []
     */
    protected function getJS()
    {
        $JSscripts = [];

        if ($this->isAddJquery()) {
            foreach ($this->getJqueryPath() as $index => $lib) {
                $JSscripts['jq-lib-' . $index] = ['src' => $lib];
            }
        }

        $JSscripts['tot-paypal-sdk'] = [
            'src' => $this->method->getUrlJsSdkLib(['components' => 'buttons,marks']),
            'data-namespace' => 'totPaypalSdkButtons',
        ];
        $JSscripts['shortcut'] = [
            'src' => __PS_BASE_URI__ . 'modules/' . $this->module->name . '/views/js/shortcut.js?v=' . $this->module->version,
        ];

        return $JSscripts;
    }

    /**
     * @return string|null
     */
    protected function getMethodType()
    {
        return null;
    }

    /**
     * @return string
     */
    protected function getTemplatePath()
    {
        return 'module:paypal/views/templates/shortcut/shortcut-layout.tpl';
    }

    /**
     * @return []
     */
    abstract protected function getTplVars();

    protected function getStyleSetting()
    {
        $styleSetting = [];
        $styleSetting['label'] = 'pay';
        $styleSetting['height'] = 35;

        return $styleSetting;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return (string) $this->id;
    }

    /**
     * @param string $id
     *
     * @return ShortcutAbstract
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /** @return []*/
    public function getJqueryPath()
    {
        return $this->getPaypalMedia()->getJqueryPath();
    }

    /** @return PaypalMedia*/
    public function getPaypalMedia()
    {
        return new PaypalMedia();
    }

    public function isAddJquery()
    {
        $isAddJquery = version_compare(_PS_VERSION_, '1.7.7', '<');

        try {
            Hook::exec('actionPaypalShortcutIsAddJquery', ['isAddJquery' => &$isAddJquery]);
        } catch (Throwable $e) {
        } catch (Exception $e) {
        }

        return $isAddJquery;
    }
}
