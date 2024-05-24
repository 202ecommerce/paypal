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

namespace PaypalPPBTlib\Extensions\Diagnostic\Stubs\Model\Database;

class TableInfo
{
    /**
     * @var string
     */
    protected $name;
    /**
     * @var array<FieldInfo>
     */
    protected $fields = [];

    /**
     * @var array
     */
    protected $errors = [];

    /**
     * @return FieldInfo[]
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @param FieldInfo[] $fields
     * @return TableInfo
     */
    public function setFields($fields)
    {
        $this->fields = $fields;
        return $this;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @param string $error
     * @return TableInfo
     */
    public function addError($error)
    {
        $this->errors[] = $error;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return TableInfo
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function toArray()
    {
        return [
            'name' => $this->getName(),
            'errors' => $this->getErrors(),
            'fields' => array_map(function (FieldInfo $fieldInfo) {
                return $fieldInfo->toArray();
            }, $this->getFields())
        ];
    }
}
