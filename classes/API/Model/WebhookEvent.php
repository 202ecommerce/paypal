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

class WebhookEvent extends PayPalModel
{
    /**
     * The ID of the webhook event notification.
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
     * The ID of the webhook event notification.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * The date and time when the webhook event notification was created.
     *
     * @param string $create_time
     *
     * @return $this
     */
    public function setCreateTime($create_time)
    {
        $this->create_time = $create_time;

        return $this;
    }

    /**
     * The date and time when the webhook event notification was created.
     *
     * @return string
     */
    public function getCreateTime()
    {
        return $this->create_time;
    }

    /**
     * The name of the resource related to the webhook notification event.
     *
     * @param string $resource_type
     *
     * @return $this
     */
    public function setResourceType($resource_type)
    {
        $this->resource_type = $resource_type;

        return $this;
    }

    /**
     * The name of the resource related to the webhook notification event.
     *
     * @return string
     */
    public function getResourceType()
    {
        return $this->resource_type;
    }

    /**
     * The version of the event.
     *
     * @param string $event_version
     *
     * @return $this
     */
    public function setEventVersion($event_version)
    {
        $this->event_version = $event_version;

        return $this;
    }

    /**
     * The version of the event.
     *
     * @return string
     */
    public function getEventVersion()
    {
        return $this->event_version;
    }

    /**
     * The event that triggered the webhook event notification.
     *
     * @param string $event_type
     *
     * @return $this
     */
    public function setEventType($event_type)
    {
        $this->event_type = $event_type;

        return $this;
    }

    /**
     * The event that triggered the webhook event notification.
     *
     * @return string
     */
    public function getEventType()
    {
        return $this->event_type;
    }

    /**
     * A summary description for the event notification. For example, `A payment authorization was created.`
     *
     * @param string $summary
     *
     * @return $this
     */
    public function setSummary($summary)
    {
        $this->summary = $summary;

        return $this;
    }

    /**
     * A summary description for the event notification. For example, `A payment authorization was created.`
     *
     * @return string
     */
    public function getSummary()
    {
        return $this->summary;
    }

    /**
     * The resource that triggered the webhook event notification.
     *
     * @param \PaypalAddons\classes\API\Model\PayPalModel $resource
     *
     * @return $this
     */
    public function setResource($resource)
    {
        $this->resource = $resource;

        return $this;
    }

    /**
     * The resource that triggered the webhook event notification.
     *
     * @return \PaypalAddons\classes\API\Model\PayPalModel
     */
    public function getResource()
    {
        return $this->resource;
    }

    public function getCartId()
    {
        $parts = explode('_', $this->getResource()->__get('custom_id'));

        foreach ($parts as $part) {
            if (strpos($part, 'Cart') === 0) {
                return (int) trim(explode(':', $part)[1]);
            }
        }

        return null;
    }
}
