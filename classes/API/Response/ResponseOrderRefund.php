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

class ResponseOrderRefund extends Response
{
    /** @var string */
    protected $idTransaction;

    /** @var string */
    protected $status;

    /** @var float */
    protected $amount;

    /** @var string */
    protected $dateTransaction;

    /** @var bool */
    protected $alreadyRefunded;

    /**
     * @return string
     */
    public function getIdTransaction()
    {
        return $this->idTransaction;
    }

    /**
     * @param string $idTransaction
     */
    public function setIdTransaction($idTransaction)
    {
        $this->idTransaction = $idTransaction;

        return $this;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param float $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * @return string
     */
    public function getDateTransaction()
    {
        return $this->dateTransaction;
    }

    /**
     * @param string $dateTransaction
     */
    public function setDateTransaction($dateTransaction)
    {
        $this->dateTransaction = $dateTransaction;

        return $this;
    }

    public function getMessage()
    {
        $message = '';
        $message .= 'Refund Transaction Id: ' . $this->getIdTransaction() . '; ';
        $message .= 'Total amount: ' . $this->getAmount() . '; ';
        $message .= 'Status: ' . $this->getStatus() . '; ';
        $message .= 'Transaction date: ' . $this->getDateTransaction() . '; ';

        return $message;
    }

    /**
     * @return bool
     */
    public function isAlreadyRefunded()
    {
        return (bool) $this->alreadyRefunded;
    }

    /**
     * @param bool $alreadyRefunded
     *
     * @return ResponseOrderRefund
     */
    public function setAlreadyRefunded($alreadyRefunded)
    {
        $this->alreadyRefunded = (bool) $alreadyRefunded;

        return $this;
    }
}
