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

namespace PaypalAddons\classes\API\Model;

if (!defined('_PS_VERSION_')) {
    exit;
}

class PayPalModel
{
    protected $_propMap = [];

    /**
     * Default Constructor
     *
     * You can pass data as a json representation or array object. This argument eliminates the need
     * to do $obj->fromJson($data) later after creating the object.
     *
     * @param array|string|null $data
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($data = null)
    {
        switch (gettype($data)) {
            case 'NULL':
                break;
            case 'string':
                if ($this->isJsonValid($data)) {
                    $this->fromJson($data);
                }
                break;
            case 'array':
                $this->fromArray($data);
                break;
            default:
        }
    }

    /**
     * Magic Get Method
     *
     * @param string $key
     *
     * @return mixed
     */
    public function __get($key)
    {
        if ($this->__isset($key)) {
            return $this->_propMap[$key];
        }

        return null;
    }

    /**
     * Magic Set Method
     *
     * @param string $key
     * @param mixed $value
     */
    public function __set($key, $value)
    {
        if (!is_array($value) && $value === null) {
            $this->__unset($key);
        } else {
            $this->_propMap[$key] = $value;
        }
    }

    /**
     * Converts the input key into a valid Setter Method Name
     *
     * @param string $key
     *
     * @return mixed
     */
    private function convertToCamelCase($key)
    {
        return str_replace(' ', '', ucwords(str_replace(['_', '-'], ' ', $key)));
    }

    /**
     * Magic isSet Method
     *
     * @param string $key
     *
     * @return bool
     */
    public function __isset($key)
    {
        return isset($this->_propMap[$key]);
    }

    /**
     * Magic Unset Method
     *
     * @param string $key
     */
    public function __unset($key)
    {
        unset($this->_propMap[$key]);
    }

    /**
     * Converts Params to Array
     *
     * @param mixed $param
     *
     * @return array
     */
    private function _convertToArray($param)
    {
        $ret = [];
        foreach ($param as $k => $v) {
            if ($v instanceof PayPalModel) {
                $ret[$k] = $v->toArray();
            } elseif (is_array($v) && sizeof($v) <= 0) {
                $ret[$k] = [];
            } elseif (is_array($v)) {
                $ret[$k] = $this->_convertToArray($v);
            } else {
                $ret[$k] = $v;
            }
        }
        // If the array is empty, which means an empty object,
        // we need to convert array to StdClass object to properly
        // represent JSON String
        if (sizeof($ret) <= 0) {
            $ret = new PayPalModel();
        }

        return $ret;
    }

    /**
     * Fills object value from Array list
     *
     * @param mixed $arr
     *
     * @return $this
     */
    public function fromArray($arr)
    {
        if (is_array($arr)) {
            // Iterate over each element in array
            foreach ($arr as $k => $v) {
                // If the value is an array, it means, it is an object after conversion
                if (is_array($v)) {
                    // Determine the class of the object
                    if (($clazz = ReflectionUtil::getPropertyClass(get_class($this), $k)) != null) {
                        // If the value is an associative array, it means, its an object. Just make recursive call to it.
                        if (empty($v)) {
                            if (ReflectionUtil::isPropertyClassArray(get_class($this), $k)) {
                                // It means, it is an array of objects.
                                $this->assignValue($k, []);
                                continue;
                            }
                            $o = new $clazz();
                            // $arr = array();
                            $this->assignValue($k, $o);
                        } elseif ($this->isAssocArray($v)) {
                            /** @var self $o */
                            $o = new $clazz();
                            $o->fromArray($v);
                            $this->assignValue($k, $o);
                        } else {
                            // Else, value is an array of object/data
                            $arr = [];
                            // Iterate through each element in that array.
                            foreach ($v as $nk => $nv) {
                                if (is_array($nv)) {
                                    $o = new $clazz();
                                    $o->fromArray($nv);
                                    $arr[$nk] = $o;
                                } else {
                                    $arr[$nk] = $nv;
                                }
                            }
                            $this->assignValue($k, $arr);
                        }
                    } else {
                        $this->assignValue($k, $v);
                    }
                } else {
                    $this->assignValue($k, $v);
                }
            }
        }

        return $this;
    }

    private function assignValue($key, $value)
    {
        $setter = 'set' . $this->convertToCamelCase($key);
        // If we find the setter, use that, otherwise use magic method.
        if (method_exists($this, $setter)) {
            $this->$setter($value);
        } else {
            $this->__set($key, $value);
        }
    }

    /**
     * Fills object value from Json string
     *
     * @param string $json
     *
     * @return $this
     */
    public function fromJson($json)
    {
        return $this->fromArray(json_decode($json, true));
    }

    /**
     * Returns array representation of object
     *
     * @return array
     */
    public function toArray()
    {
        return $this->_convertToArray($this->_propMap);
    }

    /**
     * Returns object JSON representation
     *
     * @param int $options http://php.net/manual/en/json.constants.php
     *
     * @return string
     */
    public function toJSON($options = 0)
    {
        // Because of PHP Version 5.3, we cannot use JSON_UNESCAPED_SLASHES option
        // Instead we would use the str_replace command for now.
        // TODO: Replace this code with return json_encode($this->toArray(), $options | 64); once we support PHP >= 5.4
        if (version_compare(phpversion(), '5.4.0', '>=') === true) {
            return json_encode($this->toArray(), $options | 64);
        }

        return str_replace('\\/', '/', json_encode($this->toArray(), $options));
    }

    /**
     * Magic Method for toString
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toJSON(128);
    }

    protected function isJsonValid($string, $silent = true)
    {
        @json_decode($string);
        if (json_last_error() != JSON_ERROR_NONE) {
            if ($string === '' || $string === null) {
                return true;
            }
            if (!$silent) {
                // Throw an Exception for string or array
                throw new \InvalidArgumentException('Invalid JSON String');
            }

            return false;
        }

        return true;
    }

    protected function isAssocArray(array $arr)
    {
        foreach ($arr as $k => $v) {
            if (is_int($k)) {
                return false;
            }
        }

        return true;
    }
}
