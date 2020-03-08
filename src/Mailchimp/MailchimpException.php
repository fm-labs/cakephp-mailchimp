<?php

namespace Mailchimp\Mailchimp;

class MailchimpException extends \Exception
{
    protected $_result;

    public function __construct($message = "", $result = null)
    {

        $code = 0;
        if (is_array($result)) {
            if (isset($result['status'])) {
                $code = $result['status'];
            }
            if (isset($result['detail'])) {
                $message = $result['detail'];
            }
        }

        $this->_result = $result;
        parent::__construct($message, $code);
    }

    public function getResult()
    {
        return $this->_result;
    }
}
