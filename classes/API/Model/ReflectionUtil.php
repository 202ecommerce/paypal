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

class ReflectionUtil
{
    /**
     * Reflection Methods
     *
     * @var \ReflectionMethod[]
     */
    private static $propertiesRefl = [];

    /**
     * Properties Type
     *
     * @var array
     */
    private static $propertiesType = [];

    /**
     * Gets Property Class of the given property.
     * If the class is null, it returns null.
     * If the property is not found, it returns null.
     *
     * @param string $class
     * @param string $propertyName
     *
     * @return string|null
     *
     * @throws \Exception
     */
    public static function getPropertyClass($class, $propertyName)
    {
        if ($class == get_class(new PayPalModel())) {
            // Make it generic if PayPalModel is used for generating this
            return get_class(new PayPalModel());
        }

        // If the class doesn't exist, or the method doesn't exist, return null.
        if (!class_exists($class) || !method_exists($class, self::getter($class, $propertyName))) {
            return null;
        }

        if (($annotations = self::propertyAnnotations($class, $propertyName)) && isset($annotations['return'])) {
            $param = $annotations['return'];
        }

        if (isset($param)) {
            $anno = preg_split("/[\s\[\]]+/", $param);

            return $anno[0];
        } else {
            throw new \Exception("Getter function for '$propertyName' in '$class' class should have a proper return type.");
        }
    }

    /**
     * Checks if the Property is of type array or an object
     *
     * @param string $class
     * @param string $propertyName
     *
     * @return bool|null
     *
     * @throws \Exception
     */
    public static function isPropertyClassArray($class, $propertyName)
    {
        // If the class doesn't exist, or the method doesn't exist, return null.
        if (!class_exists($class) || !method_exists($class, self::getter($class, $propertyName))) {
            return null;
        }

        if (($annotations = self::propertyAnnotations($class, $propertyName)) && isset($annotations['return'])) {
            $param = $annotations['return'];
        }

        if (isset($param)) {
            return substr($param, -strlen('[]')) === '[]';
        } else {
            throw new \Exception("Getter function for '$propertyName' in '$class' class should have a proper return type.");
        }
    }

    /**
     * Retrieves Annotations of each property
     *
     * @param string $class
     * @param string $propertyName
     *
     * @return mixed
     *
     * @throws \RuntimeException
     */
    public static function propertyAnnotations($class, $propertyName)
    {
        $class = is_object($class) ? get_class($class) : $class;
        if (!class_exists('ReflectionProperty')) {
            throw new \RuntimeException('Property type of ' . $class . "::{$propertyName} cannot be resolved");
        }

        if ($annotations = &self::$propertiesType[$class][$propertyName]) {
            return $annotations;
        }

        if (!($refl = &self::$propertiesRefl[$class][$propertyName])) {
            $getter = self::getter($class, $propertyName);
            $refl = new \ReflectionMethod($class, $getter);
            self::$propertiesRefl[$class][$propertyName] = $refl;
        }

        // todo: smarter regexp
        if (!preg_match_all(
            '~\@([^\s@\(]+)[\t ]*(?:\(?([^\n@]+)\)?)?~i',
            $refl->getDocComment(),
            $annots,
            PREG_PATTERN_ORDER)) {
            return null;
        }
        foreach ($annots[1] as $i => $annot) {
            $annotations[strtolower($annot)] = empty($annots[2][$i]) ? true : rtrim($annots[2][$i], " \t\n\r)");
        }

        return $annotations;
    }

    /**
     * Returns the properly formatted getter function name based on class name and property
     * Formats the property name to a standard getter function
     *
     * @param string $class
     * @param string $propertyName
     *
     * @return string getter function name
     */
    public static function getter($class, $propertyName)
    {
        return method_exists($class, 'get' . ucfirst($propertyName)) ?
            'get' . ucfirst($propertyName) :
            'get' . preg_replace_callback("/([_\-\s]?([a-z0-9]+))/", function ($match) {return ucwords($match[2]); }, $propertyName);
    }
}
