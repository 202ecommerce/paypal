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

namespace PaypalAddons\classes\API\Response;

class ResponseOrderGet extends Response
{
    /** @var Client */
    protected $client;

    /** @var Address */
    protected $address;

    protected $purchaseUnit;

    protected $depositBankDetails;

    /** @var string */
    protected $status;

    public function __construct()
    {
        $this->setClient(new Client());
        $this->setAddress(new Address());
        $this->setPurchaseUnit(new PurchaseUnit());
        $this->setDepositBankDetails(new DepositBankDetails());
    }

    /**
     * @return mixed
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param mixed $client
     */
    public function setClient($client)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param mixed $address
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    public function setPurchaseUnit(PurchaseUnit $purchaseUnit)
    {
        $this->purchaseUnit = $purchaseUnit;
    }

    public function getPurchaseUnit()
    {
        return $this->purchaseUnit;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return (string) $this->status;
    }

    /**
     * @param string $status
     *
     * @return ResponseOrderGet
     */
    public function setStatus($status)
    {
        $this->status = (string) $status;

        return $this;
    }

    public function setDepositBankDetails(DepositBankDetails $bankDetails)
    {
        $this->depositBankDetails = $bankDetails;

        return $this;
    }

    public function getDepositBankDetails()
    {
        return $this->depositBankDetails;
    }
}
