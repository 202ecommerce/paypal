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

class Webhook extends PayPalModel
{
    /**
     * The ID of the webhook.
     *
     * @param string $id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * The ID of the webhook.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * The URL that is configured to listen on `localhost` for incoming `POST` notification messages that contain event information.
     *
     * @param string $url
     *
     * @return $this
     */
    public function setUrl($url)
    {
        if ($this->isUrlValid($url)) {
            $this->url = $url;
        }

        return $this;
    }

    protected function isUrlValid($url)
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * The URL that is configured to listen on `localhost` for incoming `POST` notification messages that contain event information.
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * A list of up to ten events to which to subscribe your webhook. To subscribe to all events including new events as they are added, specify the asterisk (`*`) wildcard. To replace the `event_types` array, specify the `*` wildcard. To see all supported events, [list available events](#available-event-type.list).
     *
     * @param \PaypalAddons\classes\API\Model\WebhookEventType[] $event_types
     *
     * @return $this
     */
    public function setEventTypes($event_types)
    {
        $this->event_types = $event_types;

        return $this;
    }

    /**
     * A list of up to ten events to which to subscribe your webhook. To subscribe to all events including new events as they are added, specify the asterisk (`*`) wildcard. To replace the `event_types` array, specify the `*` wildcard. To see all supported events, [list available events](#available-event-type.list).
     *
     * @return \PaypalAddons\classes\API\Model\WebhookEventType[]
     */
    public function getEventTypes()
    {
        return $this->event_types;
    }
}
