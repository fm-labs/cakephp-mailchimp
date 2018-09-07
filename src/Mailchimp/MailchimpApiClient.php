<?php

namespace Mailchimp\Mailchimp;

use Cake\Core\InstanceConfigTrait;

/**
 * MailchimpApiClient class
 *
 * @link http://developer.mailchimp.com/documentation/mailchimp/
 *
 * @method get($method, $args = array(), $timeout = null)
 * @method post($method, $args = array(), $timeout = null)
 * @method put($method, $args = array(), $timeout = null)
 * @method patch($method, $args = array(), $timeout = null)
 * @method delete($method, $args = array(), $timeout = null)
 */
class MailchimpApiClient {

    use InstanceConfigTrait;

    const MEMBER_STATUS_SUBSCRIBED      = 'subscribed';
    const MEMBER_STATUS_PENDING         = 'pending';
    const MEMBER_STATUS_UNSUBSCRIBED    = 'unsubscribed';
    const MEMBER_STATUS_CLEANED         = 'cleaned';

    /**
     * @var array
     */
    protected $_defaultConfig = [
        'api_key' => null,
        'throw_exceptions' => true,
        'list_id' => null // @deprecated
    ];

    /**
     * @var \DrewM\MailChimp\MailChimp
     */
    protected $_api;

    public function __construct(array $config)
    {
        $this->config($config);

        if (!$this->config('api_key')) {
            throw new \InvalidArgumentException('MailChimp api requires an api key');
        }

        $apiClass = '\\DrewM\\MailChimp\\MailChimp';
        if (!class_exists($apiClass)) {
            throw new \RuntimeException("MailChimp api class not found: " . $apiClass);
        }
        $this->_api = new $apiClass($this->config('api_key'));
    }

    public function __call($action, $params)
    {
        if (!method_exists($this->_api, $action)) {
            throw new \InvalidArgumentException("Unknown method: ". $action);
        }

        $return = call_user_func_array([$this->_api, $action], $params);

        if (in_array($action, ['get', 'post', 'put', 'patch', 'delete'])) {
            return $this->_return($return);
        }

        return $return;
    }

    public function getLists()
    {
        return $this->get('lists');
    }

    public function getListSignupForms($listId)
    {
        return $this->get('lists/' . $listId . '/signup-forms');
    }

    public function getListMembers($listId)
    {
        return $this->get('lists/' . $listId . '/members');
    }

    public function getListMemberByEmail($listId, $email)
    {
        $hash = $this->_api->subscriberHash($email);
        return $this->get('lists/' . $listId . '/members/' . $hash);
    }

    public function subscribeListMemberByEmail($listId, $email, array $data = [])
    {
        $data = array_merge(
            ['status' => self::MEMBER_STATUS_SUBSCRIBED],
            $data,
            ['email_address' => $email]
        );

        // POST strategy
        //return $this->_return($this->_api->post('lists/' . $listId . '/members', $data));

        // PUT strategy
        $hash = $this->_api->subscriberHash($email);
        return $this->put('lists/' . $listId . '/members/' . $hash, $data);
    }

    public function unsubscribeListMemberByEmail($listId, $email)
    {
        $hash = $this->_api->subscriberHash($email);
        return $this->patch('lists/' . $listId . '/members/' . $hash, ['status' => 'unsubscribed']);
    }

    public function deleteListMemberByEmail($listId, $email)
    {
        $hash = $this->_api->subscriberHash($email);
        return $this->delete('lists/' . $listId . '/members/' . $hash);
    }

    /**
     * @deprecated Use getListMembers() instead
     */
    public function getSubscribers($listId = null)
    {
        $listId = $this->_listId($listId);
        return $this->getListMembers($listId);
    }

    /**
     * @deprecated Use getListMemberByEmail() instead
     */
    public function getSubscriber($email, $listId = null)
    {
        $listId = $this->_listId($listId);
        return $this->getListMemberByEmail($listId, $email);
    }

    /**
     * @deprecated Use subscribeListMemberByEmail() instead
     */
    public function addSubscriber($email, $listId = null, array $data = [])
    {
        $listId = $this->_listId($listId);
        return $this->subscribeListMemberByEmail($listId, $email, $data);
    }

    /**
     * @deprecated Use unsubscribeListMemberByEmail() instead
     */
    public function unsubscribeSubscriber($email, $listId = null)
    {
        $listId = $this->_listId($listId);
        return $this->unsubscribeListMemberByEmail($listId, $email);
    }

    /**
     * @deprecated Use deleteListMemberByEmail() instead
     */
    public function removeSubscriber($email, $listId = null)
    {
        $listId = $this->_listId($listId);
        return $this->deleteListMemberByEmail($listId, $email);
    }

    /**
     * Check the listId we are working on.
     * If listId === null, then use the default list.
     * If the config option `throw_exception` is enabled,
     *    an exception will be thrown, if no listId is set
     *
     * @param string $listId
     * @throws \InvalidArgumentException
     * @return string|null
     */
    protected function _listId($listId)
    {
        if ($listId === null) {
            $listId = $this->config('list_id');
        }

        if ($this->config('throw_exception')) {
            throw new \InvalidArgumentException('Mailchimp list ID not specified');
        }

        return $listId;
    }

    protected function _return($result)
    {
        if ($this->config('throw_exceptions') == true) {
            $lastError = $this->_api->getLastError();
            if ($lastError) {
                throw new MailchimpException($lastError, $result);
            }
        }

        return $result;
    }
}
