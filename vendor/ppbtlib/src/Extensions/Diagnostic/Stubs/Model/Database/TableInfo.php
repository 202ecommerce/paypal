<?php
/*
 * NOTICE OF LICENSE
 *
 * This source file is subject to a commercial license from SARL 202 ecommerce
 * Use, copy, modification or distribution of this source file without written
 * license agreement from the SARL 202 ecommerce is strictly forbidden.
 * In order to obtain a license, please contact us: tech@202-ecommerce.com
 * ...........................................................................
 * INFORMATION SUR LA LICENCE D'UTILISATION
 *
 * L'utilisation de ce fichier source est soumise a une licence commerciale
 * concedee par la societe 202 ecommerce
 * Toute utilisation, reproduction, modification ou distribution du present
 * fichier source sans contrat de licence ecrit de la part de la SARL 202 ecommerce est
 * expressement interdite.
 * Pour obtenir une licence, veuillez contacter 202-ecommerce <tech@202-ecommerce.com>
 * ...........................................................................
 *
 * @author    202-ecommerce <tech@202-ecommerce.com>
 * @copyright Copyright (c) 202-ecommerce
 * @license   Commercial license
 * @version   develop
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
