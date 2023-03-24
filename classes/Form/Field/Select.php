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

namespace PaypalAddons\classes\Form\Field;

use Context;

class Select implements FieldInteface
{
    /** @var string */
    protected $name;

    /** @var array */
    protected $options;

    /** @var string */
    protected $label;

    /** @var mixed */
    protected $value;

    /** @var string */
    protected $type;

    /** @var string */
    protected $afterSelectContent;

    /** @var string */
    protected $css;

    /** @var bool */
    protected $hintSwitch;

    public function __construct($name, $options, $label = null, $value = null, $type = null, $hintSwitch = false, $css = null)
    {
        $this->setName($name);
        $this->setOptions($options);
        $this->setLabel($label);
        $this->setValue($value);
        $this->setType($type);
        $this->setHintSwitch($hintSwitch);
        $this->setCss($css);
    }

    public function render()
    {
        if (false === empty($this->options)) {
            foreach ($this->options as $key => $option) {
                if ($this->getValue() == $option->getValue()) {
                    $option->setIsSelected(true);
                }
            }
        }

        return Context::getContext()->smarty
            ->assign('name', $this->getName())
            ->assign('options', $this->getOptions())
            ->assign('label', $this->getLabel())
            ->assign('configType', $this->getType())
            ->assign('afterSelectContent', $this->getAfterSelectContent())
            ->assign('css', $this->getCss())
            ->assign('hintSwitch', $this->isHintSwitch())
            ->fetch(_PS_MODULE_DIR_ . 'paypal/views/templates/admin/_partials/form/fields/select.tpl');
    }

    /**
     * @return string
     */
    public function getName()
    {
        return (string) $this->name;
    }

    /**
     * @param string $name
     *
     * @return Select
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return SelectOption[]
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param array $options
     *
     * @return Select
     */
    public function setOptions($options)
    {
        $this->options = [];

        if (empty($options)) {
            return $this;
        }

        foreach ($options as $option) {
            $this->addOption($option);
        }

        return $this;
    }

    /**
     * @param SelectOption $option
     *
     * @return Select
     */
    public function addOption(SelectOption $option)
    {
        $this->options[] = $option;

        return $this;
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
     * @return Select
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     *
     * @return Select
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return (string) $this->type;
    }

    /**
     * @param string $type
     *
     * @return Select
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getAfterSelectContent()
    {
        return (string) $this->afterSelectContent;
    }

    /**
     * @param string $afterSelectContent
     *
     * @return Select
     */
    public function setAfterSelectContent($afterSelectContent)
    {
        $this->afterSelectContent = (string) $afterSelectContent;

        return $this;
    }

    /**
     * @return string
     */
    public function getCss()
    {
        return (string) $this->css;
    }

    /**
     * @param string $css
     *
     * @return Select
     */
    public function setCss($css)
    {
        $this->css = $css;

        return $this;
    }

    /**
     * @return bool
     */
    public function isHintSwitch()
    {
        return (bool) $this->hintSwitch;
    }

    /**
     * @param bool $hintSwitch
     *
     * @return Select
     */
    public function setHintSwitch($hintSwitch)
    {
        $this->hintSwitch = $hintSwitch;

        return $this;
    }
}
