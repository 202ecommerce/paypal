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

class SelectOption implements FieldInteface
{
    /** @var string */
    protected $description;

    /** @var string */
    protected $value;

    /** @var bool */
    protected $isSelected;

    public function __construct($value, $description)
    {
        $this->setDescription($description);
        $this->setValue($value);
    }

    public function render()
    {
        return Context::getContext()->smarty
            ->assign('value', $this->getValue())
            ->assign('description', $this->getDescription())
            ->assign('isSelected', $this->isSelected())
            ->fetch(_PS_MODULE_DIR_ . 'paypal/views/templates/admin/_partials/form/fields/selectOption.tpl');
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return (string) $this->description;
    }

    /**
     * @param string $description
     *
     * @return SelectOption
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return (string) $this->value;
    }

    /**
     * @param string $value
     *
     * @return SelectOption
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return bool
     */
    public function isSelected()
    {
        return (bool) $this->isSelected;
    }

    /**
     * @param bool $isSelected
     *
     * @return SelectOption
     */
    public function setIsSelected($isSelected)
    {
        $this->isSelected = (bool) $isSelected;

        return $this;
    }
}
