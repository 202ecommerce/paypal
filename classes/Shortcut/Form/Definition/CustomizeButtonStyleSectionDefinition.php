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

namespace PaypalAddons\classes\Shortcut\Form\Definition;

class CustomizeButtonStyleSectionDefinition
{
    /** @var string */
    protected $nameColor;

    /** @var int */
    protected $typeColor;

    /** @var string */
    protected $nameShape;

    /** @var int */
    protected $typeShape;

    /** @var string */
    protected $nameLabel;

    /** @var int */
    protected $typeLabel;

    /** @var string */
    protected $nameWidth;

    /** @var int */
    protected $typeWidth;

    /** @var string */
    protected $nameHeight;

    /** @var int */
    protected $typeHeight;

    /** @var array */
    protected $errors;

    /**
     * @return string
     */
    public function getNameColor()
    {
        return (string) $this->nameColor;
    }

    /**
     * @param string $nameColor
     *
     * @return CustomizeButtonStyleSectionDefinition
     */
    public function setNameColor($nameColor)
    {
        $this->nameColor = $nameColor;

        return $this;
    }

    /**
     * @return int
     */
    public function getTypeColor()
    {
        return (int) $this->typeColor;
    }

    /**
     * @param int $typeColor
     *
     * @return CustomizeButtonStyleSectionDefinition
     */
    public function setTypeColor($typeColor)
    {
        $this->typeColor = $typeColor;

        return $this;
    }

    /**
     * @return string
     */
    public function getNameShape()
    {
        return (string) $this->nameShape;
    }

    /**
     * @param string $nameShape
     *
     * @return CustomizeButtonStyleSectionDefinition
     */
    public function setNameShape($nameShape)
    {
        $this->nameShape = $nameShape;

        return $this;
    }

    /**
     * @return int
     */
    public function getTypeShape()
    {
        return (int) $this->typeShape;
    }

    /**
     * @param int $typeShape
     *
     * @return CustomizeButtonStyleSectionDefinition
     */
    public function setTypeShape($typeShape)
    {
        $this->typeShape = $typeShape;

        return $this;
    }

    /**
     * @return string
     */
    public function getNameLabel()
    {
        return (string) $this->nameLabel;
    }

    /**
     * @param string $nameLabel
     *
     * @return CustomizeButtonStyleSectionDefinition
     */
    public function setNameLabel($nameLabel)
    {
        $this->nameLabel = $nameLabel;

        return $this;
    }

    /**
     * @return int
     */
    public function getTypeLabel()
    {
        return (int) $this->typeLabel;
    }

    /**
     * @param int $typeLabel
     *
     * @return CustomizeButtonStyleSectionDefinition
     */
    public function setTypeLabel($typeLabel)
    {
        $this->typeLabel = $typeLabel;

        return $this;
    }

    /**
     * @return string
     */
    public function getNameWidth()
    {
        return (string) $this->nameWidth;
    }

    /**
     * @param string $nameWidth
     *
     * @return CustomizeButtonStyleSectionDefinition
     */
    public function setNameWidth($nameWidth)
    {
        $this->nameWidth = $nameWidth;

        return $this;
    }

    /**
     * @return int
     */
    public function getTypeWidth()
    {
        return (int) $this->typeWidth;
    }

    /**
     * @param int $typeWidth
     *
     * @return CustomizeButtonStyleSectionDefinition
     */
    public function setTypeWidth($typeWidth)
    {
        $this->typeWidth = $typeWidth;

        return $this;
    }

    /**
     * @return string
     */
    public function getNameHeight()
    {
        return (string) $this->nameHeight;
    }

    /**
     * @param string $nameHeight
     *
     * @return CustomizeButtonStyleSectionDefinition
     */
    public function setNameHeight($nameHeight)
    {
        $this->nameHeight = $nameHeight;

        return $this;
    }

    /**
     * @return int
     */
    public function getTypeHeight()
    {
        return (int) $this->typeHeight;
    }

    /**
     * @param int $typeHeight
     *
     * @return CustomizeButtonStyleSectionDefinition
     */
    public function setTypeHeight($typeHeight)
    {
        $this->typeHeight = $typeHeight;

        return $this;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        if (false === is_array($this->errors)) {
            return [];
        }

        return $this->errors;
    }

    /**
     * @param array $errors
     *
     * @return CustomizeButtonStyleSectionDefinition
     */
    public function setErrors($errors)
    {
        if (false === is_array($errors) || empty($errors)) {
            $this->errors = [];

            return $this;
        }

        foreach ($errors as $error) {
            $this->addError($error);
        }

        return $this;
    }

    /**
     * @param string $error
     *
     * @return CustomizeButtonStyleSectionDefinition
     */
    public function addError($error)
    {
        if (false === is_string($error)) {
            return $this;
        }

        $this->errors[] = $error;

        return $this;
    }
}
