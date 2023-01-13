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

namespace PaypalAddons\classes\Webhook;

use PaypalAddons\classes\AbstractMethodPaypal;
use PaypalAddons\classes\API\Request\V_1\ValidateWebhookEventRequest;

class RequestValidator
{
    public function validateIPN($data)
    {
        $curl = curl_init($this->getIpnPaypalListener() . '?cmd=_notify-validate&' . http_build_query($data));
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_TIMEOUT, 90);
        $response = curl_exec($curl);

        return trim($response) == 'VERIFIED';
    }

    /**
     * @return string
     */
    protected function getIpnPaypalListener()
    {
        return (new IpnPaypalListener())->get();
    }

    /**
     * @param array $headers
     * @param string $request
     *
     * @return bool
     */
    public function isValidWebhookEvent($headers, $request, $attemptCount = 1)
    {
        $attemptCountDone = 0;
        $validateRequest = new ValidateWebhookEventRequest(
            AbstractMethodPaypal::load(),
            $headers,
            $request
        );

        while ($attemptCount > $attemptCountDone) {
            ++$attemptCountDone;
            $isValid = $validateRequest->execute()->isSuccess();

            if ($isValid) {
                return true;
            }
        }

        return false;
    }
}
