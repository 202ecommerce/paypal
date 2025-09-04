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

namespace PaypalAddons\classes\API\Request;

use PaypalAddons\classes\AbstractMethodPaypal;
use PaypalAddons\classes\API\Client\HttpClient;
use PaypalAddons\classes\API\ExtensionSDK\VaultPaymentTokens;
use PaypalAddons\classes\API\HttpAdoptedResponse;
use PaypalAddons\classes\API\Model\VaultInfo;
use PaypalAddons\classes\API\Response\Error;
use PaypalAddons\classes\API\Response\ResponseVaultPaymentToken;
use PaypalAddons\classes\Constants\Vaulting;
use PaypalAddons\classes\PaypalException;

if (!defined('_PS_VERSION_')) {
    exit;
}

class PaypalGenerateVaultPaymentTokenRequest extends RequestAbstract
{
    /** @var string */
    protected $tokenId;

    public function __construct(HttpClient $client, AbstractMethodPaypal $method, $tokenId)
    {
        parent::__construct($client, $method);

        $this->tokenId = (string) $tokenId;
    }

    public function execute()
    {
        $response = new ResponseVaultPaymentToken();
        $request = new VaultPaymentTokens($this->tokenId);

        try {
            $exec = $this->client->execute($request);

            if ($exec instanceof HttpAdoptedResponse) {
                $exec = $exec->getAdoptedResponse();
            }

            $response->setData($exec);

            if ($exec->statusCode >= 200 && $exec->statusCode < 300) {
                $response->setSuccess(true);
                $vaultInfo = $this->getVaultInfo($exec);

                if ($vaultInfo instanceof VaultInfo) {
                    $response->setVaultInfo($vaultInfo);
                }
            } else {
                $response->setSuccess(false)->setData($exec);
            }
        } catch (PaypalException $e) {
            $error = new Error();
            $resultDecoded = json_decode($e->getMessage(), true);

            if (empty($resultDecoded['details'][0]['description'])) {
                $error->setMessage($e->getMessage());
            } else {
                $error->setMessage($resultDecoded['details'][0]['description']);
            }

            $error->setErrorCode($e->getCode());
            $response->setSuccess(false)->setError($error);
        } catch (\Throwable $e) {
            $error = new Error();
            $error->setMessage($e->getMessage())
                ->setErrorCode($e->getCode());
            $response->setSuccess(false)
                ->setError($error);
        }

        return $response;
    }

    /**
     * @return VaultInfo
     */
    protected function getVaultInfo($response)
    {
        $vaultInfo = new VaultInfo();
        $vaultInfo->setPaymentSource(Vaulting::PAYMENT_SOURCE_PAYPAL);

        if (false === empty($response->result->id)) {
            $vaultInfo->setVaultId((string) $response->result->id);
        }
        if (false === empty($response->result->customer->id)) {
            $vaultInfo->setCustomerId((string) $response->result->customer->id);
        }

        return $vaultInfo;
    }
}
