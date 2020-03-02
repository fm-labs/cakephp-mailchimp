<?php

namespace Mailchimp\Event;

use Cake\Event\Event;
use Cake\Utility\Hash;

/**
 * MailchimpWebhookEvent class
 *
 * An event wrapper class for Mailchimp Webhooks
 * @link https://developer.mailchimp.com/documentation/mailchimp/guides/about-webhooks/
 */
class MailchimpWebhookEvent extends Event
{
    /**
     * Construct a cake event from the webhook payload data
     *
     * @param string $payload
     */
    public function __construct($payload)
    {
        $name = 'event';
        $data = [];

        if (isset($payload['type'])) {
            $name = $payload['type'];
        }

        if (isset($payload['fired_at'])) {
            $this->_firedAt = $payload['fired_at'];
        }

        if (isset($payload['data'])) {
            $data = $payload['data'];
        }

        parent::__construct('Mailchimp.Webhook.' . $name, null, $data);
    }

    /**
     * Returns the datetime when the event was fired from mailchimp
     * @return string
     */
    public function getEventTime()
    {
        return $this->_firedAt;
    }

    /**
     * Returns the mailchimp eventid
     * @return string
     */
    public function getEventId()
    {
        return Hash::get($this->data, 'id');
    }

    /**
     * Returns the mailchimp internal member Id from the webhook data
     * @return string
     */
    public function getMemberId()
    {
        return Hash::get($this->data, 'web_id');
    }

    /**
     * Returns the mailchimp internal list Id from the webhook data
     * @return string
     */
    public function getListId()
    {
        return Hash::get($this->data, 'list_id');
    }

    /**
     * Returns the email address from the webhook data
     * @return string
     */
    public function getEmail()
    {
        return Hash::get($this->data, 'email');
    }

    /**
     * Returns the email format from the webhook data
     * @return string
     */
    public function getEmailFormat()
    {
        return Hash::get($this->data, 'email_type');
    }

    /**
     * Alias for getMemberId()
     * @return string
     */
    public function getMailchimpMemberId()
    {
        return $this->getMemberId();
    }

    /**
     * Alias for getListId()
     * @return string
     */
    public function getMailchimpListId()
    {
        return $this->getListId();
    }
}
