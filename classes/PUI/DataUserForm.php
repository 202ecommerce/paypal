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

namespace PaypalAddons\classes\PUI;

use PaypalAddons\services\FormatterPaypal;

if (!defined('_PS_VERSION_')) {
    exit;
}

class DataUserForm
{
    /** @var string */
    protected $firstName;

    /** @var string */
    protected $lastName;

    /** @var string */
    protected $phone;

    /** @var string */
    protected $email;

    /** @var string */
    protected $birth;
    /**
     * @var FormatterPaypal
     */
    protected $formatter;

    public function __construct()
    {
        $this->formatter = new FormatterPaypal();
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return (string) $this->firstName;
    }

    /**
     * @param string $firstName
     *
     * @return self
     */
    public function setFirstName($firstName)
    {
        $this->firstName = (string) $firstName;

        return $this;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return (string) $this->lastName;
    }

    /**
     * @param string $lastName
     *
     * @return self
     */
    public function setLastName($lastName)
    {
        $this->lastName = (string) $lastName;

        return $this;
    }

    /**
     * @return string
     */
    public function getPhone()
    {
        return (string) $this->phone;
    }

    /**
     * @param string $phone
     *
     * @return self
     */
    public function setPhone($phone)
    {
        $this->phone = $this->formatter->formatPhoneNumber((string) $phone);

        return $this;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return (string) $this->email;
    }

    /**
     * @param string $email
     *
     * @return self
     */
    public function setEmail($email)
    {
        $this->email = (string) $email;

        return $this;
    }

    /**
     * @return string
     */
    public function getBirth($format = \PayPal::PS_CUSTOMER_DATE_FORMAT)
    {
        $date = \DateTime::createFromFormat(\PayPal::PS_CUSTOMER_DATE_FORMAT, (string) $this->birth);

        if (!$date) {
            return '';
        }

        return $date->format($format);
    }

    /**
     * @param string $birth
     *
     * @return self
     */
    public function setBirth($birth)
    {
        $this->birth = (string) $birth;

        return $this;
    }
}
