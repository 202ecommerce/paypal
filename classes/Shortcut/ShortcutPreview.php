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

class ShortcutPreview extends ShortcutAbstract
{
    /** @var string */
    protected $label = null;

    /** @var int */
    protected $height = null;

    /** @var int */
    protected $width = null;

    /** @var string */
    protected $color = null;

    /** @var string */
    protected $shape = null;

    public function __construct(
        $label,
        $height,
        $width,
        $color,
        $shape
    ) {
        parent::__construct();

        $this->label = $label;
        $this->height = $height;
        $this->width = $width;
        $this->color = $color;
        $this->shape = $shape;
    }

    /**
     * @return []
     */
    protected function getTplVars()
    {
        return [
            'shortcutID' => $this->getId(),
            'styleSetting' => $this->getStyleSetting(),
        ];
    }

    protected function getTemplatePath()
    {
        return 'extends:' . _PS_MODULE_DIR_ . 'paypal/views/templates/shortcut/shortcut-layout.tpl|' . _PS_MODULE_DIR_ . 'paypal/views/templates/shortcut/shortcut-preview.tpl';
    }

    protected function getJS()
    {
        $jsScripts = [];
        $jsScripts['tot-paypal-sdk'] = [
            'src' => $this->method->getUrlJsSdkLib(),
            'data-namespace' => 'totPaypalSdkButtons',
        ];

        return $jsScripts;
    }

    protected function getStyleSetting()
    {
        return [
            'label' => $this->getLabel(),
            'height' => $this->getHeight(),
            'width' => $this->getWidth(),
            'color' => $this->getColor(),
            'shape' => $this->getShape(),
        ];
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return (string) $this->label;
    }

    /**
     * @param string $label
     *
     * @return ShortcutPreview
     */
    public function setLabel($label)
    {
        $this->label = (string) $label;

        return $this;
    }

    /**
     * @return int
     */
    public function getHeight()
    {
        return (int) $this->height;
    }

    /**
     * @param int $height
     *
     * @return ShortcutPreview
     */
    public function setHeight($height)
    {
        $this->height = (int) $height;

        return $this;
    }

    /**
     * @return int
     */
    public function getWidth()
    {
        return (int) $this->width;
    }

    /**
     * @param int $width
     *
     * @return ShortcutPreview
     */
    public function setWidth($width)
    {
        $this->width = $width;

        return $this;
    }

    /**
     * @return string
     */
    public function getColor()
    {
        return (string) $this->color;
    }

    /**
     * @param string $color
     *
     * @return ShortcutPreview
     */
    public function setColor($color)
    {
        $this->color = $color;

        return $this;
    }

    /**
     * @return string
     */
    public function getShape()
    {
        return (string) $this->shape;
    }

    /**
     * @param string $shape
     *
     * @return ShortcutPreview
     */
    public function setShape($shape)
    {
        $this->shape = $shape;

        return $this;
    }
}
