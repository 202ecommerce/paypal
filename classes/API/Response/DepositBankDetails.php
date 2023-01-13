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

class DepositBankDetails
{
    /** @var string */
    protected $bic;

    /** @var string */
    protected $bankName;

    /** @var string */
    protected $iban;

    /** @var string */
    protected $accountHolderName;

    /**
     * @return string
     */
    public function getBic()
    {
        return (string) $this->bic;
    }

    /**
     * @param string $bic
     *
     * @return DepositBankDetails
     */
    public function setBic($bic)
    {
        $this->bic = $bic;

        return $this;
    }

    /**
     * @return string
     */
    public function getBankName()
    {
        return (string) $this->bankName;
    }

    /**
     * @param string $bankName
     *
     * @return DepositBankDetails
     */
    public function setBankName($bankName)
    {
        $this->bankName = $bankName;

        return $this;
    }

    /**
     * @return string
     */
    public function getIban()
    {
        return (string) $this->iban;
    }

    /**
     * @param string $iban
     *
     * @return DepositBankDetails
     */
    public function setIban($iban)
    {
        $this->iban = $iban;

        return $this;
    }

    /**
     * @return string
     */
    public function getAccountHolderName()
    {
        return (string) $this->accountHolderName;
    }

    /**
     * @param string $accountHolderName
     *
     * @return DepositBankDetails
     */
    public function setAccountHolderName($accountHolderName)
    {
        $this->accountHolderName = $accountHolderName;

        return $this;
    }
}
