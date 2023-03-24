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

use Configuration;

class ShortcutSignup extends ShortcutCart
{
    /** @var bool */
    protected $isWidget;

    public function __construct()
    {
        parent::__construct();
        $this->setIsWidget(false);
    }

    protected function getTemplatePath()
    {
        if ($this->isWidget()) {
            return parent::getTemplatePath();
        }

        return 'module:paypal/views/templates/shortcut/shortcut-signup.tpl';
    }

    protected function getStyleSetting()
    {
        if (Configuration::get(ShortcutConfiguration::CUSTOMIZE_STYLE)) {
            $styleSetting = [
                'label' => Configuration::get(ShortcutConfiguration::STYLE_LABEL_SIGNUP, null, null, null, ShortcutConfiguration::STYLE_LABEL_CHECKOUT),
                'color' => Configuration::get(ShortcutConfiguration::STYLE_COLOR_SIGNUP, null, null, null, ShortcutConfiguration::STYLE_COLOR_GOLD),
                'shape' => Configuration::get(ShortcutConfiguration::STYLE_SHAPE_SIGNUP, null, null, null, ShortcutConfiguration::STYLE_SHAPE_RECT),
                'height' => (int) Configuration::get(ShortcutConfiguration::STYLE_HEIGHT_SIGNUP, null, null, null, 35),
                'width' => (int) Configuration::get(ShortcutConfiguration::STYLE_WIDTH_SIGNUP, null, null, null, 200),
            ];
        } else {
            $styleSetting = [
                'label' => ShortcutConfiguration::STYLE_LABEL_CHECKOUT,
                'color' => ShortcutConfiguration::STYLE_COLOR_GOLD,
                'shape' => ShortcutConfiguration::STYLE_SHAPE_RECT,
                'height' => 35,
                'width' => 200,
            ];
        }

        return $styleSetting;
    }

    /**
     * @return bool
     */
    public function isWidget()
    {
        return $this->isWidget;
    }

    /**
     * @param bool $isWidget
     *
     * @return ShortcutSignup
     */
    public function setIsWidget($isWidget)
    {
        $this->isWidget = $isWidget;

        return $this;
    }
}
