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

namespace PaypalAddons\classes\API\Request;

use Exception;
use PaypalAddons\classes\AbstractMethodPaypal;
use PaypalAddons\classes\API\ExtensionSDK\PartnerReferrals;
use PaypalAddons\classes\API\Response\Error;
use PaypalAddons\classes\API\Response\ResponsePartnerReferrals;
use PaypalAddons\services\Builder\PartnerReferralsRequestBody;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalHttp\HttpException;
use Throwable;

class PaypalPartnerReferralsRequest extends RequestAbstract
{
    protected $bodyBuilder;

    public function __construct(PayPalHttpClient $client, AbstractMethodPaypal $method)
    {
        parent::__construct($client, $method);

        $this->bodyBuilder = new PartnerReferralsRequestBody($method);
    }

    public function execute()
    {
        $response = $this->getResponse();
        $partnerReferral = new PartnerReferrals();
        $partnerReferral->headers = array_merge($partnerReferral->headers, $this->getHeaders());
        $partnerReferral->body = $this->buildRequestBody();

        try {
            $exec = $this->client->execute($partnerReferral);
            $response->setActionLink($this->getActionLink($exec));
            $response->setSelfLink($this->getSelfLink($exec));
        } catch (HttpException $e) {
            $error = new Error();
            $resultDecoded = json_decode($e->getMessage());
            $error->setMessage($resultDecoded->details[0]->description)->setErrorCode($e->getCode());

            return $response->setSuccess(false)
                ->setError($error);
        } catch (Throwable $e) {
            $error = new Error();
            $error->setMessage($e->getMessage())
                ->setErrorCode($e->getCode());

            return $response->setSuccess(false)->setError($error);
        } catch (Exception $e) {
            $error = new Error();
            $error->setMessage($e->getMessage())
                ->setErrorCode($e->getCode());

            return $response->setSuccess(false)->setError($error);
        }

        $response->setSuccess(true);
        $response->setData($exec);

        return $response;
    }

    public function buildRequestBody()
    {
        return $this->bodyBuilder->build();
    }

    /** @return ResponsePartnerReferrals*/
    protected function getResponse()
    {
        return new ResponsePartnerReferrals();
    }

    protected function getActionLink(\PayPalHttp\HttpResponse $exec)
    {
        return $this->getLink('action_url', $exec);
    }

    protected function getSelfLink(\PayPalHttp\HttpResponse $exec)
    {
        return $this->getLink('self', $exec);
    }

    protected function getLink($type, \PayPalHttp\HttpResponse $exec)
    {
        $link = '';

        if (empty($exec->result->links)) {
            return $link;
        }

        foreach ($exec->result->links as $link) {
            if (empty($link->rel)) {
                continue;
            }

            if ($link->rel == $type) {
                return empty($link->href) ? '' : $link->href;
            }
        }

        return $link;
    }
}
