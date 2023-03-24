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

class InputChain implements FieldInteface
{
    /** @var TextInput[] */
    protected $inputs;

    /** @var string */
    protected $label;

    public function __construct($inputs)
    {
        $this->setInputs($inputs);
    }

    public function render()
    {
        return Context::getContext()->smarty
            ->assign('inputs', $this->getInputs())
            ->assign('label', $this->getLabel())
            ->fetch(_PS_MODULE_DIR_ . 'paypal/views/templates/admin/_partials/form/fields/inputChain.tpl');
    }

    /**
     * @param TextInput[] $inputs
     *
     * @return InputChain
     */
    public function setInputs($inputs)
    {
        $this->inputs = [];

        if (empty($inputs)) {
            return $this;
        }

        foreach ($inputs as $input) {
            $this->addInput($input);
        }

        return $this;
    }

    /**
     * @param TextInput $input
     *
     * @return InputChain
     */
    public function addInput(TextInput $input)
    {
        $this->inputs[] = $input;

        return $this;
    }

    /**
     * @return TextInput[]
     */
    public function getInputs()
    {
        return $this->inputs;
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
     * @return InputChain
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }
}
